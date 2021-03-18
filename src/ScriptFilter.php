<?php


namespace FMDataAPI;


use Exception;

class ScriptFilter
{
    /** @var FileMakerDataAPI */
    protected $api;

    /** @var Settings */
    protected $settings;

    public function __construct(FileMakerDataAPI $api, Settings $settings)
    {
        $this->api = $api;
        $this->settings = $settings;

        add_filter('fm-call-script', [$this, 'callScript']);
    }

    public function callScript(ScriptParameter $parameters)
    {
        try {
            return $this->api->callScript($parameters->getLayout(), $parameters->getScript(), $parameters->getParameter());
        } catch (Exception $e) {
            return null;
        }
    }
}