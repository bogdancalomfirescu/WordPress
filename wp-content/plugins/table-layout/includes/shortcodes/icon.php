<?php

class MMTL_Icon_Shortcode
{
	static private $instance = null;

	protected $icon_data = array();

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
		$this->icon_data = array
		(
			'glyphicons' => array
			(
				'name' => 'Glyphicons',
				'version' => '1.9.2',
				'file' => 'css/glyphicons.css',
				'format' => '<span class="glyphicons %s"></span>',
				'pattern' => 'glyphicons-[a-z0-9_-]+'
			),
			
			'font-awesome' => array
			(
				'name' => 'Font Awesome',
				'version' => '4.5.0',
				'file' => '/css/font-awesome.min.css',
				'format' => '<i class="fa %s"></i>',
				'pattern' => 'fa-[a-z0-9_-]+'
			),

			'dashicons' => array
			(
				'name' => 'Dashicons',
				'version' => false,
				'file' =>  'css/dashicons.css',
				'format' => '<span class="dashicons %s"></span>',
				'pattern' => 'dashicons-(?!before)[a-z0-9_-]+'
			),

			'genericon' => array
			(
				'name' => 'Genericons',
				'version' => '3.4.1',
				'file' =>  'css/genericons.css',
				'format' => '<span class="genericon %s"></span>',
				'pattern' => 'genericon-[a-z0-9_-]+'
			),

			'themify-icons' => array
			(
				'name' => 'Themify',
				'version' => '1.0.1',
				'file' =>  'css/themify-icons.css',
				'format' => '<span class="%s"></span>',
				'pattern' => 'ti-[a-z0-9_-]+'
			),

			'foundation-icons' => array
			(
				'name' => 'Foundation',
				'version' => '3.0',
				'file' =>  'css/foundation-icons.css',
				'format' => '<span class="%s"></span>',
				'pattern' => 'fi-[a-z0-9_-]+'
			),

			'ionicons' => array
			(
				'name' => 'Ionicons',
				'version' => '2.0.1',
				'file' =>  'css/ionicons.min.css',
				'format' => '<span class="%s"></span>',
				'pattern' => 'ion-[a-z0-9_-]+'
			),

			'linecons' => array
			(
				'name' => 'Linecons',
				'version' => false,
				'file' =>  'css/linecons.css',
				'format' => '<span class="%s"></span>',
				'pattern' => 'li_[a-z0-9_-]+'
			)
		);
		
