<?php defined( 'ABSPATH' ) or die();
?>
<p class="description">
	<?php _e( 'Tip: Here you can set the answer value.', 'cftp_dt' ); ?>
</p>

<div id="cftp_dt_current_answer">
		<?php if (isset($_GET['parent_id']) && $_GET['parent_id']>0):?>
		<script type="text/javascript">
			jQuery(function(){
				jQuery('#parent_id').val(<?php echo $_GET['parent_id'];?>);
			});
		</script>
		<?php endif; ?>

		<div class="add_answer" data-answer-type="<?php echo esc_attr( $provider_name ); ?>">
			<div class="answer_field">
				Current Answer:
			</div>
			<div class="answer_title">
				<input type="text" class="regular-text" name="_cftp_dt_answer_value[simple][text]" value="<?php echo $meta_data['_cftp_dt_answer_value'][0]?>" placeholder="<?php esc_attr_e( 'Answer', 'cftp_dt' ); ?>" />
			</div>
		</div>

</div>
