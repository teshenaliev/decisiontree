<?php defined( 'ABSPATH' ) or die();
$customFields = get_post_custom();
 ?>
<ol id="cftp-dt-answers">
	<li class="cftp-dt-current">
		<h3 class="cftp-dt-current"><?php echo $title; ?></h3>
	</li>
</ol>
<div class="cftp-dt-content">
	<div class="cftp-dt-content">
			<?php echo $content; ?>
	</div>
	<?php if (isset($customFields['Price']) && $customFields['Price']>0 && !is_array($customFields['Price'])):?>
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

	<?php if ( $answer_links ) : echo $answer_links; endif; ?>

</div>
