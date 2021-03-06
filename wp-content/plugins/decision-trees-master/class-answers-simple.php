<?php 

// Exit if accessed directly
defined( 'ABSPATH' ) or die();

/**
 * Decision Trees simple answers class
 *
 * @package Decision-Trees
 * @subpackage Main
 */
class CFTP_DT_Answers_Simple {

	/**
	 * Singleton stuff.
	 * 
	 * @access @static
	 * 
	 * @return void
	 */
	static public function init() {
		static $instance = false;

		if ( ! $instance ) {
			$class = get_called_class();
			$instance = new $class();
		}

		return $instance;
	}
	
	/**
	 * Hook into the CFTP_DT answer_providers filter
	 * 
	 * @filter cftp_dt_answer_providers
	 * 
	 * @access @static
	 * 
	 * @return void
	 */
	static public function filter_answer_providers( $answers ) {
		$answers['simple'] = self::init();
		return $answers;
	}
	
	/**
	 * @TODO
	 *
	 * @access public
	 * 
	 * @return void
	 **/
	public function __construct() {
		// Nothing
	}
	
	/**
	 * @TODO
	 *
	 * @access public
	 * 
	 * @return string
	 **/
	public function get_edit_form( $id, CFTP_DT_Answer $answer ) {

		return sprintf( '<input type="text" class="regular-text" name="cftp_dt_edit[%s][simple][text]" placeholder="%s" value="%s" />',
			$id,
			__( 'Answer link text', 'cftp_dt' ),
			esc_attr( $answer->get_answer_value() )
		);

	}
	
	/**
	 * @TODO
	 *
	 * @access public
	 * 
	 * @return string
	 **/
	public function get_add_form() {
		return sprintf( '<input type="text" class="regular-text" name="cftp_dt_new[simple][text]" placeholder="%s" />',
			__( 'Answer link text', 'cftp_dt' )
		);
	}
	/**
	 * @TODO
	 *
	 * @access public
	 * 
	 * @return string
	 **/
	public function get_answer( CFTP_DT_Answer $answer ) {
		$questionMeta = $answer->get_all_meta();
		$questionUserMeta = $answer->get_user_meta();
		if (isset($questionMeta['selectable'][0]) && $questionMeta['selectable'][0]==1){
			if (isset($questionUserMeta['selected']) && $questionUserMeta['selected']=='1'){
		
				if (has_post_thumbnail($answer->get_page_id())){
					//echo ;
				}
				echo sprintf( '<a class="cftp_dt_answer_link btn btn-warning btn-large" href="%1$s">
						%2$s
						<div><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> 
						%3$s</div></a>',
					get_permalink( $answer->get_post()->ID ),
					get_the_post_thumbnail( $answer->get_page_id(), 'thumbnail' ),
					$answer->get_answer_value()
				);
			}
			else{?>
				<span class="cftp_dt_answer_link btn btn-warning btn-large">
					<?php echo get_the_post_thumbnail( $answer->get_page_id(), 'thumbnail' );?>
	                <div><input id="decision-tree-<?php echo $answer->post->ID;?>" type="checkbox" <?php echo (isset($questionUserMeta['selected']) && $questionUserMeta['selected']==1)?'checked':'';?>>
	                <label for="decision-tree-<?php echo $answer->post->ID;?>">
	                    <?php echo $answer->get_answer_value()?></a>
	                </label>
	                </div>
	            </span>
			<?php }
		}
		else{
			if (isset($questionMeta['sequence'][0]) && $questionMeta['sequence'][0]==1){
				if (isset($questionUserMeta['ignore']) && $questionUserMeta['ignore']==1){
					return sprintf( '<span class="cftp_dt_answer_link btn btn-warning btn-large">
						<span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span> 
						%2$s</a>',
						$answer->get_answer_value()
					);
				}
				else{
					return sprintf( '<a class="cftp_dt_answer_link btn btn-warning btn-large" href="%1$s">
						<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> 
						%2$s</a>',
					get_permalink( $answer->get_post()->ID ),
					$answer->get_answer_value()
					);
				}
			}
			else{
				if (isset($questionUserMeta['value']) && $questionUserMeta['value']>0){
					return sprintf( '<a class="cftp_dt_answer_link btn btn-warning btn-large" href="%1$s">
						%2$s <div> <span class="glyphicon glyphicon glyphicon-plus" aria-hidden="true"></span> %3$s : %4$s</div></a>',
						get_permalink( $answer->get_post()->ID ),
						get_the_post_thumbnail( $answer->get_page_id(), 'thumbnail' ),
						$answer->get_answer_value(),
						$questionUserMeta['value']
					);
				}
				if (isset($questionUserMeta['ignore']) && $questionUserMeta['ignore']==1){
					return sprintf( '<span class="cftp_dt_answer_link btn btn-warning btn-large">
						%1$s <div>
						<span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span> %2$s</div></a>',
						get_the_post_thumbnail( $answer->get_page_id(), 'thumbnail' ),
						$answer->get_answer_value()
					);
				}
				else if (isset($questionUserMeta['skip']) && $questionUserMeta['skip']==1){
					return sprintf( '<a class="cftp_dt_answer_link btn btn-warning btn-large" href="%1$s">
						%2$s <div>
						<span class="glyphicon glyphicon glyphicon-step-forward" aria-hidden="true"></span>  %3$s</div></a>',
						get_permalink( $answer->get_post()->ID ),
						get_the_post_thumbnail( $answer->get_page_id(), 'thumbnail' ),
						$answer->get_answer_value()
					);
				}
				else
					echo sprintf( '<a class="cftp_dt_answer_link btn btn-warning btn-large" href="%1$s">%2$s <div>%3$s</div></a>',
					get_permalink( $answer->get_post()->ID ),
					get_the_post_thumbnail( $answer->get_page_id(), 'thumbnail' ),
					$answer->get_answer_value()
					);
			}
		}
	}
	
	/**
	 * @TODO
	 *
	 * @access public
	 * 
	 * @return string
	 **/
	public function get_edit_answer_url( CFTP_DT_Answer $answer ) {
		return get_permalink( $answer->get_post()->post_parent );
	}

}

add_filter( 'cftp_dt_answer_providers', 'CFTP_DT_Answers_Simple::filter_answer_providers', 0 );
