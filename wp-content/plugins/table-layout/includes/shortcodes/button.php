<?php

class MMTL_Button_Shortcode
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
		add_filter( 'mmtl_editor_components', array( $this, 'register_component' ) );
		add_filter( 'mmtl_editor_column_accepts', array( $this, 'mmtl_editor_column_accepts' ) );
		
		add_action( 'admin_footer', array( $this, 'admin_print_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_shortcode( 'mmtl-button', array( $this, 'parse' ) );
	}

	public function mmtl_editor_column_accepts( $accepts )
	{
		$accepts['mmtl-button'] = true;

		return $accepts;
	}

	public function register_component( $components )
	{
		$components[ 'mmtl-button' ] = array
		(
			'title'       => __( 'Button', 'table-layout' ),
			'description' => __( '', 'table-layout' ),
			'icon' => '<span class="glyphicons glyphicons-hand-up"></span>'
		);

		return $components;
	}

	public function register_settings()
	{
		MMTL_API::add_settings_page( 'mmtl-button', __( 'Button Settings', 'table-layout' ), array( $this, 'print_settings_page' ) );

		//  Button

		MMTL_API::add_settings_section( 'general', __( 'General', 'table-layout' ), '', 'mmtl-button' );

		MMTL_API::register_setting( 'mmtl-button', 'content' );
		MMTL_API::add_settings_field( 'content', __( 'Text', 'table-layout' ), array( 'MMTL_Form', 'textfield' ), 'mmtl-button', 'general', array
		(
			'id'        => 'mmtl-content',
			'label_for' => 'mmtl-content',
			'class'     => '',
			'name'      => 'content',
			'value'     => ''
		));

		MMTL_API::register_setting( 'mmtl-button', 'url' );
		MMTL_API::add_settings_field( 'level', __( 'URL', 'table-layout' ), array( 'MMTL_Form', 'textfield' ), 'mmtl-button', 'general', array
		(
			'id'        => 'mmtl-url',
			'label_for' => 'mmtl-url',
			'class'     => '',
			'name'      => 'url',
			'value'     => ''
		));

		MMTL_API::register_setting( 'mmtl-button', 'new_window' );
		MMTL_API::add_settings_field( 'new_window', __( 'New Window', 'table-layout' ), array( 'MMTL_Form', 'checkbox' ), 'mmtl-button', 'general', array
		(
			'id'        => 'mmtl-new_window',
			'label_for' => 'mmtl-new_window',
			'class'     => '',
			'name'      => 'new_window',
			'value'     => ''
		));

		MMTL_API::copy_settings_section( 'common', 'layout', 'mmtl-button' );
		MMTL_API::copy_settings_section( 'common', 'attributes', 'mmtl-button' );
	}

	public function parse( $atts, $content = '' )
	{
		extract( shortcode_atts( array
		(
			'id'         => '',
			'class'      => '',
			'type'       => '',
			'url'        => '',
			'new_window' => '',
			'size'       => '',
			'align'      => '',
			'space'      => ''
		), $atts ) );

		$class .= ' mmtl-button';

		if ( $size )
		{
			$class .= ' mmtl-size-' . $size;
		}

		if ( $type )
		{
			$class .= ' mmtl-type-' . $type;
		}

		$class = trim( $class );

		$str = '';

		$str .= sprintf( '<div class="mmtl-button-wrap mmtl-text-align-%s mmtl-space-%s">', 
			esc_attr( $align ), esc_attr( $space ) );

		$str .= sprintf( '<a%s>', MMTL_Common::parse_html_attributes( array
		(
			'id'      => $id,
			'class'   => $class,
			'href'    => $url ? $url : '',
			'_target' => $new_window ? '_blank' : ''
		)));

		$str .= do_shortcode( $content );

		$str .= '</a>';

		$str .= '</div>';

		return $str;
	}

	public function admin_print_scripts()
	{
		if ( ! MMTL_API::is_editor_screen() )
		{
			return;
		}

		$component = MMTL_API::get_component( 'mmtl-button' );

		?>

		<script type="text/html" id="tmpl-mmtl-shortcode-button">
			
			<div class="mmtl-preview">
				<div class="mmtl-preview-header">
					<div class="mmtl-preview-icon"><?php echo $component['icon']; ?></div>
					<div class="mmtl-preview-title"><?php echo $component['title']; ?>: {{ data.content }}</div>
					<ul class="mmtl-preview-meta">
						<# if ( data.url ) { #><li><?php _e( 'url:', 'table-layout' ); ?> {{ data.url }}</li><# } #>
						<# if ( data.new_window ) { #><li><?php _e( 'new window:', 'table-layout' ); ?> {{ data.new_window }}</li><# } #>
						<# if ( data.id ) { #><li><?php _e( 'id:', 'table-layout' ); ?> {{ data.id }}</li><# } #>
						<# if ( data.class ) { #><li><?php _e( 'class:', 'table-layout' ); ?> {{ data.class }}</li><# } #>
						<# if ( data.type ) { #><li><?php _e( 'type:', 'table-layout' ); ?> {{ data.type }}</li><# } #>
						<# if ( data.align ) { #><li><?php _e( 'align:', 'table-layout' ); ?> {{ data.align }}</li><# } #>
						<# if ( data.space ) { #><li><?php _e( 'space:', 'table-layout' ); ?> {{ data.space }}</li><# } #>
						<# if ( data.size ) { #><li><?php _e( 'size:', 'table-layout' ); ?> {{ data.size }}</li><# } #>
					</ul>
				</div>
			</div>

		</script>

		<?php
	}

	public function print_settings_page()
	{
		?>

		<form method="post">

			<?php MMTL_API::settings_fields( 'mmtl-button' ); ?>

			<?php MMTL_API::do_settings_sections( 'mmtl-button' ); ?>

			<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

		</form>

		<?php
	}
}

MMTL_Button_Shortcode::get_instance()->init();

?>