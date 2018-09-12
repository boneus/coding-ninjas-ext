<?php 

namespace codingninjasext;

// use \Exception;

class App {

	/**
	 * Instance of App
	 * @var null
	 */
	public static $instance = null;

	/**
	 * Plugin main file
	 * @var
	 */
	public static $main_file;

	/**
	 * Path to app folder
	 * @var string
	 */
	public static $app_path;

	/**
	 * Url to app folder
	 * @var string
	 */
	public static $app_url;

	/**
	 * App constructor.
	 * @param $main_file
	 */
	public function __construct( $main_file ) {

		self::$main_file = $main_file;
		self::$app_path = dirname( $main_file ).'/app';
		self::$app_url = plugin_dir_url( $main_file ).'app';
		
		spl_autoload_register( array( &$this, 'autoloader' ) );

		$this->initActions();
		$this->initAdminActions();
		$this->initFilters();
		$this->initShortcodes();
	}

	/** Run App
	 * @param $main_file
	 * @return App|null
	 */
	public static function run( $main_file )
	{
		if ( !self::$instance ) {
			self::$instance = new self( $main_file );
		}

		return self::$instance;
	}

	/**
	 * Init wp actions
	 */
	private function initActions()
	{

		add_action( 'init', array( &$this, 'onInitPostTypes' ) );

		// Freelancer metabox with name and avatar fields
		add_action( 'add_meta_boxes_freelancer', array( &$this, 'addFreelancerMetaBox' ) );
		add_action( 'save_post', array( &$this, 'saveFreelancerMetaboxFields' ) );
		add_action( 'new_to_publish', array( &$this, 'saveFreelancerMetaboxFields' ) );

		// Task freelancer metabox
		add_action( 'add_meta_boxes_task', array( &$this, 'addTaskFreelancerMetaBox' ) );
		add_action( 'save_post', array( &$this, 'saveTaskFreelancerMetaboxFields' ) );
		add_action( 'new_to_publish', array( &$this, 'saveTaskFreelancerMetaboxFields' ) );

		if ( \codingninjas\App::$route == 'tasks' ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'onInitScripts' ), 21 );
			add_action( 'wp_enqueue_scripts', array( $this, 'onInitStyles' ), 21 );

