<?php 
/*
Plugin Name: Decision Trees
Plugin URI:  https://github.com/cftp/decision-trees
Description: Provides a custom post type to create decision trees in WordPress
Version:     1.4
Author:      Code for the People
Author URI:  http://codeforthepeople.com/ 
Text Domain: cftp_dt
Domain Path: /languages/
*/

/*  Copyright 2013 Code for the People Ltd

                _____________
               /      ____   \
         _____/       \   \   \
        /\    \        \___\   \
       /  \    \                \
      /   /    /          _______\
     /   /    /          \       /
    /   /    /            \     /
    \   \    \ _____    ___\   /
     \   \    /\    \  /       \
      \   \  /  \____\/    _____\
       \   \/        /    /    / \
        \           /____/    /___\
         \                        /
          \______________________/


    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

// Exit if accessed directly
defined( 'ABSPATH' ) or die();

require_once dirname( __FILE__ ) . '/class-plugin.php';
require_once dirname( __FILE__ ) . '/ClientWidget.php';
require_once dirname( __FILE__ ) . '/QuestionnaireController.php';
require_once dirname( __FILE__ ) . '/ExportController.php';
require_once dirname( __FILE__ ) . '/class-answers-simple.php';
require_once dirname( __FILE__ ) . '/libraries/phpexcel/PHPExcel.php';
if(session_id() == '')
     session_start();
/**
 * Decision Trees
 *
 * @package Decision-Trees
 * @subpackage Main
 */
class CFTP_Decision_Trees extends CFTP_DT_Plugin {
	
	/**
	 * A version for cache busting, DB updates, etc.
	 *
	 * @var string
	 **/
	public $version;
	
	public $QuestionnaireController;
	public $ExportController;
	
	public $post_type = 'decision_node';
	public $post_status;

	public $no_recursion = false;

	/**
	 * Singleton stuff.
	 * 
	 * @access @static
	 * 
	 * @return CFTP_Decision_Trees
	 */
	static public function init() {
		static $instance = false;


		if ( ! $instance ) {
			$instance = new CFTP_Decision_Trees;
		}

		return $instance;

	}
	
