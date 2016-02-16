<?php defined( 'ABSPATH' ) or die(); ?>

<ul id="cftp-dt-next">
	<?php 
	$selectableExists = false;
	$sequenceExists = false;
	foreach ( $answers as $answer_id => $answer ) :
		$selectableExists = $answer->get_meta_by_key('selectable')== 1 ? true : $selectableExists;
		$sequenceExists = $answer->get_meta_by_key('sequence')== 1 ? true : $sequenceExists;
		if ( !( $provider = $this->get_answer_provider( $answer->get_answer_type() ) ) ) {
			# @TODO this should probably raise a warning or something
			continue;
		}
		?>
		<li class="cftp-dt-next-answer">
			<?php echo $provider->get_answer( $answer ); ?>
		</li>
	<?php endforeach; ?>
</ul>
<?php if ($selectableExists == 1):?>
	    	<button class="btn btn-default selectable-continue-button"><?php _e('Continue','cftp_dt');?></button>
<?php elseif ($sequenceExists == 1):?>
	    	<!--<button class="btn btn-default sequence-continue-button"><?php _e('Continue','cftp_dt');?></button>-->
<?php endif;?>
