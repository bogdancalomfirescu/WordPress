<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Responsive Table Layout
 Plugin URI:
 Description: Provides an easy- and user friendly way to make your site's content more responsive.
 Version:     1.5.4
 Author:      Maarten Menten
 Author URI:  https://www.ivalue.be/blog/author/mmenten/
 License:     GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.1.html
 Text Domain: table-layout
 Domain Path: /languages
------------------------------------------------------------------------------------------------------------------------
*/

define( 'MMTL_FILE', __FILE__ );
define( 'MMTL_NONCE_NAME', '_mmtlnonce' );
define( 'MMTL_VERSION', '1.5.4' );

require_once plugin_dir_path( MMTL_FILE ) . 'includes/debug.php';

require_once plugin_dir_path( MMTL_FILE ) . 'includes/common.php';
require_once plugin_dir_path( MMTL_FILE ) . 'includes/shortcodes.php';
require_once plugin_dir_path( MMTL_FILE ) . 'includes/shortcodes/row.php';
require_once plugin_dir_path( MMTL_FILE ) . 'includes/shortcodes/column.php';
require_once plugin_dir_path( MMTL_FILE ) . 'includes/shortcodes/text.php';
require_once plugin_dir_path( MMTL_FILE ) . 'includes/shortcodes/heading.php';
require_once plugin_dir_path( MMTL_FILE ) . 'includes/shortcodes/button.php';
require_once plugin_dir_path( MMTL_FILE ) . 'includes/shortcodes/icon.php';
require_once plugin_dir_path( MMTL_FILE ) . 'includes/api.php';

if ( is_admin() )
{
	require_once plugin_dir_path( MMTL_FILE ) . 'includes/form.php';
	require_once plugin_dir_path( MMTL_FILE ) . 'includes/settings.php';
	require_once plugin_dir_path( MMTL_FILE ) . 'includes/ajax.php';
	require_once plugin_dir_path( MMTL_FILE ) . 'includes/editor.php';
	require_once plugin_dir_path( MMTL_FILE ) . 'includes/updater.php';
}

class MM_Table_Layout
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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 5 );
		add_filter( 'body_class', array( $this, 'body_class' ) );

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	public function load_textdomain()
	{
		load_plugin_textdomain( 'table-layout', false, plugin_basename( dirname( MMTL_FILE ) ) . '/languages' );
	}

	public function body_class( $classes )
	{
		if ( MMTL_API::is_table_layout( 'mmtl-row' ) )
		{
			$classes[] = 'table-layout';
		}

		return $classes;
	}

	public function enqueue_scripts()
	{
		wp_register_style( 'table-layout', plugins_url( 'css/table-layout.min.css', MMTL_FILE ) );

		if ( MMTL_API::is_table_layout( 'mmtl-row' ) )
		{
			wp_enqueue_style( 'table-layout-theme', plugins_url( 'css/table-layout-theme.min.css', MMTL_FILE ) );
		}
	}

	public function admin_enqueue_scripts()
	{
		wp_register_style( 'glyphicons', plugins_url( 'css/glyphicons.css', MMTL_FILE ), null, '1.9.2' );
		wp_register_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', MMTL_FILE ), null, '4.5.0' );
		wp_register_style( 'jquery-ui-structure', plugins_url( 'css/jquery-ui.structure.min.css', MMTL_FILE ), null, '1.11.4' );
		wp_register_style( 'table-layout-admin', plugins_url( 'css/admin.min.css', MMTL_FILE ) );
		wp_register_style( 'table-layout', plugins_url( 'css/table-layout.min.css', MMTL_FILE ) );

		wp_register_script( 'table-layout-admin', plugins_url( 'js/admin.min.js', MMTL_FILE ), null, false, true );
	}
}

MM_Table_Layout::get_instance()->init();

?>