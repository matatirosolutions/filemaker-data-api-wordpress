<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 28/07/2018
 * Time: 12:40
 */

namespace FMDataAPI;

use \Exception;

class Admin
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'fmDataApiMenu'] );
        add_filter('plugin_action_links_'.FM_DATA_API_BASENAME, [$this, 'fmDataApiSettingsLink']);
    }


    public function fmDataApiSettingsLink( $links )
    {
        $settings_link = '<a href="options-general.php?page=fm-data-api">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }


    function fmDataApiMenu()
    {
        add_options_page( 'FileMaker Data API settings', 'FM Data API', 'manage_options', 'fm-data-api', [$this, 'fmDataApiOptions'] );
    }

    /**
     * @throws Exception
     */
    function fmDataApiOptions()
    {
        //must check that the user has the required capability
        if (!current_user_can('manage_options'))
        {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }

        $html = '';
        $postField = 'fm-dataapi_submit';

        $default = self::fmDataApiDefaultOptions();
        $settings = get_option( FM_DATA_API_SETTINGS, $default);

        if( isset($_POST[ $postField ]) && $_POST[ $postField ] == 'Y' ) {
            $settings = Settings::CreateFromArray($_POST);
            update_option( FM_DATA_API_SETTINGS, $settings );
            $html .= '<div class="updated"><p><strong>Your server settings have been saved.</strong></p></div>';


            try {
                $api = new FileMakerDataAPI($settings);
                $api->fetchToken();

                $html .= '<div class="updated"><p><strong>Success! A connection was made to FileMaker.</strong></p></div>';
            } catch(Exception $e) {
                $html .= sprintf('<div class="error"><p><strong>Oh dear! Unable to connect to FileMaker with message %s.</strong></p></div>', $e->getMessage());
            }

        }


        $html .= <<<EOHTML
        <div class="wrap">
        <h2>FileMaker Data API settings</h2>
        <p>Enter the settings for your FileMaker server below. All fields are required!</p>
        <hr />
        <form name="form1" method="post" action="">
            <input type="hidden" name="{$postField}" value="Y">
            <table class="form-table"><tbody> 
EOHTML;
        foreach(Settings::DATA_API_PARAMETERS as $setting) {
            $title = ucfirst($setting);
            $value = $settings->{'get'.$setting}();

            switch($setting) {
                case 'locale':
                    $html .= $this->localeSettingSelector($value);
                    break;
                case 'verify':
                    $html .= $this->verifyCheckbox($value);
                    break;
                case 'cache':
                    $html .= $this->cacheCheckbox($value);
                    break;
                default:
                    $html .= $this->inputBox($setting, $title, $value);
            }
        }

        $html .= <<<EOHTML
        </tbody></table>
        <hr />
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="Save Changes" />
        </p>
    </form>
    </div>
EOHTML;

        print($html);
    }


    private function localeSettingSelector($value)
    {
        $html = <<<EOHTML
            <tr>
                <th scope="row"><label for="locale">Locale</label></th>
                <td><select name="locale">';
EOHTML;

        foreach(Locales::LOCALES as $id => $name)
        {
            $selected = $id == $value ? ' selected' : '';
            $html .= sprintf('<option value="%s"%s>%s</option>', $id, $selected, $name);
        }

        $html .= '</select><p>Determines how currency is displayed.</p></td></tr>';

        return $html;
    }

    private function inputBox($setting, $title, $value)
    {
        $type = 'password' == $setting ? 'password' : 'text';
        return <<<EOHTML
            <tr>
                <th scope="row"><label for="{$setting}">{$title}</label></th>
                <td><input type="{$type}" name="{$setting}" value="{$value}" size="20"></td>
            </tr>
EOHTML;
    }

    private function verifyCheckbox($value)
    {
        $checked = $value ? ' checked' : '';
        return <<<EOHTML
            <tr>
                <th scope="row"><label for="verify">Do not verify SSL</label></th>
                <td>
                    <input type="hidden" name="verify" value="0">
                    <input type="checkbox" name="verify" value="1"{$checked} />
                    <span style="font-size: 11px">
                        In general this is a bad idea and you should only enable this for testing purposes<br />
                        where you simply can't install a valid SSL certificate for your FMS.
                    </span>
                </td>
            </tr>
EOHTML;
    }

    private function cacheCheckbox($value)
    {
        $checked = $value ? ' checked' : '';
        return <<<EOHTML
            <tr>
                <th scope="row"><label for="cache">Cache images</label></th>
                <td>
                    <input type="hidden" name="cache" value="0">
                    <input type="checkbox" name="cache" value="1"{$checked} />
                    <span style="font-size: 11px">
                        Some themes will try to apply lazy loading to images which will result in issues with<br />
                        the one-time-use signed URLs returned by the DAPI. Checking this box will save local copies<br />
                        of requested images, stamped with a modId and serve those instead.
                    </span>
                </td>
            </tr>
EOHTML;
    }

    /**
     * @return Settings
     */
    public static function fmDataApiDefaultOptions()
    {
        try {
            return Settings::CreateFromArray([
                'server' => '',
                'port' => '443',
                'database' => '',
                'username' => '',
                'password' => '',
                'verify' => 0,
                'locale' => 'en_US',
                'cache' => 0,
            ]);
        } catch(Exception $e) {}
    }
}