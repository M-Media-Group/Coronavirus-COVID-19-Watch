<?php
/*
Plugin Name: Coronavirus COVID-19 Watch
Plugin URI: https://mmediagroup.fr/covid-19
Description: Free live, historical, and vaccine data with total cases around the world or by country. Get live data on the admin dashboard, using the helpful link in the top toolbar to quickly get more info, or via shortcode (by country or global). There's even a widget that you can add to your footers, menus, and widget areas.
Author: M Media
Version: 1.5.0
Author URI: https://profiles.wordpress.org/mmediagroup/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
{Plugin Name} is distributed in the hope that it will be useful to Covid clients,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
 */

if (!defined('MMEDIA_COVID_VER')) {
    define('MMEDIA_COVID_VER', '1.5.0');
}

// Start up the engine
class MMedia_Covid
{

    /**
     * Static property to hold our singleton instance
     *
     */
    public static $instance = false;

    private static $request_args;

    /**
     * This is our constructor
     *
     * @return void
     */
    private function __construct()
    {
        self::$request_args = array('headers' => array("Accept" => "application/json", "origin" => site_url()));
        // register_activation_hook(__FILE__, array($this, 'covid_install'));
        // register_deactivation_hook(__FILE__, array($this, 'covid_uninstall'));

        // back end
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'), 999);
        add_action('wp_enqueue_scripts', array($this, 'load_scripts'));
        add_action('do_meta_boxes', array($this, 'create_metaboxes'), 10, 2);
        add_filter('auto_update_plugin', array($this, 'auto_update_specific_plugins'), 10, 2);

        add_action('admin_init', array($this, 'handle_admin_init'));
        // add_action('admin_menu', array($this, 'covid_create_menu'));
        // add_action('admin_menu', array($this, 'covid_remove_menus'), 999);
        // add_action('admin_notices', array($this, 'my_error_notice'));
        // add_action('wp_dashboard_setup', array($this, 'my_custom_dashboard_widgets'));
        add_action('widgets_init', array($this, 'wpdocs_register_widgets'));
        add_action('admin_bar_menu', array($this, 'covid_remove_toolbar_nodes'), 999);
        add_shortcode('covid-watch', array($this, 'shortcode_cases'));
        add_shortcode('covid-history', array($this, 'shortcode_history'));
        add_shortcode('covid-vaccines', array($this, 'shortcode_vaccines'));
        add_shortcode('covid-live-map', array($this, 'shortcode_map'));
    }

    public static function getCasesData()
    {
        $body = get_transient('mmedia_covid_cases');
        if (false === $body) {
            $response = wp_remote_get('https://covid-api.mmediagroup.fr/v1/cases', self::$request_args);
            $body = json_decode($response['body'], true);
            set_transient('mmedia_covid_cases', $body, HOUR_IN_SECONDS);
        }
        return $body;
    }

    public static function getHistoryData($status = 'confirmed')
    {
        $body = get_transient('mmedia_covid_history_' . $status);
        if (false === $body) {
            $response = wp_remote_get('https://covid-api.mmediagroup.fr/v1/history?status=' . $status, self::$request_args);
            $body = json_decode($response['body'], true);
            set_transient('mmedia_covid_history_' . $status, $body, DAY_IN_SECONDS);
        }
        return $body;
    }
    
    public static function getVaccinesData()
    {
        $body = get_transient('mmedia_covid_vaccines');
        if (false === $body) {
            $response = wp_remote_get('https://covid-api.mmediagroup.fr/v1/vaccines', self::$request_args);
            $body = json_decode($response['body'], true);
            set_transient('mmedia_covid_vaccines', $body, DAY_IN_SECONDS);
        }
        return $body;
    }

