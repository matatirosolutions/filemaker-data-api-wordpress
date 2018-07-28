<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 28/07/2018
 * Time: 13:51
 */

namespace FMDataAPI;

use \Exception;

class ShortCodes
{

    /** @var FileMakerDataAPI */
    protected $api;

    /** @var Settings */
    protected $settings;

    public function __construct(FileMakerDataAPI $api, Settings $settings)
    {
        $this->api = $api;
        $this->settings = $settings;

        add_shortcode('FM-DATA-FIELD', [$this, 'retrieveFieldContent']);
        add_shortcode('FM-DATA-TABLE', [$this, 'retrieveTableContent']);
    }

    public function retrieveTableContent(array $attr)
    {
        if(!$this->validateAttributesOrExit(['layout', 'fields'], $attr)) {
            return '';
        }

        try {
            $records = $this->api->findAll($attr['layout']);
            print($this->generateTable($records, $attr));
        } catch (Exception $e) {
            print('Unable to load records.');
        }

        return '';
    }

    public function retrieveFieldContent(array $attr)
    {
        if(!$this->validateAttributesOrExit(['layout', 'id-field', 'id', 'field'], $attr)) {
            return '';
        }

        $id = $attr['id'];
        if('URL' == substr($attr['id'], 0, 3)) {
            $params = explode('-', $attr['id']);
            $id = array_key_exists($params[1], $_GET) ? $_GET[$params[1]] : $attr['id'];
        }

        try {
            $record = $this->api->findOneBy($attr['layout'], [$attr['id-field'] => $id]);
            if(array_key_exists($attr['field'], $record)) {
                $type = empty($attr['type']) ? null : $attr['type'];
                return $this->outputField($record, $attr['field'], $type);
            }
            return 'Field is missing';
        } catch (Exception $e) {
            return 'Unable to load record.';
        }
    }


    private function generateTable(array $records, array $attr)
    {
        $fields = explode('|', $attr['fields']);
        $types = array_key_exists('types', $attr)
            ? explode('|', $attr['types'])
            : [];

        $html = '<table>';
        $html .= array_key_exists('labels', $attr)
            ? $this->generateHeaderRow($attr['labels'])
            : $this->generateHeaderRow($attr['fields']);
        $html .= '<tbody>';


        foreach($records as $record) {
            $link = array_key_exists('id-field', $attr) && array_key_exists('detail-url', $attr)
                ? str_replace('*id*', $record[$attr['id-field']], $attr['detail-url'])
                : '';

                $html .= '<tr>';
            foreach($fields as $id => $field) {
                $type = array_key_exists($id, $types) ? $types[$id] : null;
                $html .= sprintf('<td>%s</td>', $this->outputField($record, trim($field), $type, $link));
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function generateHeaderRow($labels)
    {
        $labels = explode('|', $labels);
        $html = '<thead><tr>';
        foreach($labels as $label) {
            $html .= trim(
                sprintf('<th>%s</th>', $label)
            );
        }
        $html .= '</tr></thead>';

        return $html;
    }


    private function outputField(array $record, $field, $type = null, $link = false)
    {
        if(!array_key_exists($field, $record)) {
            return '';
        }

        switch(substr(strtolower($type), 0, 5)) {
            case 'image':
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
            case 'thumb':
                $content = $this->sizeImage($type, $record[$field]);
                break;
            default:
                $content = nl2br($record[$field]);
        }

        if($link) {
            return sprintf('<a href="%s">%s</a>', $link, $content);
        }

        return $content;
    }

    private function sizeImage($type, $path)
    {
        $params = explode('-', $type);
        $width = 'image' == $params[0]
            ? (isset($params[1]) ? $params[1] : '')
            : (isset($params[1]) ? $params[1] : '50');

        return sprintf('<img src="%s" width="%s" />', $path, $width);
    }


    private function validateAttributesOrExit(array $reqs, array $attr)
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