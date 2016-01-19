<?php
echo "<ul>";
if ($favorite_post_ids) {
	$c = 0;
	$favorite_post_ids = array_reverse($favorite_post_ids);
    $total = 0;
    if (count($favorite_post_ids )>0){ ?>
        <table class="table table-condensed table-striped">
        <thead>
            <tr>
                <th>Selection</th>
                <th>Price</th>
                <th>Action</th> 
            </tr> 
        </thead>
        <tbody>
        <?php foreach ($favorite_post_ids as $post_id) {
            $customFields = get_post_custom($post_id);
            if ($c++ == $limit) break;
            $p = get_post($post_id);?>
            <tr>
                <td><a href="<?php echo get_permalink($post_id);?> " title="<?php echo $p->post_title ?>"><?php echo  $p->post_title ;?></a></td>
                <td><?php if (isset($customFields['Price'])){
                    $total += $customFields['Price'][0];
                    echo $customFields['Price'][0];
                }?></td>
                <td><?php echo wpfp_get_remove_link($post_id, 1,0);?></td> 
            </tr> 
        <?php }?>
        <tfoot>
        <tr>
            <th>Total:</th>
            <th colspan="2"><?php echo number_format($total, '2', ',', ' ');?></th>
        </tr>
        </tfoot>
    <?php }
}
else{
    echo "<li>";
    echo "No items in selection";
    echo "</li>";
}
echo "</ul>";
?>