    public function shortcode_cases($atts = [], $content = null)
    {
        // do something to $content
        // always return
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // override default attributes with user attributes
        $covid_atts = shortcode_atts([
            'country' => 'Global',
            'status' => 'confirmed',
            'sort' => 'alphabetical',
            'limit' => 0,
        ], $atts);
        $body = $this->getCasesData();

        if (strtolower($covid_atts['country']) == 'all') {
            $covid_atts['sort'] = strtolower($covid_atts['sort']);
            if ($covid_atts['sort'] == 'confirmed' || $covid_atts['sort'] == 'deaths') {
                uasort($body, function (array $a, array $b) use ($covid_atts) {
                    return $b['All'][$covid_atts['sort']] - $a['All'][$covid_atts['sort']];
                });
            } else {
                ksort($body);
            }

            $iteration = 0;

            $html = "<table>";
            $html .= "<tr>";
            $html .= "<th>" . __('Country', 'coronavirus-covid-19-watch') . "</th>";
            $html .= "<th>" . __('Confirmed cases', 'coronavirus-covid-19-watch') . "</th>";
            $html .= "<th>" . __('Deaths', 'coronavirus-covid-19-watch') . "</th>";
            $html .= "</tr>";
            foreach ($body as $key => $value) {
                if ($key == 'Global') {
                    continue;
                }
                if ($covid_atts['limit'] !== 0 && $iteration == $covid_atts['limit']) {
                    break;
                }
                $html .= "<tr>";
                $html .= "<td>" . $key . "</td>";
                $html .= "<td>" . number_format($value['All']['confirmed']) . "</td>";
                $html .= "<td>" . number_format($value['All']['deaths']) . "</td>";
                $html .= "</tr>";
                $iteration++;
            }
            $html .= "<tr>";
            $html .= "<td><strong>Global</strong></td>";
            $html .= "<td>" . number_format($body['Global']['All']['confirmed']) . "</td>";
            $html .= "<td>" . number_format($body['Global']['All']['deaths']) . "</td>";
            $html .= "</tr>";
            if (get_option('covid_setting_attribute')) {
                $html .= "<tfoot><tr>";
                $html .= "<td><strong>" . __('API provider', 'coronavirus-covid-19-watch') . "</strong></td>";
                $html .= '<td><strong><a href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=shortcode_cases" target="_BLANK" rel="noreferrer">M Media</a></strong></td>';
                $html .= "<td></td>";
                $html .= "</tr></tfoot>";
            }
            $html .= "</table>";
            return $html;
        } elseif (isset($body[esc_html__($covid_atts['country'])])) {
            $number = number_format($body[esc_html__($covid_atts['country'])]['All'][esc_html__($covid_atts['status'])]);
            return get_option('covid_setting_attribute') ? '<a href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=shortcode_cases" target="_BLANK" rel="noreferrer">' . $number . '</a>' : $number;
        }
        return "Country '" . $covid_atts['country'] . "' is invalid.";
    }

    public function shortcode_history($atts = [], $content = null)
    {
        // do something to $content
        // always return
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // override default attributes with user attributes
        $covid_atts = shortcode_atts([
            'country' => 'Global',
            'status' => 'confirmed',
            'limit' => 0,
        ], $atts);
        $body = $this->getHistoryData(esc_html__($covid_atts['status']));

        if (isset($body[esc_html__($covid_atts['country'])])) {
            $body = $body[esc_html__($covid_atts['country'])]['All']['dates'];
        } else {
            return "Country '" . $covid_atts['country'] . "' or status is invalid.";
        }

        $html = "<table>";
        $html .= "<tr>";
        $html .= "<th>" . __('Date', 'coronavirus-covid-19-watch') . " (YYYY-MM-DD)</th>";
        $html .= "<th>" . ($covid_atts['status'] == 'confirmed' ? __('Cases', 'coronavirus-covid-19-watch') : __('Deaths', 'coronavirus-covid-19-watch')) . " (" . esc_html__($covid_atts['country']) . ")</th>";
        $html .= "</tr>";
        $iteration = 0;
        foreach ($body as $key => $value) {
            if ($covid_atts['limit'] !== 0 && $iteration == $covid_atts['limit']) {
                break;
            }
            $html .= "<tr>";
            $html .= "<td>" . $key . "</td>";
            $html .= "<td>" . number_format($value) . "</td>";
            $html .= "</tr>";
            $iteration++;
        }
        if (get_option('covid_setting_attribute')) {
            $html .= "<tfoot><tr>";
            $html .= "<td><strong>" . __('API provider', 'coronavirus-covid-19-watch') . "</strong></td>";
            $html .= '<td><strong><a href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=shortcode_history" target="_BLANK" rel="noreferrer">M Media</a></strong></td>';
            $html .= "</tr></tfoot>";
        }
        $html .= "</table>";
        return $html;
    }

