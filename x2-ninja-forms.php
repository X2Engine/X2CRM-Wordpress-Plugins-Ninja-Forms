<?php
/**
 * Ninja Forms - X2CRM
 *
 * @package     Ninja Forms - X2CRM
 * @author      Raymond Colebaugh <raymond@x2engine.com>
 * @copyright   2016 X2Engine, Inc.
 * @license     GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name: Ninja Forms - X2CRM
 * Plugin URI:  
 * Description: Provides a custom Ninja Form action for submitting form data to the X2CRM REST API.
 * Version:     1.0.0
 * Author:      X2Engine, Inc.
 * Author URI:  https://www.x2crm.com
 * Text Domain: ninja-forms-x2crm
 * License:     GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the new tracking key template field
 */
function register_x2_ninja_tracker_form_fields() {
    $argsTracker = array(
        'name' => 'X2 Tracking Key',
        'display_function' => 'display_x2_tracker_field',
        'sidebar' => 'template_fields',
        'display_label' => false,
        'display_wrap' => false,
    );

    if (function_exists('ninja_forms_register_field')) {
        ninja_forms_register_field('x2_tracker_fields', $argsTracker);
    }
}

/**
 * Render the hidden tracking key field
 */
function display_x2_tracker_field() {
    global $post;

    if(!empty($post)) {
        ?>
            <input type="hidden" name="x2_key" />
        <?php
    }
}

/**
 * Register the new X2CRM API action
 */
function x2crm_api_nf_action( $types ) {
    $types['x2crm-api'] = require_once (plugin_dir_path( __FILE__ ) . "actions/x2-ninja-action.php");
    return $types;
}

add_action('init', 'register_x2_ninja_tracker_form_fields');
add_filter('nf_notification_types', 'x2crm_api_nf_action');
?>
