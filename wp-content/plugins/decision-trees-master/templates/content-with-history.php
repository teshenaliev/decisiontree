<?php defined( 'ABSPATH' ) or die();
$customFields = get_post_meta($post->ID);
?>
<?php if ( $start ) : ?>
	<div class="cftp-dt-restart">
		<a href="<?php echo get_permalink( $start->ID ); ?>">restart</a>
	</div>
<?php endif; ?>
<ol id="cftp-dt-answers">

	<?php
		foreach ( $previous_answers as $previous_answer ) :
			$previous_answer = get_post( $previous_answer );
			if ( ! $previous_answer->post_parent )
				continue;
			$previous_answer_parent = get_post( $previous_answer->post_parent );
			$answer = new CFTP_DT_Answer( $previous_answer->ID );
			$provider = $this->get_answer_provider( $answer->get_answer_type() );
		?>
			<li class="cftp_dt_prev_answer">
				<a href="<?php echo $provider->get_edit_answer_url( $answer ); ?>"><h3 class="cftp-dt-node-title"><?php echo $previous_answer_parent->post_title; ?></h3></a>
				&#10097;
			</li>
	<?php endforeach; ?>
		<li class="cftp-dt-current">
			<h3 class="cftp-dt-current"><?php echo $title; ?></h3>
		</li>

</ol>

<div class="cftp-dt-content">
	<div class="cftp-dt-content">
		<?php echo $content; ?>
	</div>
	<?php if (isset($customFields['Price'][0]) && $customFields['Price'][0]>0 && is_numeric($customFields['Price'][0])):?>
	<div class="cftp-dt-add-to-selection">

	<div class="input-group input-group-lg">
		<span class="input-group-addon" id="basic-addon1">$</span>
      	<input type="number" class="form-control" value="<?php echo $customFields['Price'][0];?>" disabled>
      	
      	<?php if (function_exists('wpfp_link')):?>
      	<span class="input-group-btn">
			<?php echo wpfp_link(1, '', false);?>
      	</span>
		<?php endif;?>

    </div>
	<?php endif;?>
    <?php if (isset($customFields['required']) && $customFields['required'][0]=='1'):
    ?>
	<div class="cftp-dt-add-value">

		<div class="input-group input-group-lg">

			<span class="input-group-addon" id="basic-addon1">units</span>
	      	<input type="number" class="form-control" value="<?php echo (isset($question_user_meta['value']) && $question_user_meta['value']>0) ? $question_user_meta['value'] : '';?>">
	      	
	      	<?php if (function_exists('wpfp_link')):?>
	      	<span class="input-group-btn">
				<?php echo wpfp_link(1, '', false);?>
				<a href="javascript:void(0)" class="btn btn-warning action-skip"><?php _e('Skip','cftp_dt')?></a>
				<a href="javascript:void(0)" class="btn btn-danger action-ignore"><?php _e('Ignore','cftp_dt')?></a>
	      	</span>
			<?php endif;?>

	    </div>

		<div class="input-group input-group-lg additional-note-group">

			<span class="input-group-addon" id="basic-addon1">Notes</span>
	      	<input type="text" class="form-control additional-note" value="<?php echo (isset($question_user_meta['additional_note'])) ? $question_user_meta['additional_note'] : '';?>">

	    </div>
    </div>
	<?php endif;?>

	<?php if ( $answer_links ) : echo $answer_links; endif; ?>

</div>
