<?php

class MMTL_Row_Shortcode
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
		add_filter( 'mmtl_sanitize_options', array( $this, 'sanitize_options' ), 10, 2 );

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_shortcode( 'mmtl-row', array( $this, 'parse' ) );
	}

	public function register_component( $components )
	{
		$components[ 'mmtl-row' ] = array
		(
			'title'       => __( 'Row', 'table-layout' ),
			'description' => __( 'A responsive row', 'table-layout' ),
			'controls'    => array( 'add', 'add_before', 'edit', 'copy', 'delete', 'toggle', 'add_after', 'source' ),
			'accepts'     => array( 'mmtl-col' )
		);

		return $components;
	}

	public function register_settings()
	{
		MMTL_API::add_settings_page( 'mmtl-row', __( 'Row Settings', 'table-layout' ), array( $this, 'print_settings_page' ) );

		// layout

		MMTL_API::add_settings_section( 'layout', __( 'Layout', 'table-layout' ), '', 'mmtl-row' );

		MMTL_API::add_settings_field( 'layout_picker', __( 'Layout', 'table-layout' ), array( $this, 'print_layout_picker' ), 'mmtl-row', 'layout' );

		MMTL_API::register_setting( 'mmtl-row', 'layout' );
		MMTL_API::add_settings_field( 'layout', __( 'Custom', 'table-layout' ), array( 'MMTL_Form', 'textfield' ), 'mmtl-row', 'layout', array
		(
			'id'        => 'mmtl-layout',
			'label_for' => 'mmtl-layout',
			'class'     => '',
			'name'      => 'layout',
			'value'     => '',
			'description' => __( 'e.g. `1/3 + 2/3` creates 2 columns with one third and two third width.', 'table-layout' )
		));

		MMTL_API::copy_settings_section( 'common', 'background', 'mmtl-row' );
		MMTL_API::copy_settings_section( 'common', 'attributes', 'mmtl-row' );
	}

	public function parse( $atts, $content = '' )
	{
		extract( shortcode_atts( array
		(
			'id'           => '',
			'class'        => '',
			'bg_image'     => '',
			'bg_position'  => '',
			'bg_repeat'    => '',
			'bg_size'      => ''
		), $atts ) );

		// classes
		
		$class .= ' mmtl-row';

		if ( $bg_image )
		{
			$class .= ' mmtl-has-bgimage';
			$class .= ' mmtl-has-overlay';
		}

		// styles

		$style = '';

		if ( $bg_image )
		{
			$style .= sprintf( 'background-image: url("%s");', $bg_image );
		}

		if ( $bg_position )
		{
			$style .= sprintf( 'background-position: %s;', $bg_position );
		}

		if ( $bg_repeat )
		{
			$style .= sprintf( 'background-repeat: %s;', $bg_repeat );
		}

		if ( $bg_size )
		{
			$style .= sprintf( 'background-size: %s;', $bg_size );
		}

		//

		$str = sprintf( '<div%s>', MMTL_Common::parse_html_attributes( array
		(
			'id'    => $id,
			'class' => trim( $class ),
			'style' => $style
		)));

		$str .= sprintf( '<div class="mmtl-content">%s</div>', do_shortcode( $content ) );

		if ( $bg_image )
		{
			$str .= '<div class="mmtl-overlay"></div>';
		}

		$str .= '</div>';

		return $str;
	}

	public function print_layout_picker()
	{
		?>

		<ul class="mmtl-layout">
			<li class="mmtl-row"><a href="#" title="1/1"><span class="mmtl-col-xs-12"></span></a></li>
			<li class="mmtl-row"><a href="#" title="1/2 + 1/2"><span class="mmtl-col-xs-6"></span><span class="mmtl-col-xs-6"></span></a></li>
			<li class="mmtl-row"><a href="#" title="1/3 + 1/3 + 1/3"><span class="mmtl-col-xs-4"></span><span class="mmtl-col-xs-4"></span><span class="mmtl-col-xs-4"></span></span></a></li>
			<li class="mmtl-row"><a href="#" title="1/3 + 2/3"><span class="mmtl-col-xs-4"></span><span class="mmtl-col-xs-8"></span></a></li>
			<li class="mmtl-row"><a href="#" title="2/3 + 1/3"><span class="mmtl-col-xs-8"></span><span class="mmtl-col-xs-4"></span></a></li>
			<li class="mmtl-row"><a href="#" title="1/4 + 3/4"><span class="mmtl-col-xs-3"></span><span class="mmtl-col-xs-9"></span></a></li>
			<li class="mmtl-row"><a href="#" title="3/4 + 1/4"><span class="mmtl-col-xs-9"></span><span class="mmtl-col-xs-3"></span></a></li>
			<li class="mmtl-row"><a href="#" title="1/4 + 1/4 + 1/4 + 1/4"><span class="mmtl-col-xs-3"></span><span class="mmtl-col-xs-3"></span><span class="mmtl-col-xs-3"></span></span><span class="mmtl-col-xs-3"></span></a></li>
		</ul>

		<?php
	}

	public function print_settings_page()
	{
		?>

		<form method="post">

			<?php MMTL_API::settings_fields( 'mmtl-row' ); ?>

			<?php MMTL_API::do_settings_sections( 'mmtl-row' ); ?>

			<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

		</form>

		<?php
	}

	public function sanitize_options( $input, $option_group )
	{
		if ( $option_group == 'mmtl-row' )
		{
			if ( isset( $input['layout'] ) )
			{
				unset( $input['layout'] );
			}
		}

		return $input;
	}
}

MMTL_Row_Shortcode::get_instance()->init();

?>