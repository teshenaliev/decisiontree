<?php
function printUserQuestionnaireTree($tempUserData, $level)
{
	foreach($tempUserData as $key=>$singleQuestionnaire):
		$editable = (isset($singleQuestionnaire['required']) && $singleQuestionnaire['required']==1 && $singleQuestionnaire['ignore'] != 1 ) ? true : false;
		?>
		<tr>
			<td class="level-<?php echo $level;?>"><?php echo $singleQuestionnaire['ID'];?></td>
			<td><?php if ($editable==true):?>
				<a href="http://<?php  echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '&post_id=' .  $singleQuestionnaire['ID'];?>"><?php echo $singleQuestionnaire['post_title'];?></a>
				<?php else:?>
					<?php echo $singleQuestionnaire['post_title'];?>
				<?php endif;?>
			</td>
			<td><?php if (isset($singleQuestionnaire['selectable']) && $singleQuestionnaire['selectable']==1):?>
					<?php _e('Selectable','cftp_dt');?>
				<?php elseif(isset($singleQuestionnaire['sequence']) && $singleQuestionnaire['sequence']==1):?>
					<?php _e('Sequence','cftp_dt');?>
				<?php elseif(isset($singleQuestionnaire['required']) && $singleQuestionnaire['required']==1):?>
					<?php _e('Question','cftp_dt');?>
				<?php endif;?>
			</td>
			<td><?php echo $singleQuestionnaire['visited']==1 ?'yes' : 'no';?></td>
			<td><?php echo $singleQuestionnaire['value'];?></td>
			<td><?php if (isset($singleQuestionnaire['skip']) && $singleQuestionnaire['skip']==1):?>
					<?php _e('Skipped','cftp_dt');?>
				<?php elseif(isset($singleQuestionnaire['ignore']) && $singleQuestionnaire['ignore']==1):?>
					<?php _e('Ignored','cftp_dt');?>
				<?php elseif (isset($singleQuestionnaire['required']) && $singleQuestionnaire['required']==1 && $singleQuestionnaire['value']>0):?>
					<?php _e('Asnwered','cftp_dt');?>
				<?php endif;?></td>
			<td><?php echo $level;?></td>
		</tr>
		<?php if (isset($singleQuestionnaire['children'])){
			printUserQuestionnaireTree($singleQuestionnaire['children'], $level+1);
		}
		endforeach;
}?>
<div class="wrap">
	<h2><?php _e( 'Questionnaire list', 'cftp_dt' ); ?> <a href="http://<?php  echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] .'&export=true';?>" class="page-title-action"><?php _e( 'Export report','cftp_dt' ); ?></a></h2>

	<div id="cftp_dt_questionnaire-list-container">
	<table class="widefat fixed striped pages">
	<thead>
		<tr>
			<th class="row-title"><?php _e('ID','cftp_dt');?></th>
			<th class="row-title"><?php _e('Title','cftp_dt');?></th>
			<th class="row-title"><?php _e('Type','cftp_dt');?></th>
			<th class="row-title"><?php _e('Visited','cftp_dt');?></th>
			<th class="row-title"><?php _e('Value','cftp_dt');?></th>
			<th class="row-title"><?php _e('Status','cftp_dt');?></th>
			<th class="row-title"><?php _e('Action','cftp_dt');?></th>
		</tr>
	</thead>
	<tbody>
		<?php printUserQuestionnaireTree($questionnaireUserMeta, 0 );?>
	</tbody>
	</table>
	</div>
</div>
