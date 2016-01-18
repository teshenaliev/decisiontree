<?php defined( 'ABSPATH' ) or die(); ?>

<?php //print_r(get_post_types()); ?>
<?php //print_r(get_post_custom()); ?>
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

			<?php 
				$answer = new CFTP_DT_Answer( get_the_ID() );
				$provider = $this->get_answer_provider( $answer->get_answer_type() );
			?>

			<h3 class="cftp-dt-current"><?php echo $title; ?></h3>

		</li>

</ol>

<div class="cftp-dt-content">

	<?php 
		$answer = new CFTP_DT_Answer( get_the_ID() );
		$provider = $this->get_answer_provider( $answer->get_answer_type() );
		$customFields = get_post_custom();
	?>

	<div class="cftp-dt-content"><?php echo $content; ?></div>
	<?php if (isset($customFields['Price'])):?>
	<div class="cftp-dt-add-to-selection">

	<div class="input-group input-group-lg">
		<span class="input-group-addon" id="basic-addon1">$</span>
      	<input type="number" class="form-control" value="<?php echo $customFields['Price'][0];?>" disabled>
      	<span class="input-group-btn">
			<a href="javascript:void(0)" class="btn btn-primary">Add to selection</a></div>
      	</span>
    </div>
	<?php endif;?>

	<?php if ( $answer_links ) : echo $answer_links; endif; ?>

</div>
