<?php
/**
 * Plugin Name:       Lead Sync - WPForms to Jetpack CRM
 * Plugin URI:        https://wordpress.org/plugins/sync-wpforms-jetcrm/
 * Description:       Use WPForms to collect leads info and save time by automating your lead generation process.
 * Version:           1.0.2
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            Jahidur Nadim
 * Author URI:        https://github.com/nadim1992/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sync-wpforms-jetcrm
 */

// Do not call this file directly.
defined( 'ABSPATH' ) || exit;



// Update version.
register_activation_hook( __FILE__, function() {
    update_option( 'lswjc_version', '1.0.2' );
} );



// Save a lead to the Jetpack CRM when a user comes from WPForms.
function lswjc_save_wpforms_entry_to_jetpack_crm( $fields ) {
    $data = array(
        'zbsc_status' => 'Lead',
        'zbsc_notes'  => 'WPForms Lead Sync',
    );

    foreach( $fields as $field ) {
        if ( 'name' === $field['type'] ) {
            list( $first_name, $last_name ) = explode( ' ', sanitize_text_field( $field['value'] ) );

            $data['zbsc_fname'] = $first_name;
            $data['zbsc_lname'] = $last_name;
        }

        if ( 'email' === $field['type'] ) {
            $data['zbsc_email'] = sanitize_email( $field['value'] );
        }

        if ( 'phone' === $field['type'] ) {
            $data['zbsc_mobtel'] = sanitize_text_field( $field['value'] );
        }

        if ( 'textarea' === $field['type'] ) {
            $data['zbsc_notes'] = sanitize_textarea_field( $field['value'] );
        }

        if ( 'address' === $field['type'] ) {
            $data['zbsc_addr1']    = sanitize_text_field( $field['address1'] );
            $data['zbsc_addr2']    = sanitize_text_field( $field['address2'] );
            $data['zbsc_city']     = sanitize_text_field( $field['city'] );
            $data['zbsc_county']   = sanitize_text_field( $field['state'] );
            $data['zbsc_postcode'] = sanitize_text_field( $field['postal'] );
            $data['zbsc_country']  = sanitize_text_field( $field['country'] );
        }
    }

    if ( empty( $data['zbsc_email'] ) || ! function_exists( 'zeroBS_integrations_addOrUpdateCustomer' ) ) {
        return;
    }

    zeroBS_integrations_addOrUpdateCustomer( 'form', $data['zbsc_email'], $data );
}

add_action( 'wpforms_process_entry_save', 'lswjc_save_wpforms_entry_to_jetpack_crm' );
