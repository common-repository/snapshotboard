<?php
defined('ABSPATH') or die('This page may not be accessed directly.');

/**
 * Plugin Name: SnapShotBoard
 * Description: SnapShotBoard WordPress Plugin.
 * Version: 0.2.2
 * Author: Grizzly New Technologies GmbH
 * Author URI: https://grizzlynt.com
 * License: MIT
 */
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

define('SNAPSHOTBOARD_PLUGIN_URL', plugin_dir_url(__FILE__));

$snapshotboard_wp_settings = [];
$ssb_screens = array();



require_once( plugin_dir_path(__FILE__) . 'snapshotboard-settings.php' );
require_once( plugin_dir_path(__FILE__) . 'snapshotboard-admin.php' );
require_once( plugin_dir_path(__FILE__) . 'snapshotboard-widget.php' );

add_action('init', 'ssbcom_init');
//add_action('widgets_init', 'ssbcom_widgetsinit');
add_action('init', array('SnapShotBoard_Admin', 'init'));
register_activation_hook(__FILE__, 'ssbcom_db_init');

function ssbcom_init() {
    ssbcom_shortcodes_init();
}

if (false) {
    add_action('admin_notices', 'my_acf_notice');
}

function my_acf_notice() {
    ?>
    <div class="update-nag notice">
        <p><?php _e('Please install Advanced Custom Fields, it is required for this plugin to work properly!', 'my_plugin_textdomain'); ?></p>
    </div>
    <?php
}

function ssbcom_widgetsinit() {
    register_widget('SnapShotBoard_Widget');
}

function ssbcom_db_init() {
    SnapShotBoard_Admin::ssb_database_install();
}

// --- SHORTCODES

function ssbcom_shortcodes_init() {

    function ssbcom_shortcode($atts = [], $content = null) {
        if (!empty($atts['id'])) {
            if (!empty($atts['type']) && $atts['type'] == "breaker") {
                $content .= '<div id="ssbhomepagewidget"><a id="ws-loading" href="https://about.snapshotboard.com/">Social Wall</a></div>';
                $content .= '<script>var wsOptions={wrapperdiv : "ssbhomepagewidget", iswidget: 1, ws : "ssb/' . $atts['id'] . '"};</script>';
                $content .= '<script src="https://static.snapshotboard.com/js/embed.js"></script>';
            } else if (!empty($atts['type']) && $atts['type'] == "carousel") {
                $content .= '<div id="ws-embed"><a id="ws-loading" href="https://about.snapshotboard.com/">Social Wall</a></div>';
                $content .= '<script>var wsOptions={wrapperdiv : "ws-embed", displayType: "carousel", ws : "ssb/' . $atts['id'] . '"};</script>';
                $content .= '<script src="https://static.snapshotboard.com/v2/embed.js"></script>';
            } else if (!empty($atts['type']) && $atts['type'] == "ticker") {
                $content .= '<div id="ws-embed"><a id="ws-loading" href="https://about.snapshotboard.com/">Social Wall</a></div>';
                $content .= '<script>var wsOptions={wrapperdiv : "ws-embed", displayType: "ticker", ws : "ssb/' . $atts['id'] . '"};</script>';
                $content .= '<script src="https://static.snapshotboard.com/v2/embed.js"></script>';
            } else {
                $content .= '<div id="ws-embed"><a id="ws-loading" href="https://about.snapshotboard.com/">Social Wall</a></div>';
                $content .= '<script>var wsOptions = {ws : "ssb/' . $atts['id'] . '", fixedHeight : false, category : 0};</script>';
                $content .= '<script src="https://static.snapshotboard.com/v2/embed.js"></script>';
            }
        } else {
            $content = "<span style='color:#ff0000;'>ERROR> Correct format for embedding the social wall: [snapshotboard id=XXXXX], where XXXXX is the id of your wall.</span>";
        }
        return $content;
    }

    add_shortcode('snapshotboard', 'ssbcom_shortcode');
}
