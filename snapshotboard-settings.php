<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

class SnapShotBoard {

    public static function get_ssbcom_settings() {
               
        /*
          During first-time setup, all the keys here will be created with their
          default values, except for keys with value 'CALCULATE_LEGACY_VALUE' or
          'CALCULATE_SPECIAL_VALUE'. These special keys aren't created until further
          below.
         */
        $defaults = array(
            'api_key' => "",
        );

        // If not set or empty, load a fresh empty array
        if (!isset($snapshotboard_wp_settings)) {
            $snapshotboard_wp_settings = get_option("SnapShotBoard_Settings");
            if (empty($snapshotboard_wp_settings)) {
                $snapshotboard_wp_settings = array();
            }
        }

        // Assign defaults if the key doesn't exist in settings
        // Except for those with value CALCULATE_LEGACY_VALUE -- we need special logic for legacy values that used to exist in previous plugin versions
        reset($defaults);
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $snapshotboard_wp_settings)) {
                $snapshotboard_wp_settings[$key] = $value;
            }
        }

        return $snapshotboard_wp_settings;
    }

    public static function save_ssbcom_settings($settings) {
        $snapshotboard_wp_settings = $settings;
        update_option("SnapShotBoard_Settings", $snapshotboard_wp_settings);
    }

}

?>