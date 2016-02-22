<?php
if ($favorite_post_ids) {
	$c = 0;
	$favorite_post_ids = array_reverse($favorite_post_ids);
    $total = 0;
    if (count($favorite_post_ids )>0){ ?>
    <div class="favorite-post-list-container">
        <table class="favorite-post-list table table-condensed table-striped">
        <thead>
            <tr>
                <th>Selection</th>
                <th>Value</th>
                <th>Action</th> 
            </tr> 
        </thead>
        <tbody>
        <?php foreach ($favorite_post_ids as $posts) {
            $customFields = get_post_custom($posts['ID']);
            if ($c++ == $limit) break;
            $p = get_post($posts['ID']);?>
            <tr>
                <td><a href="<?php echo get_permalink($posts['ID']);?> " title="<?php echo $p->post_title ?>"><?php echo substr($p->post_title, 0, 10).'...';?></a></td>
                <td><?php if (isset($customFields['value'])){
                    $total += $posts['value'];
                    echo $posts['value'];
                }?></td>
                <td><?php echo wpfp_get_remove_link($posts['ID'], 1,0);?></td> 
            </tr> 
        <?php }?>
        </tbody>
        </table>
        </div>
    <?php }
}
else{
    echo "No items in selection";
}
?>
