<?php defined( 'ABSPATH' ) or die();
$customFields = get_post_custom(); ?>
<div class="cftp-dt-content">
	<div class="select-user-container">
		<h2><?php _e('Please, log in','cftp_dt');?></h2>
		<p>Dear user, Please log in in order to fill questionnaire.</p>
		<a href="<?php echo wp_login_url( get_permalink() ); ?>" title="Login">Login</a>
</div>