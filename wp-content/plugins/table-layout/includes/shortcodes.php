<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Shortcodes
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
		add_filter( 'the_content', array( $this, 'the_content' ), 5 );
		add_filter( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );

		add_filter( 'mmtl_component', array( $this, 'add_common_controls' ), 1, 15 );

		add_action( 'admin_init', array( $this, 'register_common_settings' ), 5 );
	}

	public function register_common_settings()
	{
		// Attributes

		MMTL_API::add_settings_section( 'attributes', __( 'Attributes', 'table-layout' ), '', 'common' );

		MMTL_API::register_setting( 'common', 'id', 'trim|sanitize_title' );
		MMTL_API::add_settings_field( 'id', __( 'ID' ), array( 'MMTL_Form', 'textfield' ), 'common', 'attributes', array
		(
			'id'        => 'mmtl-id',
			'label_for' => 'mmtl-id',
			'class'     => '',
			'name'      => 'id',
			'value'     => ''
		));

		MMTL_API::register_setting( 'common', 'class' );
		MMTL_API::add_settings_field( 'class', __( 'Class', 'table-layout' ), array( 'MMTL_Form', 'textfield' ), 'common', 'attributes', array
		(
			'id'        => 'mmtl-class',
			'label_for' => 'mmtl-class',
			'class'     => '',
			'name'      => 'class',
			'value'     => ''
		));

		// Layout

		MMTL_API::add_settings_section( 'layout', __( 'Layout', 'table-layout' ), '', 'common' );

		MMTL_API::register_setting( 'common', 'align' );
		MMTL_API::add_settings_field( 'align', __( 'Alignment', 'table-layout' ), array( 'MMTL_Form', 'dropdown' ), 'common', 'layout', array
		(
			'id'        => 'mmtl-align',
			'label_for' => 'mmtl-align',
			'class'     => '',
			'name'      => 'align',
			'value'     => '',
			'options'   => array
			(
				''       => '',
				'left'   => __( 'left', 'table-layout' ),
				'center' => __( 'center', 'table-layout' ),
				'right'  => __( 'right', 'table-layout' )
			)
		));

		MMTL_API::register_setting( 'common', 'space' );
		MMTL_API::add_settings_field( 'space', __( 'Space', 'table-layout' ), array( 'MMTL_Form', 'dropdown' ), 'common', 'layout', array
		(
			'id'        => 'mmtl-space',
			'label_for' => 'mmtl-space',
			'class'     => '',
			'name'      => 'space',
			'value'     => 'medium',
			'options'   => array
			(
				''       => '',
				'small'  => __( 'small', 'table-layout' ),
				'medium' => __( 'medium', 'table-layout' ),
				'large'  => __( 'large', 'table-layout' )
			)
		));

		MMTL_API::register_setting( 'common', 'size' );
		MMTL_API::add_settings_field( 'size', __( 'Size', 'table-layout' ), array( 'MMTL_Form', 'dropdown' ), 'common', 'layout', array
		(
			'id'        => 'mmtl-size',
			'label_for' => 'mmtl-size',
			'class'     => '',
			'name'      => 'size',
			'value'     => 'medium',
			'options'   => array
			(
				''       => '',
				'small'  => __( 'small', 'table-layout' ),
				'medium' => __( 'medium', 'table-layout' ),
				'large'  => __( 'large', 'table-layout' )
			)
		));

		// Background

		MMTL_API::add_settings_section( 'background', __( 'Background', 'table-layout' ), '', 'common' );

		MMTL_API::register_setting( 'common', 'bg_image' );
		MMTL_API::add_settings_field( 'bg_image', __( 'Image', 'table-layout' ), array( 'MMTL_Form', 'image_picker' ), 'common', 'background', array
		(
			'id'        => 'mmtl-bg_image',
			'label_for' => 'mmtl-bg_image',
			'class'     => '',
			'name'      => 'bg_image',
			'value'     => ''
		));

		MMTL_API::register_setting( 'common', 'bg_repeat' );
		MMTL_API::add_settings_field( 'bg_repeat', __( 'Repeat', 'table-layout' ), array( 'MMTL_Form', 'dropdown' ), 'common', 'background', array
		(
			'id'        => 'mmtl-bg_repeat',
			'label_for' => 'mmtl-bg_repeat',
			'class'     => '',
			'name'      => 'bg_repeat',
			'value'     => '',
			'options'   => array
			(
				''          => '',
				'repeat'    => __( 'repeat', 'table-layout' ),
				'repeat-x'  => __( 'repeat-x', 'table-layout' ),
				'repeat-y'  => __( 'repeat-y', 'table-layout' ),
				'no-repeat' => __( 'no-repeat', 'table-layout' )
			)
		));

		MMTL_API::register_setting( 'common', 'bg_position' );
		MMTL_API::add_settings_field( 'bg_position', __( 'Position', 'table-layout' ), array( 'MMTL_Form', 'dropdown' ), 'common', 'background', array
		(
			'id'        => 'mmtl-bg_position',
			'label_for' => 'mmtl-bg_position',
			'class'     => '',
			'name'      => 'bg_position',
			'value'     => '',
			'options'   => array
			(
				''              => '',
				'left top'      => __( 'left top', 'table-layout' ),
				'left center'   => __( 'left center', 'table-layout' ),
				'left bottom'   => __( 'left bottom', 'table-layout' ),
				'right top'     => __( 'right top', 'table-layout' ),
				'right center'  => __( 'right center', 'table-layout' ),
				'right bottom'  => __( 'right bottom', 'table-layout' ),
				'center top'    => __( 'center top', 'table-layout' ),
				'center center' => __( 'center center', 'table-layout' ),
				'center bottom' => __( 'center bottom', 'table-layout' )
			)
		));

		MMTL_API::register_setting( 'common', 'bg_size' );
		MMTL_API::add_settings_field( 'bg_size', __( 'Size', 'table-layout' ), array( 'MMTL_Form', 'dropdown' ), 'common', 'background', array
		(
			'id'        => 'mmtl-bg_size',
			'label_for' => 'mmtl-bg_size',
			'class'     => '',
			'name'      => 'bg_size',
			'value'     => '',
			'options'   => array
			(
				''        => '',
				'cover'   => __( 'cover', 'table-layout' ),
				'contain' => __( 'contain', 'table-layout' )
			)
		));
	}

	public function add_common_controls( $component )
	{
		if ( ! in_array( 'edit', $component['controls'] ) )
		{
			$component['controls'][] = 'edit';
		}

		if ( ! in_array( 'copy', $component['controls'] ) )
		{
			$component['controls'][] = 'copy';
		}

		if ( ! in_array( 'delete', $component['controls'] ) )
		{
			$component['controls'][] = 'delete';
		}

		return $component;
	}

	public function enqueue_scripts()
	{
		if ( ! MMTL_Common::is_shortcode_used( 'mmtl-row' ) )
		{
			return;
		}

		wp_enqueue_style( 'table-layout' );
	}

	public function the_content( $the_content )
 	{
 		if ( has_shortcode( $the_content, 'mmtl-row' ) )
 		{
 			$the_content = sprintf( '<div class="mmtl-wrap">%s</div>', $the_content );
 		}

 		return $the_content;
 	}
}

MMTL_Shortcodes::get_instance()->init();

?>