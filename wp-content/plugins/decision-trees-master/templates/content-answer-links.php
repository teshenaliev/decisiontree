<?php defined( 'ABSPATH' ) or die(); ?>
<?php if ($_SESSION['questionnaire_view_mode']=='list_view'):
	if (count($answers)>0):?>
	<table class="cftp-dt-answers table table-striped">

		<tr>
			<th><?php _e('Title');?></th>
			<th><?php _e('Value');?></th>
			<th><?php _e('Note');?></th>
			<th><?php _e('Action');?></th>
		</tr>
	<?php 
	$selectableExists = false;
	$sequenceExists = false;
	foreach ( $answers as $answer_id => $answer ) :
		$selectableExists = $answer->get_meta_by_key('selectable')== 1 ? true : $selectableExists;
		$sequenceExists = $answer->get_meta_by_key('sequence')== 1 ? true : $sequenceExists;
		$questionUserMeta = $answer->get_user_meta();
		if ( !( $provider = $this->get_answer_provider( $answer->get_answer_type() ) ) ) {
			# @TODO this should probably raise a warning or something
			continue;
		}
		?>
		<?php if (!isset($questionUserMeta['ignore']) || $questionUserMeta['ignore']!=1):?>
			<tr>
				<td>

				<?php if ($answer->get_meta_by_key('selectable')==1):?>
					<?php if (isset($questionUserMeta['selected']) && $questionUserMeta['selected']=='1'):?>
						<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
						<a href="<?php echo get_permalink( $answer->get_page_id() )?>"><?php echo $answer->get_page_title(); ?></a>
					<?php else:?>
						<input id="decision-tree-<?php echo $answer->get_page_id();?>" type="checkbox">
						<label for="decision-tree-<?php echo $answer->get_page_id();?>">
		                    <?php echo $answer->get_page_title(); ?>
	                	</label>
					<?php endif;?>
				<?php else:?>
	                <?php echo $answer->get_page_title(); ?>
				<?php endif;?>
					</td>
				<td>
				<?php if ($answer->get_meta_by_key('required')==1):?>
					<input type="hidden" class="question_id " value="<?php echo $answer->get_page_id();?>"/>
					<input type="number" class="question_value " value="<?php echo (isset($questionUserMeta['value'])) ? $questionUserMeta['value']:'';?>"/>
				<?php endif;?>
				</td>
				<td>
					<?php if ($answer->get_meta_by_key('required')==1):?>
						<input type="text"  class="question_additional_note" value="<?php echo (isset($questionUserMeta['additional_note'])) ? $questionUserMeta['additional_note']:'';?>"/></td>
					<?php endif;?>
				<td class="list-view-action-column">
					<?php if ($answer->get_meta_by_key('required')==1):?>
						<a href="javascript:void(0)" class="btn btn-primary action-add"><?php _e('Save','cftp_dt')?></a>
						<a href="javascript:void(0)" class="btn btn-danger action-ignore"><?php _e('Ignore','cftp_dt')?></a>
					<?php endif;?>
				</td>
			</tr>
		<?php endif;?>
	<?php endforeach; ?>
	</table>
	<div id="dialog" title="Value Saved" style="display:none">
	  <p>Value is Saved</p>
	</div>
<?php endif;?>
<?php else:?>
<ul id="cftp-dt-next">
	<?php 
	$selectableExists = false;
	$sequenceExists = false;
	foreach ( $answers as $answer_id => $answer ) :
		$selectableExists = $answer->get_meta_by_key('selectable')== 1 ? true : $selectableExists;
		$sequenceExists = $answer->get_meta_by_key('sequence')== 1 ? true : $sequenceExists;
		$questionUserMeta = $answer->get_user_meta();
		if ( !( $provider = $this->get_answer_provider( $answer->get_answer_type() ) ) ) {
			# @TODO this should probably raise a warning or something
			continue;
		}
		?>
		<?php if (!isset($questionUserMeta['ignore']) || $questionUserMeta['ignore']!=1):?>
		<li class="cftp-dt-next-answer">
			<?php echo $provider->get_answer( $answer ); ?>
		</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
<?php endif;?>


<?php if ($selectableExists == 1):?>
	    	<button class="btn btn-default selectable-continue-button"><?php _e('Continue','cftp_dt');?></button>
<?php elseif ($sequenceExists == 1):?>
	    	<!--<button class="btn btn-default sequence-continue-button"><?php _e('Continue','cftp_dt');?></button>-->
<?php endif;?>
