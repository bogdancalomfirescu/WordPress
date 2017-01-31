<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Debug
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
		if ( ! $this->is_active() )
		{
			return;
		}
		
		add_filter( 'plugins_url', array( $this, 'change_assets_url' ), 10, 3 );
	}

	public function is_active()
	{
		return defined( 'MMTL_DEBUG' ) && MMTL_DEBUG;
	}

	public function log( $message )
	{
		if ( ! $this->is_active() )
		{
			return false;
		}

		return error_log( sprintf( '[Table Layout] %s', $message ) );
	}

	public function change_assets_url( $url, $path, $plugin )
	{
		if ( $plugin == MMTL_FILE )
		{
			if ( strpos( $path, '.min.' ) !== false )
			{
				$dev_path = preg_replace( '/\.min(\.(js|css))$/i' , '$1', $path );

				if ( file_exists( plugin_dir_path( MMTL_FILE ) . $dev_path ) )
				{
					$url = str_replace( $path, $dev_path, $url );
				}
			}
		}

		return $url;
	}
}

MMTL_Debug::get_instance()->init();

?>