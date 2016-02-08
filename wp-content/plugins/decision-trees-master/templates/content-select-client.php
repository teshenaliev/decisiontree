<?php defined( 'ABSPATH' ) or die();
$customFields = get_post_custom(); ?>
<div class="cftp-dt-content">
	<div class="select-user-container">
		<h2><?php _e('Client list','cftp_dt');?></h2>
			<?php if(!is_null($user_list) && count($user_list)>0):?>
				<select class="user-list">
					<option value=""><?php _e('Please select user','cftp_dt');?></option>
				<?php foreach($user_list as $singleUser):?>
					<option value="<?php echo $singleUser->ID;?>"><?php echo $singleUser->display_name;?></option>
				<?php endforeach;?>
				</select>
			<?php endif;?>
			<button href="javascript:void(0)" class="select-user-button"><?php _e('Select User','cftp_dt');?></button>
	</div>
	<div class="create-user-container">

		<h2><?php _e('Register Client','');?></h2>
			<?php echo do_shortcode('[nm-wp-registration forceshow=true]');?>
	</div>
</div>