	/**
	 * Let's go!
	 *
	 * @access public
	 * 
	 * @return void
	 **/
	public function __construct() {

		# Actions
		add_action('export_report', 		 array( $this, 'export_report_callback') );
		add_action( 'admin_init',            array( $this, 'action_admin_init' ) );
		add_action( 'init',                  array( $this, 'action_init' ) );
		add_action( 'add_meta_boxes',        array( $this, 'action_add_meta_boxes' ), 10, 2 );
		add_action( 'save_post',             array( $this, 'action_save_post' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', 	 array( $this, 'action_enqueue_scripts' ) );
		add_action( 'widgets_init', 		 array( $this, 'action_register_widget') );
		add_action( 'admin_menu',            array( $this, 'action_admin_menu' ) );
		add_action( 'admin_notices',         array( $this, 'action_admin_notices' ) );
		# Filters
		add_filter( 'the_content',           array( $this, 'filter_the_content' ) );
		add_filter( 'the_title',             array( $this, 'filter_the_title' ), 0, 2 );
		//add_filter('show_admin_bar', 		'__return_false');

		add_action( 'wp_ajax_ajax', 		 array( $this, 'ajax_controller' ));


		$this->version = 3;
		$this->QuestionnaireController = new QuestionnaireController($this);
		$this->post_status = get_post_stati();
		unset(
			$this->post_status['trash'],
			$this->post_status['auto-draft'],
			$this->post_status['inherit']
		);

		parent::__construct( __FILE__ );
	}

	function action_admin_notices() {
		if ( ( get_current_screen()->post_type == $this->post_type ) and isset( $_GET['answer_added'] ) ) {
			?>
			<div class="updated" id="cftp_dt_answer_added">
				<p><?php _e( 'Answer added.', 'cftp_dt' ); ?></p>
			</div>
			<?php
		}
	}

	// HOOKS
	// =====
	
	/**
	 * undocumented function
	 *
	 * @action init
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	function action_admin_init() {
		if (isset($_GET['export']) && $_GET['export']==true){
	        do_action('export_report');
	    }
		$this->maybe_update();

		if ( isset( $_POST['action'] ) and ( 'cftp_dt_add_answer' == $_POST['action'] ) )
			$this->process_add_answer();

		
		if (current_user_can('editor')){
			add_filter( 'admin_body_class', array($this,'add_admin_body_class') );
		}
		wp_enqueue_style(
			'cftp-dt-admin',
			$this->plugin_url( 'css/admin.css' )
		);
	}

	function export_report_callback()
	{
		$this->ExportController = new ExportController($this);
		$this->ExportController->exportReport();
	}

	function add_admin_body_class( $classes ) {
    	return "$classes user-type-editor";
	}
	
	function process_add_answer() {

		check_admin_referer( 'cftp_dt_add_answer' );

		$post = get_post( $post_id = absint( $_POST['post_id'] ) );

		$answer_page_ids = get_post_meta( $post_id, '_cftp_dt_answers', true );

		if ( empty( $answer_page_ids ) )
			$answer_page_ids = array();

		# @TODO D.R.Y. This (and the code in action_save_meta) should be abstracted:

		foreach ( $_POST['cftp_dt_new'] as $answer_type => $answer ) {

			if ( !isset( $answer['page_title'] ) or empty( $answer['page_title'] ) ) {
				if ( isset( $answer['text'] ) and !empty( $answer['text'] ) )
					$answer['page_title'] = $answer['text'];
				else
					continue;
			}

			$answer_meta = array();

			$title = trim( $answer['page_title'] );
			$page  = get_page_by_title( $title, OBJECT, $this->post_type );

			if ( true ) {
				$this->no_recursion = true;
				$page_id = wp_insert_post( array(
					'post_title'  => $title,
					'post_type'   => $this->post_type,
					'post_status' => 'publish',
					'post_parent' => $post->ID,
				) );
				wp_update_post( array( 'ID' => $page_id, 'post_name' => sanitize_title_with_dashes( $answer['text'] ) ) );
				$page = get_post( $page_id );
				$this->no_recursion = false;
			}

			$answer_meta['_cftp_dt_answer_value'] = $answer['text'];
			$answer_meta['_cftp_dt_answer_type']  = $answer_type;
			$answer_page_ids[] = $page->ID;

			foreach ( $answer_meta as $k => $v )
				update_post_meta( $page->ID, $k, $v );

		}

		update_post_meta( $post->ID, '_cftp_dt_answers', $answer_page_ids );

		$redirect = add_query_arg( array(
			'post_type'    => $this->post_type,
			'page'         => 'cftp_dt_visualise',
			'answer_added' => 'true'
		), admin_url( 'edit.php' ) );

		wp_redirect( $redirect );
		die();

	}

	/**
	 * undocumented function
	 *
	 * @action init
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	function action_init() {

		load_plugin_textdomain( 'cftp_dt', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		$args = array(
			'labels' => array(
				'label'              => __( 'Decision Node', 'cftp_dt' ),
				'name_admin_bar'     => __( 'Decision Node', 'add new on admin bar', 'cftp_dt' ),
				'name'               => __( 'Decision Trees', 'cftp_dt' ),
				'singular_name'      => __( 'Decision Node', 'cftp_dt' ),
				'add_new'            => __( 'Add New Node', 'cftp_dt' ),
				'add_new_item'       => __( 'Add New Decision Node', 'cftp_dt' ),
				'edit_item'          => __( 'Edit Decision Node', 'cftp_dt' ),
				'new_item'           => __( 'New Decision Node', 'cftp_dt' ),
				'view_item'          => __( 'View Decision Node', 'cftp_dt' ),
				'search_items'       => __( 'Search Decision Nodes', 'cftp_dt' ),
				'not_found'          => __( 'No nodes found.', 'cftp_dt' ),
				'not_found_in_trash' => __( 'No nodes found in Trash.', 'cftp_dt' ),
				'parent_item_colon'  => __( 'Parent Decision Node:', 'cftp_dt' ),
				'all_items'          => __( 'All Decision Nodes', 'cftp_dt' ),
				'menu_name'          => __( 'Decision Trees', 'cftp_dt' ),
				'label'              => __( 'Decision Node', 'cftp_dt' ),
			),
			'public'             => true,
			'publicly_queryable' => true,
			'capability_type'    => 'page', // @TODO: Set this to `$this->post_type` and map meta caps
		//	'map_meta_cap'       => true,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-networking',
			'hierarchical'       => true,
			'rewrite'            => array(
				'with_front' => false,
				'slug'       => 'decision-tree'
			),
			'query_var'          => 'help', // @TODO: is this the best qv name?
			'delete_with_user'   => false,
			'supports'           => array( 'title', 'editor', 'page-attributes', 'thumbnail' ),
		);
		if (current_user_can('editor')){
			add_action('get_header', array( $this, 'remove_admin_login_header') );
		}
		$args = apply_filters( 'cftp_dt_cpt_args', $args );
		$cpt = register_post_type( $this->post_type, $args );
	}

	function action_admin_menu() {

		$pto = get_post_type_object( $this->post_type );

		add_submenu_page(
			'edit.php?post_type=decision_node',
			__( 'Visualise Nodes', 'cftp_dt' ),
			__( 'Visualise Nodes', 'cftp_dt' ),
			$pto->cap->edit_posts,
			'cftp_dt_visualise',
			array( $this, 'admin_page_visualise' )
		);

		add_submenu_page(
			'edit.php?post_type=decision_node',
			__( 'Client list', 'cftp_dt' ),
			__( 'Client list', 'cftp_dt' ),
			$pto->cap->edit_posts,
			'quesionnaire_list',
			array( $this, 'admin_page_quesionnaire_list' )
		);

	}

	function admin_page_visualise() {

		# @TODO D.R.Y.:
		$post_status = get_post_stati();
		unset(
			$post_status['trash'],
			$post_status['auto-draft'],
			$post_status['inherit']
		);
		if (!isset($_GET['post_id'])){
			$tree = get_pages( array(
				'post_type'   => $this->post_type,
				'post_status' => $post_status,
				'orderby'     => 'ID',
				'order'       => 'ASC',
				'parent'      => 0,
			) );
		}
		else{
			$tree[] = get_post( $_GET['post_id'] );
		}
		foreach($tree as $key => $singleTree){
			$this->populate_tree( $tree, $key );
		};
		$vars['tree'] = $tree;
		$this->render_admin( 'visualise-tree.php', $vars );
	}

	function remove_admin_login_header() {
		remove_action('wp_head', '_admin_bar_bump_cb');
	}

	function admin_page_quesionnaire_list() {
		if (!isset($_GET['user_id'])){
			$vars['user_list'] = $this->get_user_list_with_meta();
			$this->render_admin( 'client-list.php', $vars );
		}
		else if (isset($_GET['user_id']) && isset($_GET['post_id'])){
			$currentUser = $this->get_user_with_meta($_GET['user_id']);
			//updating
			if (isset($_POST['client_id'])){
				$values  = array();
				if (is_numeric($_POST['value']) && $_POST['value'] > 0){
					$values['value'] = $_POST['value'];
				} 
				if (is_numeric($_POST['price']) && $_POST['price'] > 0){
					$values['price'] = $_POST['price'];
				} 
				$vars['update_successfull'] = $this->QuestionnaireController->saveQuestionValues($_POST['client_id'], $_POST['post_id'], unserialize($currentUser->meta_data['questionnaire_tree'][0]), $values);

				$currentUser = $this->get_user_with_meta($_GET['user_id']);
			}

			$vars['current_user'] = $currentUser;
			$vars['current_question_user_meta'] = $this->QuestionnaireController->getCurrentPostUserData($_GET['post_id'], unserialize($currentUser->meta_data['questionnaire_tree'][0]));
			$this->render_admin( 'edit-questionnaire.php', $vars );
		}
		else{
			$currentUser = $this->get_user_with_meta($_GET['user_id']);
			$vars['current_user'] = $currentUser;
			$questionnaire_user_meta = unserialize($currentUser->meta_data['questionnaire_tree'][0]);
			$vars['questionnaireUserMeta'] = $this->QuestionnaireController->removeNotActiveQuestions($questionnaire_user_meta);
			$this->render_admin( 'questionnaire-list.php', $vars );
		}

	}
	function populate_tree( &$tree, $treeKey = 0 ) {

		# @TODO D.R.Y.:
		$post_status = get_post_stati();
		unset(
			$post_status['trash'],
			$post_status['auto-draft'],
			$post_status['inherit']
		);
		$children = get_posts( array(
			'posts_per_page'=> -1,
			'post_type'   	=> $this->post_type,
			'post_status' 	=> $this->post_status,
			'orderby'       => 'ID',
			'order'         => 'ASC',
			'post_parent' 	=> $tree[$treeKey]->ID # This is required when using the 'parent' arg and is a WP bug. @TODO: file it
		) );

		if ( !empty( $children ) ) {
			$tree[$treeKey]->children = $children;
			foreach($tree[$treeKey]->children as $childKey => $child){
				$tree[$treeKey]->children[$childKey]->metadata = get_post_meta($child->ID);
				$this->populate_tree( $tree[$treeKey]->children, $childKey );
			}
		}

	}

	function action_save_post( $post_id, $post ) {

		if ( $this->no_recursion )
			return;
		if ( wp_is_post_revision( $post_id ) )
			return;
		if ( wp_is_post_autosave( $post_id ) )
			return;
		if ( $this->post_type != $post->post_type )
			return;
		
		$postAnswer = '';
		if ( isset( $_POST['_cftp_dt_answer_value']['simple']['text'] ) && strlen($_POST['_cftp_dt_answer_value']['simple']['text']) > 1){
			$postAnswer = $_POST['_cftp_dt_answer_value']['simple']['text'];
		}
		else if (isset($_POST['post_title'])){
			$postAnswer = $_POST['post_title'];
		}
		update_post_meta( $post->ID, '_cftp_dt_answer_value', $postAnswer );
		update_post_meta( $post->ID, '_cftp_dt_answer_type', 'simple' );
		if ( isset( $_POST["cftp_dt_post_{$post_id}_parent"] ) ) {

			# See: http://core.trac.wordpress.org/ticket/8592
			# A page with a non-published parent will get its parent removed
			# when you save the post because it won't be listed in the post parent
			# dropdown. We'll fix that manually.

			$this->no_recursion = true;

			$parentID = (absint( $_POST["cftp_dt_post_{$post_id}_parent"] =='0' && $_POST["parent_id"] > 0 ) || ($_POST["parent_id"] > 0 && $_POST["cftp_dt_post_{$post_id}_parent"] != $_POST["parent_id"])) ? $_POST["parent_id"] : $_POST["cftp_dt_post_{$post_id}_parent"];

			wp_update_post( array(
				'ID'          => $post->ID,
				'post_parent' => $parentID ,
			) );
			$this->no_recursion = false;


			//update parent meta data to make a tree
			if (absint( $_POST["cftp_dt_post_{$post_id}_parent"] =='0' && $_POST["parent_id"] > 0 ) || ($_POST["parent_id"] > 0 && $_POST["cftp_dt_post_{$post_id}_parent"] != $_POST["parent_id"])){
				$parentAnswers = get_post_meta($_POST["parent_id"], '_cftp_dt_answers', true);
				if (!in_array($post_id, $parentAnswers)){
					$parentAnswers[] = $post_id;
					update_post_meta($_POST["parent_id"], '_cftp_dt_answers', $parentAnswers);
				}
			}

		}

		if ( !isset( $_POST['cftp_dt_add'] ) and !isset( $_POST['cftp_dt_edit'] ) )
			return;

		$answer_page_ids = array();

		if ( isset( $_POST['cftp_dt_edit'] ) ) {

			foreach ( array_values( $_POST['cftp_dt_edit'] ) as $id => $answers ) {
				foreach ( $answers as $answer_type => $answer ) {

					$answer_meta = array();

					$page = get_post( $answer['page_id'] );

					$answer_meta['_cftp_dt_answer_value'] = $answer['text'];
					$answer_page_ids[] = $page->ID;

					foreach ( $answer_meta as $k => $v )
						update_post_meta( $page->ID, $k, $v );
				}
			}

		}

		if ( isset( $_POST['cftp_dt_add'] ) ) {

			foreach ( array_values( $_POST['cftp_dt_add'] ) as $id => $answers ) {
				foreach ( $answers as $answer_type => $answer ) {

					if ( !isset( $answer['page_title'] ) or empty( $answer['page_title'] ) ) {
						if ( isset( $answer['text'] ) and !empty( $answer['text'] ) )
							$answer['page_title'] = $answer['text'];
						else
							continue;
					}

					$answer_meta = array();

					$title = trim( $answer['page_title'] );
					$page  = get_page_by_title( $title, OBJECT, $this->post_type );

					if ( !$page ) {
						$this->no_recursion = true;
						$page_id = wp_insert_post( array(
							'post_title'  => $title,
							'post_type'   => $this->post_type,
							'post_status' => 'publish',
							'post_parent' => $post->ID,
						) );
						wp_update_post( array( 'ID' => $page_id, 'post_name' => sanitize_title_with_dashes( $answer['text'] ) ) );
						$page = get_post( $page_id );
						$this->no_recursion = false;
					}

					$answer_meta['_cftp_dt_answer_value'] = $answer['text'];
					$answer_meta['_cftp_dt_answer_type']  = $answer_type;
					$answer_page_ids[] = $page->ID;

					foreach ( $answer_meta as $k => $v )
						update_post_meta( $page->ID, $k, $v );
				}
			}

		}

		update_post_meta( $post->ID, '_cftp_dt_answers', $answer_page_ids );

	}

	function action_admin_enqueue_scripts() {

		if ( $this->post_type != get_current_screen()->post_type )
			return;

		wp_enqueue_style(
			'cftp-dt-admin',
			$this->plugin_url( 'css/admin.css' ),
			array( 'wp-admin', 'thickbox' ),
			$this->plugin_ver( 'css/admin.css' )
		);

		wp_register_script(
			'jquery.jsPlumb',
			$this->plugin_url( 'js/jquery.jsPlumb-1.3.16-all-min.js' ),
			array( 'jquery'/*, 'jquery-ui'*/ ), /* jQuery UI is only needed if we add drag-and-drop */
			'1.3.16'
		);
		wp_enqueue_script(
			'cftp-dt-admin',
			$this->plugin_url( 'js/admin.js' ),
			array( 'jquery', 'jquery.jsPlumb', 'thickbox' ),
			$this->plugin_ver( 'js/admin.js' )
		);
		wp_enqueue_script(
			'cftp-dt-google-chart',
			'https://www.gstatic.com/charts/loader.js',
			array( 'jquery'),
			$this->plugin_ver( 'js/admin.js' )
		);

	}

	function action_enqueue_scripts() {

		wp_enqueue_style(
			'cftp-dt-fronend',
			$this->plugin_url( 'css/frontend.css' ),
			array(),
			$this->plugin_ver( 'css/admin.css' )
		);


		wp_enqueue_script(
			'cftp-dt-frontend-js',
			$this->plugin_url( 'js/script.js' ),
			array( 'jquery'),
			$this->plugin_ver( 'js/script.js' )
		);
		wp_enqueue_script( 'jquery-ui-dialog', array('jquery') );
		wp_enqueue_script( 'bootbox', $this->plugin_url( 'js/bootbox.js' ), array( 'jquery'));

		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
		wp_localize_script( 'cftp-dt-frontend-js', 'my_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'action' => 'ajax' ) );

	}

	function ajax_controller()
	{
		if (isset($_POST['operation']) && $_POST['operation']=='save-selectable'){
			$this->QuestionnaireController->saveSelectableQuestions();
		}
		if (isset($_POST['operation']) && $_POST['operation']=='get-next-sequence-url'){
			$this->QuestionnaireController->getNextSequenceUrl();
		}
		if (isset($_POST['operation']) && $_POST['operation']=='save-value'){
			$this->QuestionnaireController->saveQuestionValue();
		}
		if (isset($_POST['operation']) && $_POST['operation']=='skip-value'){
			$this->QuestionnaireController->skipQuestionValue();
		}
		if (isset($_POST['operation']) && $_POST['operation']=='ignore-value'){
			$this->QuestionnaireController->ignoreQuestionValue();
		}
		wp_die();
	}
	function action_register_widget() {

		register_widget( 'DT_ClientWidget' );

	}

	function filter_the_content( $content ) {

		global $post;
		$vars = array();

		if (is_user_logged_in()){
			$currenUser = wp_get_current_user();
			if ($currenUser->roles[0]=='administrator' || $currenUser->roles[0]=='editor'){
				if (isset($_GET['select_user']) && $_GET['select_user'] == 'true' && is_numeric($_GET['user_id'])){
					$_SESSION['client_id'] = $_GET['user_id'];
					$this->QuestionnaireController->initializeQuestinonnaireTree($_SESSION['client_id']);
					$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
					$url = 'http://' . $_SERVER['HTTP_HOST'] . $uri_parts[0];
				}
				if (isset($_GET['questionnaire_view_mode']) && $_GET['questionnaire_view_mode']!='') {
					update_user_meta ($_SESSION['client_id'], 'questionnaire_view_mode', $_GET['questionnaire_view_mode'] );
					$_SESSION['questionnaire_view_mode'] = $_GET['questionnaire_view_mode'];
				}
				if (isset($_GET['sign_out_client']) && $_GET['sign_out_client']=='true'){
					unset($_SESSION['client_id']);
					unset($_SESSION['client_idquestionnaire_tree']);
					unset($_SESSION['questionnaire_tree']);
					unset($_SESSION['questionnaire_view_mode']);
				}
				if (isset($_SESSION['client_id']) && isset($_SESSION['client_id'])){
					$this->QuestionnaireController->changeQuestionnaireValue($post->ID,array('visited'=>true));
					$vars[ 'question_user_meta' ] = $this->QuestionnaireController->getCurrentPostUserData( $post->ID );
				}
				//echo 'xxxx:';print_r($_SESSION);
			}
			if ($currenUser->roles[0]!='administrator'){
				show_admin_bar( false );
			}
		}
		if ( $this->post_type != $post->post_type )
			return $content;

		$answers = cftp_dt_get_post_answers( $post->ID );

		$start = false;
		$previous_answers = array();
		if ( $post->post_parent ) {
			$previous_answers = array_reverse( get_post_ancestors( $post->ID ) );
			$previous_answers[] = get_the_ID();
			$start = array_shift( $previous_answers );
			array_shift( $previous_answers );
			$start = get_post( $start );
		}

		remove_filter( 'the_title', array( $this, 'filter_the_title' ), 0, 2 );

		$vars[ 'start' ] = $start;
		$vars[ 'previous_answers' ] = $previous_answers;
		$vars[ 'title' ] = get_the_title( $post->ID );
		$vars[ 'content' ] = $content;
		$vars[ 'answers' ] = $answers;
		$vars[ 'answer_links' ] = $this->capture( 'content-answer-links.php', $vars );
		add_filter( 'the_title', array( $this, 'filter_the_title' ), 0, 2 );

		$vars['user_list'] = $this->get_user_list_with_meta();
		if (!is_user_logged_in()){
			return $this->capture( 'content-login.php', $vars );
		}
		if (!isset($_SESSION['client_id']) || !$_SESSION['client_id'])
			return $this->capture( 'content-select-client.php', $vars );
		else if ( $post->post_parent )
			return $this->capture( 'content-with-history.php', $vars );
		else
			return $this->capture( 'content-no-history.php', $vars );
	}

	function filter_the_title( $title, $post ) {
		if ( is_admin() )
			return $title;

		$post = get_post( $post );
		if ( $post->post_type == 'decision_node'  ){
			return $title;
		}
		else{
			return $title;
		}
	}

	/**
	 * Hooks the WP action add_meta_boxes
	 *
	 * @action add_meta_boxes
	 *
	 * @param $post_type The name of the post type
	 * @param $post The post object
	 * @return void
	 * @author Simon Wheatley
	 **/
	function action_add_meta_boxes( $post_type, $post ) {
		if ( $this->post_type != $post_type )
			return;
		add_meta_box( 'cftp_dt_answers', __( 'Answers', 'cftp_dt' ), array( $this, 'callback_answers_meta_box' ), $this->post_type, 'advanced', 'default' );
	}

	// CALLBACKS
	// =========

	/**
	 * Callback to provide the HTML for the answers metabox.
	 *
	 * @param $post A post object
	 * @param $box The parameters for this meta box
	 * @return void
	 * @author Simon Wheatley
	 **/
	function callback_answers_meta_box( $post, $box ) {
		$this->init_answer_providers();

		$vars = array();
		$vars[ 'answers' ] = cftp_dt_get_post_answers( $post->ID );
		$vars[ 'meta_data' ] = get_post_meta($post->ID );
		$this->render_admin( 'meta-box-current-answer.php', $vars );
		$this->render_admin( 'meta-box-answers.php', $vars );
	}

	// METHODS
	// =======
	
	/**
	 * Checks the DB structure is up to date, rewrite rules, 
	 * theme image size options are set, etc.
	 *
	 * @return void
	 **/
	public function maybe_update() {
		global $wpdb;
		$option_name = 'cftp_dt_version';
		$version = absint( get_option( $option_name, 0 ) );
		
		// Debugging and dev:
		// delete_option( "{$option_name}_running", true, null, 'no' );

		if ( $version == $this->version )
			return;

		// Institute a lock, for long running operations
		if ( $start_time = get_option( "{$option_name}_running", false ) ) {
			$time_diff = time() - $start_time;
			// Check the lock is less than 30 mins old, and if it is, bail
			if ( $time_diff < ( 60 * 30 ) ) {
				error_log( "CFTP DT: Existing update routine has been running for less than 30 minutes" );
				return;
			}
			error_log( "CFTP DT: Update routine is running, but older than 30 minutes; going ahead regardless" );
		} else {
			add_option( "{$option_name}_running", time(), null, 'no' );
		}

		// Flush the rewrite rules
		if ( $version < 2 ) {
			flush_rewrite_rules();
			error_log( "CFTP DT: Flush rewrite rules" );
		}

		// Change existing posts to our new post type name
		if ( $version < 3 ) {
			$q = $wpdb->prepare( "
				UPDATE {$wpdb->posts}
				SET post_type = %s
				WHERE post_type = 'decision_tree'
			", $this->post_type );
			$wpdb->query( $q );
			error_log( "CFTP DT: Updated old post type names" );
		}

		// N.B. Remember to increment $this->version in self::__construct above when you add a new IF

		delete_option( "{$option_name}_running", true, null, 'no' );
		update_option( $option_name, $this->version );
		error_log( "CFTP DT: Done upgrade, now at version " . $this->version );
	}

	function init_answer_providers() {

		if ( !isset( $this->answer_providers ) )
			$this->answer_providers = apply_filters( 'cftp_dt_answer_providers', array() );

	}

	function get_answer_provider( $answer_type ) {

		$this->init_answer_providers();
		
		if ( isset( $this->answer_providers[$answer_type] ) )
			return $this->answer_providers[$answer_type];
		else
			return false;
	}

	function get_answer_providers() {

		$this->init_answer_providers();

		return $this->answer_providers;

	}

	function get_user_list_with_meta()
	{
		$userList = get_users(array(
			'fields'=>array('ID','display_name','user_email'),
			'role'=>'Subscriber'
			));
		foreach ($userList as $key=>$singleUser){
			$userList[$key]->meta_data = get_user_meta($singleUser->ID);
		}
		return $userList;
	}

	function get_user_with_meta($ID)
	{
		$currentUser = get_user_by('ID',$ID);
		$currentUser->meta_data = get_user_meta($currentUser->ID);
		return $currentUser;
	}

}

// Initiate the singleton
CFTP_Decision_Trees::init();

function cftp_dt_get_post_answers( $post_id = null ) {

	if ( ! $post = get_post( $post_id ) )
		return array();

	$post_status = get_post_stati();
	unset(
		$post_status['trash'],
		$post_status['auto-draft'],
		$post_status['inherit']
	);
	$children = get_posts( array(
			'posts_per_page'=> -1,
			'post_type'   	=> 'decision_node',
			'post_status' 	=> $post_status,
			'orderby'       => 'ID',
			'order'         => 'ASC',
			'post_parent' 	=> $post->ID # This is required when using the 'parent' arg and is a WP bug. @TODO: file it
		) );
	$answers = array();
	foreach ($children as $singlePost){
		$answers[] = $singlePost->ID;
	}
	if ( empty( $answers ) )
		$answers = array();

	foreach ( $answers as &$answer )
		$answer = new CFTP_DT_Answer( $answer );

	return $answers;

}

function cftp_dt_get_previous_answers( $post_id = null ) {

	if ( ! $post = get_post( $post_id ) )
		return array();

	if ( ! $post->post_parent )
		return array();

	// $ancestors = 
}


class CFTP_DT_Answer {
	private $QuestionnaireController;
	function __construct( $post_id ) {
		$this->post = get_post( $post_id );
		$this->QuestionnaireController = new QuestionnaireController(null, $this);
	}

	function get_post() {
		return $this->post;
	}

	function get_page_title() {
		return get_the_title( $this->post->ID );
	}
	function get_page_id() {
		return  $this->post->ID ;
	}

	function get_answer_value() {
		return get_post_meta( $this->post->ID, '_cftp_dt_answer_value', true );
	}

	function get_answer_type() {
		return get_post_meta( $this->post->ID, '_cftp_dt_answer_type', true );
	}

	function get_question_type() {
		return get_post_meta( $this->post->ID, 'question_type', true );
	}

	function get_all_meta() {
		return get_post_meta( $this->post->ID );
	}
	function get_user_meta() {
		return $this->QuestionnaireController->getCurrentPostUserData($this->post->ID);

	}
	function get_meta_by_key($key) {
		return get_post_meta( $this->post->ID, $key, true );
	}

}
