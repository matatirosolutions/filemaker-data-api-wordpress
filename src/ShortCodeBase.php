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
                $content = $this->sizeImage($type, $record[$field]);
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

    protected function sizeImage($type, $path)
    {
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
}