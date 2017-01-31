<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Ajax
{
	private static $instance = null;

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
		add_action( 'wp_ajax_mmtl_set_editor_state', array( $this, 'set_editor_state' ) );
		add_action( 'wp_ajax_mmtl_get_attachment_sizes', array( $this, 'get_attachment_sizes' ) );
		add_action( 'wp_ajax_mmtl_get_components_screen', array( $this, 'get_components_screen' ) );
		add_action( 'wp_ajax_mmtl_get_settings_page', array( $this, 'get_settings_page' ) );
	}

	public function get_components_screen()
	{
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
		{
			return;
		}

		check_admin_referer( 'editor', MMTL_NONCE_NAME );

		$component_id = ! empty( $_POST['id'] ) ? $_POST['id'] : 0;

		$html = MMTL_Editor::get_instance()->get_components_screen( $component_id );

		wp_send_json_success( $html );
	}

	public function get_settings_page()
	{
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
		{
			return;
		}

		check_admin_referer( 'editor', MMTL_NONCE_NAME );

		$page = ! empty( $_POST['_page'] ) ? $_POST['_page'] : null;

		$html = MMTL_Settings::get_instance()->get_settings_page( $page );

		wp_send_json_success( $html );
	}

	public function set_editor_state()
	{
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
		{
			return;
		}

		check_admin_referer( 'editor', MMTL_NONCE_NAME );

		$post_id  = ! empty( $_POST['post_id'] ) ? $_POST['post_id'] : 0;
		$activate = ! empty( $_POST['active'] );

		MMTL_Editor::get_instance()->set_active_state( $activate, $post_id );

		wp_send_json_success();
	}

	public function get_attachment_sizes()
	{
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
		{
			return;
		}

		check_admin_referer( 'editor', MMTL_NONCE_NAME );

		if ( empty( $_POST['attachment'] ) )
		{
			wp_send_json_error();
		}

		$id = $_POST['attachment'];

		// makes sure we have an array (multiple urls possible)

		if ( ! is_array( $id ) )
		{
			$ids = array( $id );
		}

		else
		{
			$ids = $id;
		}

		// gets sizes

		$sizes = array();

		foreach ( $ids as $key => $value )
		{
			if ( is_numeric( $value ) )
			{
				$attachment_id = $value;
			}

			else
			{
				$attachment_id = MMTL_Common::get_attachment_id_by_url( $value );
			}

			if ( ! $attachment_id )
			{
				continue;
			}

			$attachment_sizes = MMTL_Common::get_attachment_sizes( $attachment_id, 'url' );

			if ( empty( $attachment_sizes ) )
			{
				continue;
			}

			$sizes[ $key ] = $attachment_sizes;
		}

		// only give array back when posted url is array

		if ( ! is_array( $id ) )
		{
			if ( count( $sizes ) > 0 )
			{
				$sizes = $sizes[0];
			}
		}

		wp_send_json_success( $sizes );
	}
}

MMTL_Ajax::get_instance()->init();

?>