			add_action( 'wp_footer', array( $this, 'outputModalHtml' ) );
		}

		// AJAX
		add_action( 'wp_ajax_nopriv_add_new_task', array( $this, 'ajaxAddNewTask' ) );
		add_action( 'wp_ajax_add_new_task', array( $this, 'ajaxAddNewTask' ) );
	}

	/**
	 * Init wp admin actions
	 */
	private function initAdminActions()
	{
		add_action( 'admin_enqueue_scripts', array( $this, 'onInitAdminScripts' ) );
	}

	/**
	 * Init wp filters
	 */
	private function initFilters()
	{

		add_filter( 'pre_get_document_title', array( &$this, 'filterPageTitles' ) );

		if ( \codingninjas\App::$route == 'tasks' ) {

			add_filter( 'cn_tasks_thead_cols', array( &$this, 'filterTasksTheadCols' ) );
			add_filter( 'cn_tasks_tbody_row_cols', array( &$this, 'filterTasksTbodyRowCols' ), 10, 2 );

			add_filter( 'cn_menu', array( &$this, 'filterMenu' ), 10, 2 );
		}
	}

	private function initShortcodes() {

		add_shortcode( 'cn_dashboard', array( &$this, 'onInitDashboardShortcode' ) );
	}

	/**
	 * Init post type freelancer
	 */
	public function onInitPostTypes()
	{
		$labels = array(
			'name'               => __( 'Freelancers', 'cne' ),
			'singular_name'      => __( 'Freelancer',  'cne' ),
			'menu_name'          => __( 'Freelancers', 'cne' ),
			'name_admin_bar'     => __( 'Freelancer',  'cne' ),
			'add_new'            => __( 'Add New', 'cne' ),
			'add_new_item'       => __( 'Add New Freelancer', 'cne' ),
			'new_item'           => __( 'New Freelancer', 'cne' ),
			'edit_item'          => __( 'Edit Freelancer', 'cne' ),
			'view_item'          => __( 'View Freelancer', 'cne' ),
			'all_items'          => __( 'All Freelancers', 'cne' ),
			'search_items'       => __( 'Search Freelancers', 'cne' ),
			'parent_item_colon'  => __( 'Parent Freelancers:', 'cne' ),
			'not_found'          => __( 'No freelancers found.', 'cne' ),
			'not_found_in_trash' => __( 'No freelancers found in Trash.', 'cne' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'freelancer' ),
			'menu_icon'            => 'dashicons-index-card',
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( Freelancer::POST_TYPE, $args );
	}


	/**
	 * Add metabox to freelancer
	 */
	public function addFreelancerMetaBox() {

		add_meta_box(
			'freelancer_meta_box',
			'Information',
			array( &$this, 'showFreelancerMetaBox' ),
			'freelancer',
			'normal',
			'default'
		);
	}

	/**
	 * Freelancer metabox output
	 */
	public function showFreelancerMetaBox() {

		global $post;

		wp_nonce_field( basename( __FILE__ ), 'cne_freelance_nonce' );

		$name = get_post_meta( $post->ID, '_freelancer_name', true );
		$avatar = get_post_meta( $post->ID, '_freelancer_avatar', true );

		$image_attributes = wp_get_attachment_image_src( $avatar, 'thumbnail' );

		$display = $image_attributes ? 'inline-block' : 'none';
		?>
			<p class="form-group">
				<label for="freelancer-name">Name:</label>
				<input type="text" class="form-control" placeholder="Name" name="freelancer-name" id="freelancer-name" value="<?php echo esc_attr( $name ) ?>">
			</p>

			<p>
				<label for="">Avatar:</label>
				<?php if ( $image_attributes ) : ?>
					<a href="#" class="upload_avatar_button"><img src="<?php echo $image_attributes[0] ?>" style="max-width:95%;display:block;" /></a>
				<?php else : ?>
					<a href="#" class="upload_avatar_button button">Upload avatar</a>
				<?php endif; ?>
				<input type="hidden" name="freelancer-avatar" id="freelancer-avatar" value="<?php echo esc_attr( $avatar ) ?>" />
				<a href="#" class="remove_avatar_button" style="display:inline-block;display:<?php echo $display ?>">Remove avatar</a>
			</p>
		<?php
	}

	/**
	 * Save freelancer metabox fields
	 */
	public function saveFreelancerMetaboxFields( $post_id ) {

		if ( !isset( $_POST['cne_freelance_nonce'] ) || !wp_verify_nonce( $_POST['cne_freelance_nonce'], basename( __FILE__ ) ) ){
			return;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( 'freelance' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ){
				return;
			}
		}


		if ( isset( $_REQUEST['freelancer-name'] ) ) {
			update_post_meta( $post_id, '_freelancer_name', sanitize_text_field( $_POST['freelancer-name'] ) );
		}

		if ( isset( $_REQUEST['freelancer-avatar'] ) ) {
			update_post_meta( $post_id, '_freelancer_avatar', sanitize_text_field( $_POST['freelancer-avatar'] ) );
		}
	}


	/**
	 * Add freelancer metabox to task
	 */
	public function addTaskFreelancerMetaBox() {

		add_meta_box(
			'task_meta_box',
			'Freelancer',
			array( &$this, 'showTaskFreelancerMetaBox' ),
			'task',
			'side',
			'default'
		);
	}

	/**
	 * Task freelancer metabox output
	 */
	public function showTaskFreelancerMetaBox() {

		global $post;

		wp_nonce_field( basename( __FILE__ ), 'cne_task_freelance_nonce' );

		$freelancers = new \codingninjasext\ModelFreelancers();
		$freelancers = $freelancers->getAll();

		$task_freelancer = get_post_meta( $post->ID, '_task_freelancer', true );
		?>
			<select name="freelancer" id="freelancer">

				<option value="0">Select freelancer</option>

				<?php foreach ( $freelancers as $freelancer ) : ?>

					<option value="<?php echo esc_attr( $freelancer->id() ); ?>" <?php if ( $task_freelancer == $freelancer->id() ) echo 'selected' ?>>
						<?php echo esc_html( $freelancer->name() ); ?>
					</option>

				<?php endforeach; ?>
			</select>
		<?php
	}

	/**
	 * Save task freelancer metabox fields
	 */
	public function saveTaskFreelancerMetaboxFields( $post_id ) {

		if ( !isset( $_POST['cne_task_freelance_nonce'] ) || !wp_verify_nonce( $_POST['cne_task_freelance_nonce'], basename( __FILE__ ) ) ){
			return;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( 'task' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ){
				return;
			}
		}


		if ( isset( $_REQUEST['freelancer'] ) ) {
			update_post_meta( $post_id, '_task_freelancer', sanitize_text_field( $_POST['freelancer'] ) );
		}
	}


	/* Мне не нравится реализация, но, к сожалению, мне не удалось сделать по-другому :( */
	public function filterPageTitles( $title ) {

		if ( \codingninjas\App::$route == 'tasks' ) {
			$title = 'Tasks';
		}
		elseif ( \codingninjas\App::$route == 'dashboard' ) {
			$title = 'Dashboard';
		}

		return $title;
	}

	/**
	 * Add freelancer column to task table
	 */
	public function filterTasksTheadCols( $cols ) {

		array_splice( $cols, 2, 0, __( 'Freelancer', 'cne' ) );

		return $cols;
	}

	public function filterTasksTbodyRowCols( $cols, $task ) {

		$task_id = substr( $task->id(), 1);

		$freelancer_id = get_post_meta( $task_id, '_task_freelancer', true );

		if ( $freelancer_id )
			$name = ( new ModelFreelancers() )->get( $freelancer_id )->name();
		else
			$name = 'Not selected';

		array_splice( $cols, 2, 0, $name );

		return $cols;
	}


	/**
	 * Add new item to menu
	 */
	public function filterMenu( $menu, $route ) {

		if ( $route == 'tasks' )
			$menu['/new-task'] = [
				'title' => __('Add New Task', 'cne'),
				'icon' => 'fa-plus-circle'
			];

		return $menu;
	}

	public function outputModalHtml() {
		?>
		<div class="modal fade" id="newTaskModal" tabindex="-1" role="dialog" aria-labelledby="newTaskModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Add new task</h4>
					</div>

					<div class="modal-body">

						<form class="form-horizontal" method="post">
							<div class="form-group">
								<label for="task-title" class="col-sm-4 control-label">Task title:</label>
								<div class="col-sm-6">
									<input type="text" name="task-title" id="task-title" placeholder="Title" class="form-control">
								</div>
							</div>

							<div class="form-group">
								<label for="task-freelancer" class="col-sm-4 control-label">Freelancer:</label>
								<div class="col-sm-6">
									<select name="task-freelancer" id="task-freelancer" class="form-control">
										<option value="0">Select freelancer</option>
										<?php echo $this->freelancersOptionsHtml(); ?>
									</select>
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-6">
									<button type="submit" class="btn btn-primary" id="add-new-task">Add</button>
								</div>

							</div>
						</form>

					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Print HTML options of freelancers select
	 */
	private function freelancersOptionsHtml() {

		// $freelancers = ( new ModelFreelancers() )->getAll();
		$freelancers = ( new ModelFreelancers() )->getWithLessThanTwoTasks();

		foreach ( $freelancers as $freelancer ) :
			echo '<option value="' . esc_attr( $freelancer->id() ) . '">' . esc_html( $freelancer->name() ) . '</option>';
		endforeach;
	}

	public function ajaxAddNewTask() {

		check_ajax_referer( 'cne_new_task_nonce' );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			if ( $_POST['title'] ) {

				$postid = wp_insert_post( [
					'post_title' => $_POST['title'],
					'post_type' => 'task',
					'post_status' => 'publish'
				] );

				update_post_meta( $postid, '_task_freelancer', $_POST['freelancer'] );

				echo $postid;
			}
		}

		die();
	}


	public function onInitDashboardShortcode( $atts ) {

		extract( shortcode_atts( [], $atts ) );

		ob_start ();
		?>
		<div class="col-lg-3 col-md-6">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="fa fa-users fa-5x"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge"><?php echo wp_count_posts( 'freelancer' )->publish; ?></div>
							<div>Freelancers</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6">
			<div class="panel panel-green">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="fa fa-tasks fa-5x"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge"><?php echo wp_count_posts( 'task' )->publish; ?></div>
							<div>Tasks</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		$content = ob_get_clean ();

		return $content;
	}


	public function onInitStyles() {

		wp_enqueue_style(
			'data-tables-style',
			'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css'
		);
	}

	public function onInitScripts() {

		wp_enqueue_script(
			'data-tables',
			'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js',
			['jquery'],
			'1.10.19',
			true
		);

		wp_enqueue_script(
			'main-scripts',
			self::$app_url.'/assets/js/main.js',
			['data-tables'],
			'1.0',
			true
		);

		wp_localize_script( 'main-scripts', 'ajaxdata', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'cne_new_task_nonce' ),
		));
	}

	public function onInitAdminScripts() {

		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_script( 
			'admin-scripts', 
			self::$app_url.'/assets/js/admin.js', 
			['jquery'], 
			null, 
			true 
		);
	}


	/**
	 * Classes autoloader
	 * @param $class
	 * @return mixed
	 */
	public function autoloader($class)
	{
		$folders = [
			'decorators',
			'controllers',
			'tables',
			'models'
		];

		$parts = explode ('\\',$class);
		array_shift ($parts);
		$class_name = array_shift ($parts);

		foreach ($folders as $folder) {
		   $file = self::$app_path.'/'.$folder.'/'.$class_name.'.php';
		   if (!file_exists ($file)) {
			  continue;
		   }

		   return require_once $file;

		   if (!class_exists ($class)) {
			   continue;
		   }
		}
	}
}