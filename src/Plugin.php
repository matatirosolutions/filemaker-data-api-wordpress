<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 28/07/2018
 * Time: 12:39
 */

namespace FMDataAPI;

class Plugin
{
    /** @var Admin */
    protected $admin;

    /** @var array */
    protected $shortcodes;

    /** @var array */
    protected $filters;

    public function __construct()
    {
        add_action('init', [$this, 'fmDataApiRegisterSession']);

        $settings = get_option( FM_DATA_API_SETTINGS, Admin::fmDataApiDefaultOptions());
        $api = new FileMakerDataAPI($settings);

        $this->admin = new Admin();
        $this->shortcodes = [
            new ShortCodeField($api, $settings),
            new ShortCodeTable($api, $settings),
        ];
        $this->filters = [
            new ScriptFilter($api, $settings)
        ];
    }

    public function fmDataApiRegisterSession(){
        if(!session_id()) {
            session_start(['read_and_close' => true]);
        }
    }

}
