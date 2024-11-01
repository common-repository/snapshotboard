<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

class SnapShotBoard_Admin {

    static $snapshotboard_wp_settings = [];
    private static $DATABASE_VERSION = '1';
    private static $RESOURCES_VERSION = '14';
    public static $SAVE_CONFIG_NONCE_KEY = 'ssb_config_page_nonce';
    public static $SAVE_CONFIG_NONCE_ACTION = 'ssb_config_page';
    private static $arrSubMenuPages = array();
    private static $arrMenuPages = array();

    public static function init() {

        // If the user is trying to save the form, require a valid nonce or die
        $postdata = filter_input_array(INPUT_POST);
        if (!empty($postdata) && array_key_exists('ssb_username', $postdata) && array_key_exists('ssb_password', $postdata)) {
            check_admin_referer(SnapShotBoard_Admin::$SAVE_CONFIG_NONCE_ACTION, SnapShotBoard_Admin::$SAVE_CONFIG_NONCE_KEY);
            SnapShotBoard_Admin::connect($postdata);
        }

        add_action('admin_menu', array(__CLASS__, 'add_admin_page'));
    }

    // -- Connect

    public static function connect($postdata) {
        if (!empty($postdata) && array_key_exists('ssb_username', $postdata) && array_key_exists('ssb_password', $postdata)) {
            $url = "https://snapshotboard.com/token.php?username=" . urlencode($postdata["ssb_username"]) . "&password=" . urlencode($postdata["ssb_password"]);
            $json = file_get_contents($url);
            if (!empty($json)) {
                $data = json_decode($json);
                if (!empty($data->access_token) && !empty($data->refresh_token) && !empty($data->expires_in)) {
                    SnapShotBoard_Admin::ssb_database_store_connection($data->access_token, $data->refresh_token, $data->expires_in, wp_get_current_user()->data->user_email);
                }
            }
        }
    }

    // -- Settings

    public static function save_config_page($config) {

        self::$snapshotboard_wp_settings = SnapShotBoard::get_ssbcom_settings();

        $stringSettings = array("api_key");
        SnapShotBoard_Admin::setStringSettings(self::$snapshotboard_wp_settings, $config, $stringSettings);
        SnapShotBoard::save_ssbcom_settings(self::$snapshotboard_wp_settings);

        return self::$snapshotboard_wp_settings;
    }

    public static function setBooleanSettings(&$snapshotboard_wp_settings, &$config, $settings) {
        foreach ($settings as $setting) {
            if (array_key_exists($setting, $config)) {
                $snapshotboard_wp_settings[$setting] = true;
            } else {
                $snapshotboard_wp_settings[$setting] = false;
            }
        }
    }

    public static function setStringSettings(&$snapshotboard_wp_settings, &$config, $settings) {
        foreach ($settings as $setting) {
            if (array_key_exists($setting, $config)) {
                $value = $config[$setting];
                //$normalized_value = WSBPNUtils::normalize($value);
                $snapshotboard_wp_settings[$setting] = $value;
            }
        }
    }

    public static function setFloatSettings(&$snapshotboard_wp_settings, &$config, $settings) {
        foreach ($settings as $setting) {
            if (array_key_exists($setting, $config)) {
                $value = $config[$setting];
                $valuenormalized = str_replace(",", ".", $value);
                $snapshotboard_wp_settings[$setting] = floatval($valuenormalized);
            }
        }
    }

    public static function setSettings(&$snapshotboard_wp_settings, &$config, $settings) {
        foreach ($settings as $setting) {
            if (array_key_exists($setting, $config)) {
                $value = $config[$setting];
                $snapshotboard_wp_settings[$setting] = intval($value);
            }
        }
    }

    // -- Menus

    public static function add_admin_page() {

        global $ssb_screens;
        $role = "manage_options";

        self::addMenuPage('SnapShotBoard', 'admin_dashboard');
        //self::addSubMenuPage('Connect', 'admin_connection', "snapshotbaord-connection");

        foreach (SnapShotBoard_Admin::$arrMenuPages as $menu) {
            $title = $menu["title"];
            $pageFunctionName = $menu["pageFunction"];
            $SnapShotBoard_menu = add_menu_page($title, $title, $role, 'snapshotboard-admin', array(__CLASS__, $pageFunctionName), 'dashicons-format-gallery');
            add_action('load-' . $SnapShotBoard_menu, array(__CLASS__, 'admin_custom_load'));
            $ssb_screens[] = $SnapShotBoard_menu;
        }

        foreach (self::$arrSubMenuPages as $menu) {
            $title = $menu["title"];
            $pageFunctionName = $menu["pageFunction"];
            $pageSlug = $menu["pageSlug"];
            $SnapShotBoard_menu = add_submenu_page('snapshotboard-admin', $title, $title, $role, $pageSlug, array(__CLASS__, $pageFunctionName));
            add_action('load-' . $SnapShotBoard_menu, array(__CLASS__, 'admin_custom_load'));
            $ssb_screens[] = $SnapShotBoard_menu;
        }
    }

    public static function admin_custom_load() {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_custom_scripts'));
        self::$snapshotboard_wp_settings = SnapShotBoard::get_ssbcom_settings();
    }

    public static function admin_custom_scripts() {
        wp_enqueue_style('icons', plugin_dir_url(__FILE__) . 'css/admin.css', false, SnapShotBoard_Admin::$RESOURCES_VERSION);
    }

    protected static function addMenuPage($title, $pageFunctionName) {
        self::$arrMenuPages[] = array("title" => $title, "pageFunction" => $pageFunctionName);
    }

    protected static function addSubMenuPage($title, $pageFunctionName, $pageSlug) {
        self::$arrSubMenuPages[] = array("title" => $title, "pageFunction" => $pageFunctionName, "pageSlug" => $pageSlug);
    }

    public static function admin_dashboard() {
        require_once( plugin_dir_path(__FILE__) . '/views/dashboard.php' );
    }

    public static function admin_connection() {
        $connection = SnapShotBoard_Admin::ssb_database_get_connection(wp_get_current_user()->data->user_email);
        if (!empty($connection)) {
            require_once( plugin_dir_path(__FILE__) . '/views/connection.php' );
        }
    }

    // database

    public static function ssb_database_install() {

        global $wpdb;

        $table_name = SnapShotBoard_Admin::ssb_database_tablename();

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime NOT NULL,
		email varchar(255) NOT NULL,
		access_token varchar(64) NOT NULL,
                refresh_token varchar(64) NOT NULL,
                expires datetime NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);

        add_option('ssb_db_version', SnapShotBoard_Admin::$DATABASE_VERSION);
    }

    public static function ssb_database_get_connection($email) {
        $table_name = SnapShotBoard_Admin::ssb_database_tablename();
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM $table_name WHERE email = '" . $email . "'");
    }

    public static function ssb_database_store_connection($access_token, $refresh_token, $expires_in, $email) {
        $table_name = SnapShotBoard_Admin::ssb_database_tablename();
        global $wpdb;
        $existing = $wpdb->get_row("SELECT * FROM $table_name WHERE email = '" . $email . "'");
        $dbdata = array(
            'time' => current_time('mysql'),
            'email' => $email,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires' => date("Y-m-d H:i:s", time() + $expires_in),
        );
        if (empty($existing)) {
            $wpdb->insert($table_name, $dbdata);
        } else {
            $wpdb->update($table_name, $dbdata, array("id" => $existing->id));
        }
    }

    public static function ssb_database_tablename() {
        global $wpdb;
        return $wpdb->prefix . 'ssb_users';
    }

    // initializer

    public function __construct() {
        
    }

}
