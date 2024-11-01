<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


defined('ABSPATH') or die('This page may not be accessed directly.');

/* if (!SSBUtils::can_modify_plugin_settings()) {
  // Exit if the current user does not have permission
  die('Insufficient permissions to access config page.');
  } */

if (!empty($connection)) {
    die("allready set");
}
?>

<div id="viewWrapper" class=view_wrapper">
    <form name="form_general_settings" id="form_general_settings" method="POST" action="#">
        <div class="wrap">
            <div class="title_line" style="height:auto; min-height:50px; margin-bottom:10px;">
                <div class="view_title">
                    Connect SnapShotBoard			
                </div>
                <div style="width:100%;height:1px;float:none;clear:both"></div>
            </div>

            <div id="ssb-global-settings-dialog-wrap">
                <?php
                // Add an nonce field so we can check for it later.
                wp_nonce_field(SnapShotBoard_Admin::$SAVE_CONFIG_NONCE_ACTION, SnapShotBoard_Admin::$SAVE_CONFIG_NONCE_KEY, true);
                ?>
                <div class="ssb-global-setting">
                    <div class="ssb-gs-tc">
                        <label><?php _e("SnapShotBoard user name:", 'ssbcom'); ?></label>
                    </div>
                    <div class="ssb-gs-tc">
                        <input type="text" class="regular-text" id="pages_for_includes" name="ssb_username" value="">
                    </div>
                    <div class="ssb-gs-tc">
                        <i style=""><?php _e("Please enter your SnapShotBoard user name. This is NOT your WordPress user name!", 'ssbcom'); ?></i>
                    </div>
                </div>
                <div class="ssb-global-setting">
                    <div class="ssb-gs-tc">
                        <label><?php _e("SnapShotBoard Password:", 'ssbcom'); ?></label>
                    </div>
                    <div class="ssb-gs-tc">
                        <input type="password" class="regular-text" id="pages_for_includes" name="ssb_password" value="">
                    </div>
                    <div class="ssb-gs-tc">
                        <i style=""><?php _e("Please enter your SnapShotBoard password. This is NOT your WordPress password!", 'ssbcom'); ?></i>
                    </div>
                </div>
            </div>
        </div>
        <p>
            <button id="button_save_general_settings" class="button-primary ssbgreen" type="submit"><?php _e("Save settings", 'ssbcom'); ?></button>
        </p>
    </form>
</div>