    public function shortcode_vaccines($atts = [], $content = null)
    {
        // do something to $content
        // always return
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // override default attributes with user attributes
        $covid_atts = shortcode_atts([
            'country' => 'Global',
            'status' => 'administered',
            'sort' => 'alphabetical',
            'limit' => 0,
        ], $atts);
        $body = $this->getVaccinesData();

        if (strtolower($covid_atts['country']) == 'all') {
            $covid_atts['sort'] = strtolower($covid_atts['sort']);
            if ($covid_atts['sort'] == 'administered' || $covid_atts['sort'] == 'people_vaccinated') {
                uasort($body, function (array $a, array $b) use ($covid_atts) {
                    return $b['All'][$covid_atts['sort']] - $a['All'][$covid_atts['sort']];
                });
            } else {
                ksort($body);
            }

            $iteration = 0;

            $html = "<table>";
            $html .= "<tr>";
            $html .= "<th>" . __('Country', 'coronavirus-covid-19-watch') . "</th>";
            $html .= "<th>" . __('Administered vaccines', 'coronavirus-covid-19-watch') . "</th>";
            $html .= "<th>" . __('People vaccinated', 'coronavirus-covid-19-watch') . "</th>";
            $html .= "<th>" . __('People partially vaccinated', 'coronavirus-covid-19-watch') . "</th>";
            $html .= "</tr>";
            foreach ($body as $key => $value) {
                if ($key == 'Global') {
                    continue;
                }
                if ($covid_atts['limit'] !== 0 && $iteration == $covid_atts['limit']) {
                    break;
                }
                $html .= "<tr>";
                $html .= "<td>" . $key . "</td>";
                $html .= "<td>" . number_format($value['All']['administered']) . "</td>";
                $html .= "<td>" . number_format($value['All']['people_vaccinated']) . "</td>";
                $html .= "<td>" . number_format($value['All']['people_partially_vaccinated']) . "</td>";
                $html .= "</tr>";
                $iteration++;
            }
            $html .= "<tr>";
            $html .= "<td><strong>Global</strong></td>";
            $html .= "<td>" . number_format($body['Global']['All']['administered']) . "</td>";
            $html .= "<td>" . number_format($body['Global']['All']['people_vaccinated']) . "</td>";
            $html .= "<td>" . number_format($body['Global']['All']['people_partially_vaccinated']) . "</td>";
            $html .= "</tr>";
            if (get_option('covid_setting_attribute')) {
                $html .= "<tfoot><tr>";
                $html .= "<td><strong>" . __('API provider', 'coronavirus-covid-19-watch') . "</strong></td>";
                $html .= '<td><strong><a href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=shortcode_vaccines" target="_BLANK" rel="noreferrer">M Media</a></strong></td>';
                $html .= "<td></td>";
                $html .= "</tr></tfoot>";
            }
            $html .= "</table>";
            return $html;
        } elseif (isset($body[esc_html__($covid_atts['country'])])) {
            $number = number_format($body[esc_html__($covid_atts['country'])]['All'][esc_html__($covid_atts['status'])]);
            return get_option('covid_setting_attribute') ? '<a href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=shortcode_vaccines" target="_BLANK" rel="noreferrer">' . $number . '</a>' : $number;
        }
        return "Country '" . $covid_atts['country'] . "' is invalid.";
    }

    public static function shortcode_map($atts = [], $content = null)
    {
        wp_enqueue_style('covid-map-css');
        wp_enqueue_script('covid-map-js');
        $div_id = 'svgMapPopulation' . rand();
        wp_enqueue_script('map-data-' . $div_id, plugin_dir_url(__FILE__) . '/js/map-data.js', array(), MMEDIA_COVID_VER, 'all');
        // do something to $content
        // always return
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // override default attributes with user attributes
        $covid_atts = shortcode_atts([
            'sort' => 'confirmed',
            'height' => '70vh',
        ], $atts);
        $body = self::getCasesData();
        unset($body['Global']);
        $translation_array = array(
            'json_data' => $body,
            'selector' => $div_id,
            'color_by' => $covid_atts['sort'],
            'attribution_text' => __('Data source', 'coronavirus-covid-19-watch') . ': <a href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=shortcode_map" target="_BLANK" rel="noreferrer">M Media API</a>',
        );
        wp_localize_script('map-data-' . $div_id, 'covid_data', $translation_array);

        $html = '<div id="' . $div_id . '" style="height: ' . esc_html__($covid_atts['height']) . ';"></div>';

        return $html;
    }

