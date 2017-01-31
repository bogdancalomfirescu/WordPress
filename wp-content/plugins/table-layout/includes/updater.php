<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Updates
{
	private static $instance = null;

	protected $page_hook = null;

	protected $actions = array();

	static public function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		
	}

	public function init()
	{
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_mmtl_updater_process_action', array( $this, 'do_action' ) );
		
		add_filter( 'mmtl_updater_actions', array( $this, 'register_actions' ), 10, 2 );
	}

	public function finish_update()
	{
		update_option( 'mmtl_version', MMTL_VERSION );

		return true;
	}

	public function get_actions( $from_version = false, $to_version = MMTL_VERSION )
	{
		if ( $from_version === false )
		{
			$from_version = get_option( 'mmtl_version' );
		}

		$all_actions = apply_filters( 'mmtl_updater_actions', array() );

		if ( empty( $all_actions ) || ! is_array( $all_actions ) )
		{
			return array();
		}

		if ( $from_version || $to_version )
		{
			$actions = array();
		
			foreach ( $all_actions as $action_id => $action )
			{
				// skips when version is less than min verion

				if ( $from_version !== false && version_compare( $action['version'], $from_version , '<' ) )
				{
					continue;
				}

				// skips when version is greater than max verion

				if ( $to_version && version_compare( $action['version'], $to_version , '>' ) )
				{
					continue;
				}

				$actions[ $action_id ] = $action;
			}
		}

		else
		{
			$actions = $all_actions;
		}
		
		// sorts on version number
		
		uasort( $actions, array( $this, 'sort_actions' ) );

		if ( count( $actions ) > 0 )
		{
			$actions['_finish'] = array
			(
				'callback' => array( $this, 'finish_update' )
			);
		}

		return $actions;
	}

	public function register_actions( $actions )
	{
		$actions['1.5.0'] = array
		(
			'version'     => '1.5.0',
			'title'       =>  __( '1.5.0 Update' ),
			'description' => __( "Add's text component shortcodes.", 'table-layout' ),
			'callback'    => array( $this, 'process_update_1_5_0' )
		);

		return $actions;
	}

	public function do_action()
	{
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
		{
			return;
		}

		check_admin_referer( 'MMTL_Updater', MMTL_NONCE_NAME );

		$action_id = ! empty( $_POST[ 'action_id' ] ) ? $_POST[ 'action_id' ] : null;

		$actions = $this->get_actions();

		if ( empty( $actions ) )
		{
			wp_send_json_error( __( 'No actions found.', 'table-layout' ) );
		}

		if ( ! $action_id || empty( $actions[ $action_id ] ) )
		{
			wp_send_json_error( __( 'Invalid action.', 'table-layout' ) );
		}

		$action = $actions[ $action_id ];

		$results = call_user_func( $action['callback'] );

		$html = $this->get_result_html( $results );

		wp_send_json_success( $html );

		wp_send_json_success( $result );
	}

	public function register_page()
	{
		$this->page_hook = add_submenu_page( null, __( 'Table Layout Updater', 'table-layout' ), __( 'Table Layout Updater', 'table-layout' ), 'update_plugins', 'mmtl_updater', array( $this, 'print_page' ) );
	}

	public function print_page()
	{
		$actions = $this->get_actions();

		?>

		<div id="mmtl-updater-screen" class="wrap">

			<h2><?php _e( 'Table Layout Updater', 'table-layout' ) ?></h2>

			<?php if ( ! empty( $actions ) ): ?>

			<div class="mmtl-ajax-hide-on-before">
				<p><?php _e( 'Database data needs to be updated.' , 'table-layout'); ?></p>
				<p><?php _e( 'Click the update button to update.' , 'table-layout'); ?></p>
			</div>

			<form action="" method="post">

				<p>
					<?php echo MMTL_Common::ajax_loader(); ?>
					<span class="mmtl-ajax-show-on-complete"><?php _e( 'Update complete.', 'table-layout' ); ?></span>
				</p>

				<div class="mmtl-output"></div>

				<p class="submit mmtl-ajax-hide-on-complete">
					<?php submit_button( __( 'Update', 'table-layout' ), 'primary', 'submit', false ); ?>
				</p>

			</form>

			<?php else : ?>
			<p><?php _e( 'No updates available.', 'table-layout' ); ?></p>
			<?php endif; ?>

		</div><!-- . wrap -->

		<?php
	}

	public function is_updateable()
	{
		$actions = $this->get_actions();
		
		return ! empty( $actions );
	}

	public function notices()
	{
		if ( ! $this->is_updateable() )
		{
			return;
		}

		$screen = get_current_screen();

		if ( $screen->id == $this->page_hook )
		{
			return;
		}

	    ?>
	    <div class="notice notice-error">
	        <p>
	        	<strong><?php _e( 'Table Layout', 'table-layout' ); ?></strong>:
	        	<?php _e( 'Database data needs to be updated.', 'table-layout' ); ?>
	        	<a href="<?php echo admin_url( 'admin.php?page=mmtl_updater' ); ?>"><?php _e( 'Go to the update page', 'table-layout' ); ?></a>
	    	</p>
	    </div>
	    <?php
	}

	public function get_result_html( $results )
	{
		$html = '';

		if ( ! empty( $results ) && is_array( $results ) )
		{
			$html .= '<ul class="mmtl-results">';

			foreach ( $results as $post_id => $result )
			{
				if ( is_wp_error( $result ) )
				{
					$class = 'mmtl-result-error';
					$icon = 'no';
					$message = $result->get_error_message();
				}

				else
				{
					$class = 'mmtl-result-success';

					$icon = 'yes';

					if ( is_numeric( $result ) )
					{
						$message = '';
					}

					else
					{
						$message = $result;
					}
				}

				$html .= sprintf( '<li class="mmtl-result %s">', $class );

				$html .= sprintf( '<span class="mmtl-result-icon dashicons dashicons-%s"></span>', esc_attr( $icon ) );

				$html .= sprintf( '#%s <strong>%s</strong><br>%s', $post_id, get_the_title( $post_id ), $message );

				$html .= '</li>';
			}

			$html .= '</ul>';
		}

		return $html;
	}

	public function enqueue_scripts()
	{
		$screen = get_current_screen();

		if ( $screen->id != $this->page_hook )
		{
			return;
		}

		wp_enqueue_style( 'table-layout-admin' );

		wp_enqueue_script( 'table-layout-updater', plugins_url( 'js/updater.js', MMTL_FILE ), array( 'jquery' ), false, true );

		wp_localize_script( 'table-layout-updater', 'MMTL_Updater_Options', array
		(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'noncename' => MMTL_NONCE_NAME,
			'nonce'     => wp_create_nonce( 'MMTL_Updater' ),
			'actions'  => array_keys( $this->get_actions() )
		));
	}

	public function sort_actions( $a, $b )
	{
		return version_compare( $a['version'], $a['version'] );
	}

	public function process_update_1_5_0()
	{
		// replaces [mmtl-col]{content}[/mmtl-col] with [mmtl-col][mmtl-text]{content}[/mmtl-text][/mmtl-col]

		global $wpdb;

		$results = array();

		$post_types = get_post_types( array( 'public' => true ) );

		foreach ( $post_types as $post_type )
		{
			$sql = sprintf( "SELECT ID, post_content FROM %s WHERE post_type='%s' AND post_content LIKE '%%[mmtl-col%%';", $wpdb->posts, $post_type );
			
			$posts = $wpdb->get_results( $sql, OBJECT );

			if ( empty( $posts ) )
			{
				continue;
			}

			foreach ( $posts as $post )
			{
				$post_content = preg_replace( '/(\[mmtl-col.*?\])(.*?)(\[\/mmtl-col\])/s', '$1[mmtl-text]$2[/mmtl-text]$3', $post->post_content );
			
				$post_id = wp_update_post( array
				(
					'ID' => $post->ID,
					'post_content' => $post_content
				), true );
				
				$results[ $post->ID ] = $post_id;
			}
		}

		return $results;
	}
}

MMTL_Updates::get_instance()->init();

?>