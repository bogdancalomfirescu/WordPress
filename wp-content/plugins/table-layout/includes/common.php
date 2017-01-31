<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Common
{
	static public function write_to_file( $file, $data )
	{
		$handle = fopen( $file, 'w' );

		if ( ! $handle )
		{
			return new WP_Error( __( 'Cannot open file for writing', 'table-layout' ) );
		}

		if ( fwrite( $handle, $data ) === false )
		{
			return new WP_Error( __( 'Cannot write to file', 'table-layout' ) );
		}

		fclose( $handle );
	}

	// https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
	static public function notice( $message = '', $args = '' )
	{
		$args = wp_parse_args( $args, array
		(
			'type' => 'info',
			'class' => '',
			'dismissable' => false
		));

		extract( $args );

		if ( $class )
		{
			$class = ' ' . $class;
		}

		if ( $type )
		{
			$class .= ' notice-' . $type;
		}

		if ( $dismissable )
		{
			$class .= ' is-dismissible';
		}

		printf( '<div class="mmtl-notice notice%s"><p>%s</p></div>', esc_attr( $class ), $message );
	}

	static public function ajax_loader()
	{
		return '<span class="mmtl-loader"><span class="mmtl-spin dashicons dashicons-image-rotate"></span></span>';
	}

	static public function parse_html_attributes( $attributes, $extra = '' )
	{
		$extra = trim( $extra );
	 
		$str = '';
	 
		foreach ( $attributes as $key => $value )
		{
			if ( (string) $value === '' )
			{
				continue;
			}
	 
			$str .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}
	 
		if ( $extra )
		{
			$str .= ' ' . $extra;
		}
	 
		return $str;
	}
	
	static public function load_template( $path, $data = array(), $return = false )
	{
		if ( $return )
		{
			ob_start();
		}

		if ( is_array( $data ) )
		{
			extract( $data );
		}

		require_once plugin_dir_path( MMTL_FILE ) . 'templates/' . $path . '.php';

		if ( $return )
		{
			return ob_get_clean();
		}
	}

	static public function get_attachment_sizes( $attachment_id, $abs = false )
	{
		$data = wp_get_attachment_metadata( $attachment_id );

		if ( ! $data )
		{
			return false;
		}
		
		$sizes = $data['sizes'];

		// adds original size

		$sizes['full'] = array
		(
			'file'   => basename( $data['file'] ),
			'width'  => $data['width'],
			'height' => $data['height']
		);

		// sets dir

		$upload_dir = wp_upload_dir();

		$base = trailingslashit( dirname( $data['file'] ) );

		if ( $abs == 'path' )
		{
			$base = trailingslashit( $upload_dir['basedir'] ) . $base;
		}

		else if ( $abs == 'url' )
		{
			$base = trailingslashit( $upload_dir['baseurl'] ) . $base;
		}

		foreach ( $sizes as &$size )
		{
			$size['file'] = $base . ltrim( $size['file'], '/' );
		}

		return $sizes;
	}

	static public function get_attachment_id_by_url( $url )
	{
		// removes size suffix

		$guid = preg_replace( '/-\d+x\d+(\.[a-z0-9]+)$/i', '$1', $url );

		// gets attachment id

		global $wpdb;

		$attachment = $wpdb->get_row( sprintf( 'SELECT ID FROM %sposts WHERE guid="%s"', esc_sql( $wpdb->prefix ), esc_sql( $guid ) ) );

		if ( $attachment )
		{
			return $attachment->ID;
		}

		return false;
	}

	static public function html_class_to_array( $class )
	{
		$class = trim( preg_replace( '/\s+/' , ' ', $class ) );

		if ( $class )
		{
			$classes = explode( ' ', $class );
		}

		else
		{
			$classes = array();
		}

		return $classes;
	}

	static public function is_shortcode_used( $tag )
	{
		global $wp_query;

	    $posts   = $wp_query->posts;
	    $pattern = get_shortcode_regex();
	    
	    if ( is_array( $posts ) )
	   	{
	    	foreach ( $posts as $post )
		    {
				if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
					&& array_key_exists( 2, $matches )
					&& in_array( $tag, $matches[2] ) )
				{
					return true;
				}    
		    }
	    }

	    return false;
	}

	static public function get_column_span( $width )
	{
		switch ( $width )
		{
			case '1/12'  : return 1;
			case '1/6'   : return 2;
			case '1/4'   : return 3;
			case '1/3'   : return 4;
			case '5/12'  : return 5;
			case '1/2'   : return 6;
			case '7/12'  : return 7;
			case '2/3'   : return 8;
			case '3/4'   : return 9;
			case '5/6'   : return 10;
			case '11/12' : return 11;
		}

		return 12;
	}
}

?>