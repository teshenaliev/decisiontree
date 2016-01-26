<?php defined( 'ABSPATH' ) or die();
?>
<p class="description">
	<?php _e( 'Tip: Here you can set the answer value.', 'cftp_dt' ); ?>
</p>

<div id="cftp_dt_current_answer">


		<div class="add_answer" data-answer-type="<?php echo esc_attr( $provider_name ); ?>">
			<div class="answer_field">
				Current Answer:
			</div>
			<div class="answer_title">
				<input type="text" class="regular-text" name="_cftp_dt_answer_value[simple][text]" value="<?php echo $meta_data['_cftp_dt_answer_value'][0]?>" placeholder="<?php esc_attr_e( 'Answer', 'cftp_dt' ); ?>" />
			</div>
		</div>

</div>
