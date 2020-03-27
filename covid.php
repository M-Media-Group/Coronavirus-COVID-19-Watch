<?php
/*
Plugin Name: Coronavirus COVID-19 Watch
Plugin URI: https://mmediagroup.fr/covid-19
Description: Free live data with total cases around the world or by country. Get live data on the admin dashboard, using the helpful link in the top toolbar to quickly get more info, or via shortcode (by country or global). There's even a widget that you can add to your footers, menus, and widget areas.
Author: M Media
Version: 1.0.1
Author URI: https://profiles.wordpress.org/mmediagroup/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: covid
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
    define('MMEDIA_COVID_VER', '1.0.1');
}

// Start up the engine
class MMedia_Covid
{

    /**
     * Static property to hold our singleton instance
     *
     */
    public static $instance = false;

    /**
     * This is our constructor
     *
     * @return void
     */
    private function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'covid_install'));
        register_deactivation_hook(__FILE__, array($this, 'covid_uninstall'));

        // back end
        //add_action('plugins_loaded', array($this, 'textdomain'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('do_meta_boxes', array($this, 'create_metaboxes'), 10, 2);

        //add_action('init', array($this, 'handle_admin_init'));
        // add_action('admin_menu', array($this, 'covid_create_menu'));
        // add_action('admin_menu', array($this, 'covid_remove_menus'), 999);
        // add_action('admin_notices', array($this, 'my_error_notice'));
        // add_action('wp_dashboard_setup', array($this, 'my_custom_dashboard_widgets'));
        add_action('widgets_init', array($this, 'wpdocs_register_widgets'));
        add_action('admin_bar_menu', array($this, 'covid_remove_toolbar_nodes'), 999);
        add_shortcode('covid-watch', array($this, 'wporg_shortcode_cases'));
    }
    public function wporg_shortcode_cases($atts = [], $content = null)
    {
        // do something to $content
        // always return
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // override default attributes with user attributes
        $wporg_atts = shortcode_atts([
            'country' => 'Global',
            'status' => 'confirmed',
        ], $atts);
        $response = wp_remote_get('https://covid-api.mmediagroup.fr/v1/cases?ab=');
        $body = json_decode($response['body'], true);
        if (isset($body[esc_html__($wporg_atts['country'])])) {
            return number_format($body[esc_html__($wporg_atts['country'])]['All'][esc_html__($wporg_atts['status'])]);
        }
        return "Country '" . $wporg_atts['country'] . "' is invalid.";
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
        // register_widget('Covid_Widget');
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
     * load textdomain
     *
     * @return void
     */

    public function textdomain()
    {
    }

    /**
     * Admin styles
     *
     * @return void
     */

    public function admin_scripts()
    {
        wp_enqueue_style('custom_wp_admin_css', plugins_url('css/admin-style.css', __FILE__), array(), COVID_VER, 'all');
    }

    /**
     * call metabox
     *
     * @return void
     */

    public function create_metaboxes($context)
    {
        add_meta_box('covid_help_widget', 'COVID-19 Watch', array($this, 'custom_dashboard_help'), 'dashboard', 'normal', 'high');
    }

    /**
     * display meta fields for notes meta
     *
     * @return void
     */

    public function custom_dashboard_help($post)
    {
        $response = wp_remote_get('https://covid-api.mmediagroup.fr/v1/cases');
        $body = json_decode($response['body'], true);

        echo '<div style="text-align: center;"><h2>' . number_format($body['Global']['All']['confirmed']) . ' confirmed cases</h2><h3>' . number_format($body['Global']['All']['deaths']) . ' deaths</h3><p>Only cases tested in a laboratory are counted; with news of sketchy reporting and others staying at home, there\'s more cases out there.</p><a class="button" href="https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=dashboard" target="_BLANK" rel="noreferrer">More info</a></div>';
    }

    /**
     * load textdomain
     *
     * @return void
     */

    public function covid_create_menu()
    {
        //create new top-level menu
        add_menu_page('Covid Plugin', 'Covid',
            'publish_pages', 'covid_main_menu', array($this, 'covid_settings_page'),
            plugins_url('images/m.svg', __FILE__));
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
        $wp_admin_bar->add_node([
            'id' => 'covid',
            'title' => 'COVID Watch',
            'href' => 'https://mmediagroup.fr/covid-19?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=' . get_site_url() . '&utm_content=toolbar',
            'meta' => [
                'target' => '_BLANK',
            ],
        ]);
    }
    /**
     * load textdomain
     *
     * @return void
     */
    public function handle_admin_init()
    {
        // register_nav_menu('covid-menu', __('Covid Menu'));
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
            'my-text', // Base ID
            'COVID-19 confirmed cases' // Name
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
        $response = wp_remote_get('https://covid-api.mmediagroup.fr/v1/cases');
        $body = json_decode($response['body'], true);

        echo $args['before_widget'];

        // if (!empty($instance['title'])) {
        //     echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        // }

        echo '<div class="textwidget">';

        echo number_format($body['Global']['All']['confirmed']) . ' confirmed cases';

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
$Covid = MMedia_Covid::getInstance();
new MMedia_Covid_Widget();
