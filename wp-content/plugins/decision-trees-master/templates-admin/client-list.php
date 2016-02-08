
<div class="wrap">
	<h2><?php _e( 'Client list', 'cftp_dt' ); ?></h2>

	<div id="cftp_dt_questionnaire-list-container">
	<table class="widefat fixed striped pages">
	<thead>
		<tr>
			<th class="row-title"><?php _e('Client name','cftp_dt');?></th>
			<th class="row-title"><?php _e('Address','cftp_dt');?></th>
			<th class="row-title"><?php _e('Action','cftp_dt');?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($user_list as $key=>$singleUser):?>
		<tr>
			<th><a href="http://<?php  echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '&user_id=' .  $singleUser->ID;?>"><?php echo $singleUser->display_name;?></a></th>
			<th><?php echo $singleUser->meta_data['wp_client_address'][0];?></th>
			<th>aaaa</th>
		</tr>
		<?php endforeach;?>
	</tbody>
	</table>
	</div>
</div>
