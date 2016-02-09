
<div class="wrap">
	<h2><?php _e( 'Questionnaire list', 'cftp_dt' ); ?></h2>

	<div id="cftp_dt_questionnaire-list-container">
	<table class="widefat fixed striped pages">
	<thead>
		<tr>
			<th class="row-title"><?php _e('Quesionnaire name','cftp_dt');?></th>
			<th class="row-title"><?php _e('Action','cftp_dt');?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($questionnaire_list as $key=>$singleQuestionnaire):?>
		<tr>
			<th><a href="http://<?php  echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '&user_id=' .  $singleQuestionnaire;?>"><?php echo $singleQuestionnaire;?></a></th>
			<th>aaaa</th>
		</tr>
		<?php endforeach;?>
	</tbody>
	</table>
	</div>
</div>
