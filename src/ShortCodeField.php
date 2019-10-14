<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 28/07/2018
 * Time: 13:51
 */

namespace FMDataAPI;

use \Exception;

class ShortCodeField extends ShortCodeBase
{
    /** @var FileMakerDataAPI */
    protected $api;

    /** @var Settings */
    protected $settings;

    /**
     * ShortCodeField constructor.
     *
     * @param FileMakerDataAPI $api
     * @param Settings $settings
     */
    public function __construct(FileMakerDataAPI $api, Settings $settings)
    {
        parent::__construct($api, $settings);

        add_shortcode('FM-DATA-FIELD', [$this, 'retrieveFieldContent']);
    }

    /**
     * @param array $attr
     *
     * @return string
     */
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
}