    /**
     * If an instance exists, this returns it.  If not, it creates one and
     * retuns it.
     *
     * @return Covid
     */

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function wpdocs_register_widgets()
    {
        register_widget('MMedia_Covid_Widget');
    }
    /**
     * load textdomain
     *
     * @return void
     */

    public function covid_install()
    {
    }

    /**
     * load textdomain
     *
     * @return void
     */

    public function covid_uninstall()
    {
    }

    /**
     * Admin styles
     *
     * @return void
     */

    public function admin_scripts()
    {
        if (function_exists('covid19_style')) {
            wp_enqueue_style('style.css', plugin_dir_url(__FILE__) . '/css/style.css', array(), MMEDIA_COVID_VER, 'all');
        }
    }

    public function load_scripts()
    {
        wp_register_style('covid-map-css', plugin_dir_url(__FILE__) . '/css/svgMap.css');
        wp_register_script('covid-map-js', plugin_dir_url(__FILE__) . '/js/svgMap.js');
    }

    /**
     * call metabox
     *
     * @return void
     */

    public function create_metaboxes($context)
    {
        add_meta_box('covid_help_widget', 'COVID-19 Watch (' . get_option('covid_setting_country') . ')', array($this, 'custom_dashboard_help'), 'dashboard', 'normal', 'high');
    }

    /**
     * Enable auto updates
     *
     * @return void
     */
    public function auto_update_specific_plugins($update, $item)
    {
        $plugins = array('coronavirus-covid-19-watch');
        if (in_array($item->slug, $plugins)) {
            // update plugin
            return true;
        } else {
            // use default settings
            return $update;
        }
    }

    /**
     * display meta fields for notes meta
     *
     * @return void
     */

    public function custom_dashboard_help($post)
    {
        $body = $this->getCasesData();
        echo '<div style="text-align: center;"><h2>' . number_format($body[get_option('covid_setting_country')]['All']['confirmed']) . ' ' . __('confirmed cases', 'coronavirus-covid-19-watch') . '</h2><h3>' . number_format($body[get_option('covid_setting_country')]['All']['deaths']) . ' ' . __('deaths', 'coronavirus-covid-19-watch') . '</h3><p>' . __('Only cases tested in a laboratory are counted; with news of sketchy reporting and others staying at home, there\'s more cases out there.', 'coronavirus-covid-19-watch') . '</p><a class="button" href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=dashboard" target="_BLANK" rel="noreferrer">' . __('More info', 'coronavirus-covid-19-watch') . '</a></div>';
    }

    /**
     * load textdomain
     *
     * @return void
     */

    public function covid_create_menu()
    {
        //create new top-level menu
        // add_menu_page('Covid Plugin', 'Covid',
        //     'publish_pages', 'covid_main_menu', array($this, 'covid_settings_page'),
        //     plugins_url('images/m.svg', __FILE__));
    }

    /**
     * load textdomain
     *
     * @return void
     */

    public function covid_remove_menus()
    {
    }
    /**
     * load textdomain
     *
     * @return void
     */

