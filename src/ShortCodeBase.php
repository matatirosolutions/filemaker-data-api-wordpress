<?php


namespace FMDataAPI;


class ShortCodeBase
{
    /** @var FileMakerDataAPI */
    protected $api;

    /** @var Settings */
    protected $settings;

    public function __construct(FileMakerDataAPI $api, Settings $settings)
    {
        $this->api = $api;
        $this->settings = $settings;
    }

    protected function outputField(array $record, $field, $type = null, $link = false)
    {
        if(!array_key_exists($field, $record)) {
            return '';
        }

        switch(substr(strtolower($type), 0, 5)) {
            case 'image':
            case 'thumb':
                $content = $this->sizeImage($type, $record, $field);
                break;
            case 'curre':
                if(empty($record[$field])) {
                    $content = '';
                } else {
                    setlocale(LC_ALL, $this->settings->getLocale());
                    $content = (money_format('%#10n', $record[$field]));
                }
                break;
            case 'webli':
                $content = sprintf('<a href="%s" target="_blank">%s</a>', $record[$field], $field);
                break;
            default:
                $content = nl2br($record[$field]);
        }

        if($link) {
            return sprintf('<a href="%s">%s</a>', $link, $content);
        }

        return $content;
    }

    protected function sizeImage($type, $record, $field)
    {
        $path = $this->settings->getCache()
            ? $this->cacheImage($record, $field)
            : $record[$field];

        $params = explode('-', $type);
        $width = 'image' == $params[0]
            ? (isset($params[1]) ? $params[1] : '')
            : (isset($params[1]) ? $params[1] : '50');

        return sprintf('<img src="%s" width="%s" />', $path, $width);
    }


    protected function validateAttributesOrExit(array $reqs, array $attr)
    {
        $err = [];
        foreach($reqs as $req) {
            if(!array_key_exists($req, $attr)) {
                $err[] = $req;
            }
        }

        if(count($err)) {
            print(sprintf('Error required attribute%s %s missing.', 1 == count($err) ? '' : 's', implode(',', $err)));

            return false;
        }

        return true;
    }

    private function cacheImage($record, $field)
    {
        $uploads = wp_get_upload_dir();
        $baseFolder = $uploads['basedir'] . DIRECTORY_SEPARATOR . 'fm-data-api';
        if(!is_dir($baseFolder)) {
            if (!mkdir($baseFolder) && !is_dir($baseFolder)) {
                // Can't create a cache folder so stream the content
                return $record[$field];
            }
        }

        $layout = strtolower(str_replace(' ', '-' , $this->api->getLayout()));
        $layoutFolder = $baseFolder . DIRECTORY_SEPARATOR . $layout;
        if(!is_dir($layoutFolder)) {
            if (!mkdir($layoutFolder) && !is_dir($layoutFolder)) {
                // Can't create a cache folder so stream the content
                return $record[$field];
            }
        }

        $filename = strtolower(str_replace(' ', '', $field)) .'-' . $record['recordId'] . '-' . $record['modId'] . '.cache';
        $cachePath = $layoutFolder . DIRECTORY_SEPARATOR . $filename;

        if(!file_exists($cachePath)) {
            $ckfile = tempnam ("/tmp", 'cookiename');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $record[$field]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile);
            $img = curl_exec($ch);

            file_put_contents(
                $cachePath,
                $img
            );
        }

        return $uploads['baseurl'] . '/fm-data-api/' . $layout . '/' . $filename;
    }

}