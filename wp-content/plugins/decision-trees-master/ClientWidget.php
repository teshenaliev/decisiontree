<?php
class DT_ClientWidget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'dt_client_widget',
			'description' => 'Decision Tree Client Widget',
		);
		parent::__construct( 'dt_client_widget', 'Decision Tree Client Widget', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if (isset($_SESSION['client_id'])){
			$currentClient = (isset($_SESSION['client_id']) && $_SESSION['client_id']>0) ? get_user_by('ID', $_SESSION['client_id']) : null;
			$currentClient->meta_data = get_user_meta($currentClient->ID);
			$currenUser = wp_get_current_user();
			$start = null;
			global $post;
			$previous_answers = array();
			if ( $post->post_parent ) {
				$previous_answers = array_reverse( get_post_ancestors( $post->ID ) );
				$previous_answers[] = get_the_ID();
				$start = array_shift( $previous_answers );
				array_shift( $previous_answers );
				$start = get_post( $start );
			}

			?>
			<table class="table table-striped">
			<tr>
				<th><?php _e('Client name','cftp_dt');?></th>
				<td><?php echo $currentClient->display_name;?></td>
			</tr>
			<tr>
				<th><?php _e('Address','cftp_dt');?></th>
				<td><?php echo $currentClient->meta_data['wp_client_address'][0];?></td>
			</tr>
			<?php if ($currenUser->roles[0]=='administrator' || $currenUser->roles[0]=='editor'):?>
			<tr>
				<th><?php _e('Action','cftp_dt');?></th>
				<td><a href="javascript:void(0)" redirect-url="<?php echo ($start!=null) ? get_permalink( $start->ID ):''; ?>" class="btn btn-primary sign-out-client-button"><?php _e('Sign out','cftp_dt');?></a></td>
			</tr>
			<?php endif ;?>
			<?php 
		}
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		?>
		Widget for showing currently selected client
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}