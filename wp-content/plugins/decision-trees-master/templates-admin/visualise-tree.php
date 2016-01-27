<?php function outputTree($post){
	$outputString = "[{v:'".$post->ID."',f:'<h3>".$post->post_title."</h3>";
	//price
	$outputString .= (isset($post->metadata['Price'][0]) && $post->metadata['Price'][0]>0) ? '<p>$' . $post->metadata['Price'][0] . '</p>':'';
	//buttons
	$outputString .= '<div class="button-group"><a href="'. get_bloginfo('url').'/wp-admin/post-new.php?post_type=decision_node&parent_id='.$post->ID.'" class="button button-small">Add New</a><a href="'.get_edit_post_link( $post->ID ).'" class="button button-small">Edit</a></div>\'}';
	//parent
	$outputString .= ", '".$post->post_parent."'],\n";

	echo $outputString;
	if (isset($post->children) && count($post->children)>0){
		foreach($post->children as $key=>$singlePost){
    		outputTree($singlePost);
    	}
	}	
}
?>
<div class="wrap">

	<script type="text/javascript">
      google.charts.load('current', {packages:["orgchart"]});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'Manager');

        // For each orgchart box, provide the name, manager, and tooltip to show.
        data.addRows([
        	<?php 
        	foreach($tree as $key=>$singlePost){
        		outputTree($singlePost);
        	};?>
        	]
        );

        // Create the chart.
        var chart = new google.visualization.OrgChart(document.getElementById('cftp_dt_visualiser'));
        // Draw the chart, setting the allowHtml option to true for the tooltips.
        chart.draw(data, {allowHtml:true,allowCollapse:true});
      }
   </script>
	<?php screen_icon(); ?>
	<h2><?php _e( 'Decision Tree Nodes', 'cftp_dt' ); ?></h2>

	<div id="cftp_dt_visualiser">

		

	</div>
</div>
