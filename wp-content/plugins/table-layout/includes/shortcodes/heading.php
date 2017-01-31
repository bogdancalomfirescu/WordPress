<?php

class MMTL_Heading_Shortcode
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

	public function init()
	{
		add_filter( 'mmtl_editor_components', array( $this, 'register_component' ) );
		add_filter( 'mmtl_editor_column_accepts', array( $this, 'mmtl_editor_column_accepts' ) );

		add_action( 'admin_footer', array( $this, 'admin_print_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_shortcode( 'mmtl-heading', array( $this, 'parse' ) );
	}

	public function mmtl_editor_column_accepts( $accepts )
	{
		$accepts['mmtl-heading'] = true;

		return $accepts;
	}

	public function parse( $atts, $content = '' )
	{
		extract( shortcode_atts( array
		(
			'id'    => '',
			'class' => '',
			'space' => '',
			'align' => '',
			'level' => ''
		), $atts ) );

		if ( $content == '' )
		{
			return '';
		}

		$class .= ' mmtl-heading';

		// Space

		if ( $space )
		{
			$class .= ' mmtl-space-' . $space;
		}

		// Align

		if ( $align )
		{
			$class .= ' mmtl-text-align-' . $align;
		}

		// Level

		if ( ! preg_match( '/^([1-6])$/', $level ) )
		{
			$level = 1;
		}

		$tag = 'h' . $level; 

		$str = sprintf( '<%1$s%2$s>%3$s</%1$s>', $tag, MMTL_Common::parse_html_attributes( array
		(
			'id'    => $id,
			'class' => trim( $class )
		)), $content );

		return $str;
	}

	public function register_component( $components )
	{
		$components[ 'mmtl-heading' ] = array
		(
			'title'       => __( 'Heading', 'table-layout' ),
			'description' => __( '', 'table-layout' ),
			'icon' => '<span class="glyphicons glyphicons-text-size"></span>'
		);

		return $components;
	}

	public function register_settings()
	{
		MMTL_API::add_settings_page( 'mmtl-heading', __( 'Heading Settings', 'table-layout' ), array( $this, 'print_settings_page' ) );

		//  heading

		MMTL_API::add_settings_section( 'general', __( 'General', 'table-layout' ), '', 'mmtl-heading' );

		MMTL_API::register_setting( 'mmtl-heading', 'content' );
		MMTL_API::add_settings_field( 'content', __( 'Text', 'table-layout' ), array( 'MMTL_Form', 'textfield' ), 'mmtl-heading', 'general', array
		(
			'id'        => 'mmtl-content',
			'label_for' => 'mmtl-content',
			'class'     => '',
			'name'      => 'content',
			'value'     => ''
		));

		MMTL_API::register_setting( 'mmtl-heading', 'level' );
		MMTL_API::add_settings_field( 'level', __( 'Level', 'table-layout' ), array( 'MMTL_Form', 'dropdown' ), 'mmtl-heading', 'general', array
		(
			'id'        => 'mmtl-level',
			'label_for' => 'mmtl-level',
			'class'     => '',
			'name'      => 'level',
			'value'     => '',
			'options'   => array
			(
				'1' => __( 'Heading 1', 'table-layout' ),
				'2' => __( 'Heading 2', 'table-layout' ),
				'3' => __( 'Heading 3', 'table-layout' ),
				'4' => __( 'Heading 4', 'table-layout' ),
				'5' => __( 'Heading 5', 'table-layout' ),
				'6' => __( 'Heading 6', 'table-layout' )
			)
		));

		MMTL_API::copy_settings_section( 'common', 'attributes', 'mmtl-heading' );
		MMTL_API::copy_settings_section( 'common', 'layout', 'mmtl-heading', array( 'align', 'space' ) );
	}

	public function admin_print_scripts()
	{
		if ( ! MMTL_API::is_editor_screen() )
		{
			return;
		}

		$component = MMTL_API::get_component( 'mmtl-heading' );

		?>

		<script type="text/html" id="tmpl-mmtl-shortcode-heading">
			
			<div class="mmtl-preview">

				<div class="mmtl-preview-header">
					<div class="mmtl-preview-icon"><?php echo $component['icon']; ?></div>
					<div class="mmtl-preview-title"><?php echo $component['title']; ?>: {{ data.content }}</div>
					<ul class="mmtl-preview-meta">
						<# if ( data.id ) { #><li><?php _e( 'id:', 'table-layout' ); ?> {{ data.id }}</li><# } #>
						<# if ( data.class ) { #><li><?php _e( 'class:', 'table-layout' ); ?> {{ data.class }}</li><# } #>
						<# if ( data.level ) { #><li><?php _e( 'level:', 'table-layout' ); ?> {{ data.level }}</li><# } #>
						<# if ( data.align ) { #><li><?php _e( 'align:', 'table-layout' ); ?> {{ data.align }}</li><# } #>
						<# if ( data.space ) { #><li><?php _e( 'space:', 'table-layout' ); ?> {{ data.space }}</li><# } #>
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

			<?php MMTL_API::settings_fields( 'mmtl-heading' ); ?>

			<?php MMTL_API::do_settings_sections( 'mmtl-heading' ); ?>

			<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

		</form>

		<?php
	}
}

MMTL_Heading_Shortcode::get_instance()->init();

?>