		add_filter( 'mmtl_editor_components', array( $this, 'register_component' ) );
		add_filter( 'mmtl_editor_column_accepts', array( $this, 'mmtl_editor_column_accepts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'admin_print_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_shortcode( 'mmtl-icon', array( $this, 'parse' ) );

		add_action( 'wp_ajax_mmtl_get_icon_picker', array( $this, 'get_icon_picker' ) );
	}

	public function mmtl_editor_column_accepts( $accepts )
	{
		$accepts['mmtl-icon'] = true;

		return $accepts;
	}

	public function register_settings()
	{
		MMTL_API::add_settings_page( 'mmtl-icon', __( 'Icon Settings', 'table-layout' ), array( $this, 'print_settings_page' ) );

		// icon

		MMTL_API::add_settings_section( 'general', __( 'Icon', 'table-layout' ), '', 'mmtl-icon' );

		MMTL_API::register_setting( 'mmtl-icon', 'icon' );
		MMTL_API::add_settings_field( 'icon', '', array( $this, 'print_icon_picker' ), 'mmtl-icon', 'general' );

		MMTL_API::copy_settings_section( 'common', 'attributes', 'mmtl-icon' );
		MMTL_API::copy_settings_section( 'common', 'layout', 'mmtl-icon' );
	}

	public function get_icon_picker()
	{
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
		{
			return;
		}

		check_admin_referer( 'editor', MMTL_NONCE_NAME );

		$lib_id = ! empty( $_POST['lib_id'] ) ? $_POST['lib_id'] : null;

		if ( empty( $this->icon_data[ $lib_id ] ) )
		{
			wp_send_json_error( sprintf( __( 'Library "%s" could not be found.' ), $lib_id ) );
		}

		$lib = $this->icon_data[ $lib_id ];

		$classes = $this->get_icon_classes( $lib_id );

		if ( is_wp_error( $classes ) )
		{
			wp_send_json_error( $classes->get_error_message() );
		}

		if ( ! empty( $classes ) )
		{
			$str = '<ul>';

			foreach ( $classes as $class )
			{
				$icon = sprintf( $lib['format'], esc_attr( $class ) );

				$str .= sprintf( '<li data-id="%s">%s</li>', esc_attr( $class ), $icon );
			}

			$str .= '</ul>';
		}
		
		wp_send_json_success( $str );
	}

	public function get_icon_library_id_from_class( $class )
	{
		foreach ( $this->icon_data as $lib_id => $lib )
		{
			if ( preg_match( '/' . $lib['pattern'] . '/' , $class ) )
			{
				return $lib_id;
			}
		}

		return null;
	}

	public function get_icon_classes( $lib_id )
	{
		// checks if already loaded

		$saved = get_option( 'mmtl_icon_classes', array() );

		if ( ! is_array( $saved ) )
		{
			$saved = array();
		}

		if ( ! empty( $saved[ $lib_id ] ) && is_array( $saved[ $lib_id ] ) )
		{
			return $saved[ $lib_id ];
		}

		if ( ! isset( $this->icon_data[ $lib_id ] ) )
		{
			return false;
		}

		$data = $this->icon_data[ $lib_id ];

		$css = file_get_contents( trailingslashit( plugin_dir_path( MMTL_FILE ) ) . $data['file'] );

		if ( $css === false )
		{
			return new WP_Error( 'file_get_contents', __( 'Unable to get file contents.', 'table-layout' ) );
		}

		$regexp = sprintf( '/\.(%s):before\s*\{/s', $data['pattern'] );

		preg_match_all( $regexp, $css, $matches );

		if ( ! is_array( $matches ) || count( $matches ) <= 1 || ! is_array( $matches[1] ) )
		{
			return new WP_Error( 'preg_match_all', __( 'No Icons found.', 'table-layout' ) );
		}

		$classes = $matches[1];

		// saves classes

		$saved[ $lib_id ] = $classes;

		update_option( 'mmtl_icon_classes', $saved );

		return $matches[1];
	}

	public function register_component( $components )
	{
		$components[ 'mmtl-icon' ] = array
		(
			'title'       => __( 'Icon', 'table-layout' ),
			'description' => __( '', 'table-layout' ),
			'icon' => '<span class="glyphicons glyphicons-star-empty"></span>'
		);

		return $components;
	}

	public function parse( $atts, $content = '' )
	{
		extract( shortcode_atts( array
		(
			'id'    => '',
			'class' => '',
			'type'  => '',
			'icon'  => '',
			'size'  => '',
			'align' => '',
			'space' => ''
		), $atts ) );

		$class .= ' mmtl-icon';

		if ( $align )
		{
			$class .= ' mmtl-text-align-' . $align;
		}

		if ( $space )
		{
			$class .= ' mmtl-space-' . $space;
		}

		if ( $size )
		{
			$class .= ' mmtl-size-' . $size;
		}

		if ( $type )
		{
			$class .= ' mmtl-type-' . $type;
		}

		$class = trim( $class );

		$str = sprintf( '<div%s>', MMTL_Common::parse_html_attributes( array
		(
			'id'    => $id,
			'class' => $class
		)));

		foreach ( $this->icon_data as $lib_id => $lib )
		{
			if ( preg_match( '/' . $lib['pattern'] . '/' , $icon ) )
			{
				$str .= sprintf( $lib['format'], esc_attr( $icon ) );

				continue;
			}
		}

		$str .= '</div>';

		return $str;
	}

	public function enqueue_icon_styles()
	{
		if ( ! is_admin() && ! MMTL_Common::is_shortcode_used( 'mmtl-row' ) )
		{
			return;
		}

		foreach ( $this->icon_data as $lib_id => $lib )
		{
			if ( ! is_admin() && ! $this->is_icon_library_used( $lib_id ) )
			{
				continue;
			}

			wp_enqueue_style( $lib_id, plugins_url( $lib['file'], MMTL_FILE ), null, $lib['version'] );
		}
	}

	public function is_icon_library_used( $lib_id )
	{
		global $wp_query;

		if ( ! empty( $this->icon_data[ $lib_id ] ) )
		{
			$lib = $this->icon_data[ $lib_id ];

			$posts = $wp_query->posts;
	    
		    foreach ( $posts as $post )
		    {
				if ( preg_match( '/icon="' . $lib['pattern'] . '"/is', $post->post_content ) )
				{
					return true;
				}
		    }
		}

	    return false;
	}

	public function enqueue_scripts()
	{
		if ( ! MMTL_Common::is_shortcode_used( 'mmtl-row' ) )
		{
			return;
		}

		$this->enqueue_icon_styles();
	}

	public function admin_enqueue_scripts()
	{
		if ( ! MMTL_Editor::get_instance()->is_editor_screen() )
		{
			return;
		}

		$this->enqueue_icon_styles();
	}

	public function admin_print_scripts()
	{
		$editor = MMTL_Editor::get_instance();

		if ( ! $editor->is_editor_screen() )
		{
			return;
		}

		$component = $editor->get_component( 'mmtl-icon' );

		?>

		<script type="text/html" id="tmpl-mmtl-shortcode-icon">
			
			<div class="mmtl-preview">

				<div class="mmtl-preview-header">
					<div class="mmtl-preview-icon"><?php echo $component['icon']; ?></div>
					<div class="mmtl-preview-title"><?php echo $component['title']; ?>
						<?php foreach ( $this->icon_data as $lib_id => $lib ) : ?>
						<# if ( data.icon && data.icon.match( /<?php echo $lib['pattern']; ?>/ ) ) { #> <?php printf( $lib['format'], '{{ data.icon }}' ); ?><# } #>
						<?php endforeach; ?>
					</div>
					<ul class="mmtl-preview-meta">
						<# if ( data.id ) { #><li data-type="id">{{ data.id }}</li><# } #>
						<# if ( data.class ) { #><li><?php _e( 'class:', 'table-layout' ); ?> {{ data.class }}</li><# } #>
						<# if ( data.type ) { #><li><?php _e( 'type:', 'table-layout' ); ?> {{ data.type }}</li><# } #>
						<# if ( data.size ) { #><li><?php _e( 'size:', 'table-layout' ); ?> {{ data.size }}</li><# } #>
						<# if ( data.align ) { #><li><?php _e( 'align:', 'table-layout' ); ?> {{ data.align }}</li><# } #>
						<# if ( data.space ) { #><li><?php _e( 'space:', 'table-layout' ); ?> {{ data.space }}</li><# } #>
					</ul>
				</div>

			</div>

		</script>

		<?php
	}

	public function print_icon_picker()
	{
		$icon = ! empty( $_POST['icon'] ) ? $_POST['icon'] : '';

		$lib_id = $this->get_icon_library_id_from_class( $icon );

		?>

		<div class="mmtl-icon-picker">
			<div class="mmtl-icon-picker-header">
				<p>
					<select class="mmtl-icon-picker-lib">
						<option value=""<?php selected( $lib_id, '' ) ?>><?php esc_html_e( '- library -' ); ?></option>
						<?php foreach ( $this->icon_data as $key => $lib ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $lib_id, $key ) ?>><?php echo esc_html( $lib['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p><input type="text" class="mmtl-icon-picker-search" placeholder="<?php esc_attr_e( 'Search', 'table-layout' ); ?>"></p>
			</div>
			<div class="mmtl-icon-picker-content">

				<?php MMTL_Common::notice( '', array( 'type' => 'error', 'class' => 'mmtl-icon-picker-output' ) ); ?>

				<div class="mmtl-icon-picker-icons"></div>

			</div>
		</div>

		<input type="hidden" id="mmtl-icon" name="icon" value="<?php echo esc_attr( $icon ); ?>">

		<?php
	}

	public function print_settings_page()
	{
		?>

		<form method="post">

			<?php MMTL_API::settings_fields( 'mmtl-icon' ); ?>

			<?php MMTL_API::do_settings_sections( 'mmtl-icon' ); ?>

			<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

		</form>

		<?php
	}
}

MMTL_Icon_Shortcode::get_instance()->init();

?>