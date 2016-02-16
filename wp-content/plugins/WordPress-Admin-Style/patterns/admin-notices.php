<h2><?php esc_attr_e( 'Admin Notices', 'wp_admin_style' ); ?></h2>
<p><?php esc_attr_e( 'define the style via param (same as the classes) on function add_settings_error() or use the class inside a div', 'wp_admin_style' ); ?></p>
<p><?php printf( __( 'Since WordPress version 4.2 give it much more classes and paths. See more information and background in this <a href="%s">post</a>.', 'wp_admin_style' ), 'https://make.wordpress.org/core/2015/04/23/spinners-and-dismissible-admin-notices-in-4-2/' ); ?></p>

<p><strong><?php esc_attr_e( 'HINT: The Admin Notices class was moved on default via wp-admin/js/common.js to the top after the first h2. See on top.', 'wp_admin_style' ) ?></strong></p>

<div class="notice notice-error"><p><?php esc_attr_e( 'class .notice-error with paragraph', 'wp_admin_style' ); ?></p></div>
<div class="notice notice-warning"><p><?php esc_attr_e( 'class .notice-warning with paragraph', 'wp_admin_style' ); ?></p></div>
<div class="notice notice-success"><p><?php esc_attr_e( 'class .notice-success with paragraph', 'wp_admin_style' ); ?></p></div>
<div class="notice notice-info is-dismissible"><p><?php esc_attr_e( 'class .notice-info with paragraph include .is-dismissible class', 'wp_admin_style' ); ?></p></div>

<!-- Deprecated
<div class="updated"><p><?php esc_attr_e( 'class .updated with paragraph, Deprecated', 'wp_admin_style' ); ?></p></div>
<div class="error"><?php esc_attr_e( 'class .error WITHOUT paragraph, Deprecated', 'wp_admin_style' ); ?></div>
<div class="settings-error"><?php esc_attr_e( 'class .settings-error WITHOUT paragraph, Deprecated', 'wp_admin_style' ); ?></div>
<div class="error form-invalid"><?php esc_attr_e( 'class .error and .form-invalid WITHOUT paragraph, Deprecated', 'wp_admin_style' ); ?></div>
<div class="notice"><p><?php esc_attr_e( 'class .notice only with paragraph, Deprecated', 'wp_admin_style' ); ?></p></div>
-->
