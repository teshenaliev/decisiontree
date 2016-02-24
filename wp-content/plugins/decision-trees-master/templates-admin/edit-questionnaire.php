<?php if ($update_successfull === true):?>
<div class="notice notice-success"><p><?php _e('Values saved','cftp_dt');?></p></div>
<?php elseif ($update_successfull === false):?>
<div class="notice notice-error"><p><?php _e('Values not saved','cftp_dt');?></p></div>
<?php endif;?>
<div class="wrap">
	<h2><?php _e( 'Edit Questionnaire', 'cftp_dt' ); ?></h2>

	<div id="cftp_dt_questionnaire-edit-container">
	<form method="post" action="<?php echo get_permalink(); ?>">
		<input type="hidden" name="client_id" value="<?php echo $current_user->data->ID?>"/>
		<input type="hidden" name="post_id" value="<?php echo $current_question_user_meta['ID']?>"/>
		<table class="widefat fixed striped pages">
		<thead>
			<tr>
				<th><?php _e('Title','cftp_dt');?></th>
				<th><?php echo $current_question_user_meta['post_title'];?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php _e('Value','cftp_dt');?></td>
				<td><input name="value" type="number" value="<?php echo $current_question_user_meta['value']?>"/></td>
			</tr>
			<tr>
				<td><?php _e('Price','cftp_dt');?></td>
				<td><input name="price" type="number" step="0.01" value="<?php echo $current_question_user_meta['price']?>"/></td>
			</tr>
			<tr>
				<td colspan="2"><input class="button-primary" type="submit" value="<?php _e( 'Save','cftp_dt' ); ?>" /> <a class="button-secondary" href="?post_type=decision_node&page=quesionnaire_list&user_id=<?php echo $current_user->data->ID?>"><?php _e( 'Return to questionnaire list','cftp_dt' ); ?></a></td>
			</tr>
		</tbody>
		</table>
	</form>
	</div>
</div>