    public function my_error_notice()
    {
    }
    /**
     * load textdomain
     *
     * @return void
     */
    public function my_custom_dashboard_widgets()
    {
    }
    /**
     * load textdomain
     *
     * @return void
     */
    public function covid_remove_toolbar_nodes($wp_admin_bar)
    {
        if (get_option('covid_setting_toolbar')) {
            $wp_admin_bar->add_node([
                'id' => 'covid',
                'title' => 'COVID Watch',
                'href' => 'https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=toolbar',
                'meta' => [
                    'target' => '_BLANK',
                ],
            ]);
        }
    }
    /**
     * load textdomain
     *
     * @return void
     */
    public function handle_admin_init()
    {
        // register_nav_menu('covid-menu', __('Covid Menu'));
        // register a new section in the "reading" page
        register_setting('reading', 'covid_setting_country', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'validateCountry'),
            'default' => 'Global',
        ));
        register_setting('reading', 'covid_setting_attribute', array(
            'type' => 'boolean',
            'default' => false,
        ));
        register_setting('reading', 'covid_setting_toolbar', array(
            'type' => 'boolean',
            'default' => false,
        ));

        add_settings_section(
            'covid_settings_section',
            'COVID-19 Watch',
            array($this, 'covid_settings_section_cb'),
            'reading'
        );

        // register a new field in the "covid_settings_section" section, inside the "reading" page
        add_settings_field(
            'covid_settings_field',
            __('Dashboard country', 'coronavirus-covid-19-watch'),
            array($this, 'covid_settings_field_cb'),
            'reading',
            'covid_settings_section'
        );

        add_settings_field(
            'covid_settings_field_attr',
            __('Show attribution', 'coronavirus-covid-19-watch'),
            array($this, 'covid_settings_field_attribute'),
            'reading',
            'covid_settings_section'
        );

        add_settings_field(
            'covid_settings_field_toolbarmenu',
            __('Show toolbar menu', 'coronavirus-covid-19-watch'),
            array($this, 'covid_settings_field_toolbar'),
            'reading',
            'covid_settings_section'
        );
    }

    public function validateCountry($input)
    {
        if (!$input) {
            return "Global";
        }
        $body = $this->getCasesData();
        if (isset($body[$input])) {
            return $input;
        }
        return 'INVALID COUNTRY';
    }

    /**
     * register covid_settings_init to the admin_init action hook
     */

    /**
     * callback functions
     */

    // section content cb
    public function covid_settings_section_cb()
    {
        echo '<p>Control settings regarding the Coronavirus COVID-19 Watch plugin.</p>';
    }

    public function covid_settings_field_cb()
    {
        $setting = get_option('covid_setting_country');
        $array = $this->getCasesData();
        ksort($array); ?>
    <select id="covid_setting_country" name="covid_setting_country" value="<?php echo $setting ? esc_attr($setting) : 'Global'; ?>">
   <?php
foreach ($array as $key => $value) {
            echo '<option value="' . $key . '" ' . ($setting == $key ? 'selected' : '') . '>' . $key . '</option>';
        } ?>
    </select>
    <p class="description"><?php _e('Try Global, US, France, Spain, Italy, United Kingdom, and more.', 'coronavirus-covid-19-watch'); ?></p>
    <?php
    }

    public function covid_settings_field_attribute()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('covid_setting_attribute');
        // output the field?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Show attribution', 'coronavirus-covid-19-watch'); ?></span></legend>
            <label for="covid_setting_attribute"><input type="checkbox" id="covid_setting_attribute" name="covid_setting_attribute" <?php echo $setting ? 'checked="checked"' : ''; ?>><?php _e('Attribute M Media, the maker of this plugin and API for the data from Johns Hopkins University, on the front-end.', 'coronavirus-covid-19-watch'); ?></label>
        </fieldset>
    <?php
    }

    public function covid_settings_field_toolbar()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('covid_setting_toolbar');
        // output the field?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Show toolbar menu', 'coronavirus-covid-19-watch'); ?></span></legend>
            <label for="covid_setting_toolbar"><input type="checkbox" id="covid_setting_toolbar" name="covid_setting_toolbar" <?php echo $setting ? 'checked="checked"' : ''; ?>><?php _e('Show the toolbar menu at the top', 'coronavirus-covid-19-watch'); ?></label>
        </fieldset>
    <?php
    }

    public function covid_settings_page()
    {
    }
    /// end class
}

class MMedia_Covid_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'covid-widget', // Base ID
            __('COVID-19 confirmed cases', 'coronavirus-covid-19-watch') // Name
        );

        add_action('widgets_init', function () {
            register_widget('MMedia_Covid_Widget');
        });
    }

    public $args = array(
        'before_title' => '<h4 class="widgettitle">',
        'after_title' => '</h4>',
        'before_widget' => '<div class="widget-wrap">',
        'after_widget' => '</div></div>',
    );

    public function widget($args, $instance)
    {
        $body = MMedia_Covid::getCasesData();

        $number = number_format($body['Global']['All']['confirmed']);

        echo $args['before_widget'];

        // if (!empty($instance['title'])) {
        //     echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        // }

        echo '<div class="textwidget">';

        echo get_option('covid_setting_attribute') ? '<a href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=widget" target="_BLANK" rel="noreferrer">' . $number . '</a>' : $number;

        echo ' ' . __('confirmed cases', 'coronavirus-covid-19-watch');

        echo '</div>';

        echo $args['after_widget'];
    }

    public function form($instance)
    {
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();

        // $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        // $instance['text'] = (!empty($new_instance['text'])) ? $new_instance['text'] : '';

        return $instance;
    }
}
// Instantiate our class
$covid = MMedia_Covid::getInstance();
