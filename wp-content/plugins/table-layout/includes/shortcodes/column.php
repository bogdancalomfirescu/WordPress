<?php

class MMTL_Column_Shortcode
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

		add_action( 'admin_init', array( $this, 'register_settings' ), 99 );

		add_shortcode( 'mmtl-col', array( $this, 'parse' ) );
	}

	public function register_component( $components )
	{
		$accepts = apply_filters( 'mmtl_editor_column_accepts', array() );

		$components[ 'mmtl-col' ] = array
		(
			'title'       => __( 'Column', 'table-layout' ),
			'description' => __( 'A responsive column and container of other components.', 'table-layout' ),
			'controls'    => array( 'add', 'add_before', /*'col_decrease_width', 'col_increase_width', 'col_width', */ 'edit', 'copy', 'delete', 'add_after', 'toggle', 'source' ),
			'accepts'     => array_keys( $accepts )
		);

		return $components;
	}

	public function register_settings()
	{
		MMTL_API::add_settings_page( 'mmtl-col', __( 'Column Settings', 'table-layout' ), array( $this, 'print_settings_page' ) );

		// responsiveness

		MMTL_API::add_settings_section( 'responsiveness', __( 'Responsiveness', 'table-layout' ), '', 'mmtl-col' );
		
		MMTL_API::add_settings_field( 'width', __( 'Repeat', 'table-layout' ), array( 'MMTL_Form', 'dropdown' ), 'mmtl-col', 'responsiveness', array
		(
			'id'        => 'mmtl-width',
			'label_for' => 'mmtl-width',
			'class'     => '',
			'name'      => 'width',
			'value'     => '',
			'options'   => array
			(
				'1/12'  => __( '1 column - 1/12', 'table-layout' ),
				'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
				'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
				'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
				'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
				'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
				'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
				'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
				'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
				'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
				'11/12' => __( '11 columns - 11/12', 'table-layout' ),
				'1/1'   => __( '12 columns - 1/1', 'table-layout' )
			)
		));

		MMTL_API::add_settings_field( 'responsives-table', '', array( $this, 'print_responsiveness_section' ), 'mmtl-col', 'responsiveness' );

		MMTL_API::register_setting( 'mmtl-col', 'offset_xs' );
		MMTL_API::register_setting( 'mmtl-col', 'offset' );
		MMTL_API::register_setting( 'mmtl-col', 'offset_md' );
		MMTL_API::register_setting( 'mmtl-col', 'offset_lg' );
		MMTL_API::register_setting( 'mmtl-col', 'width_xs' );
		MMTL_API::register_setting( 'mmtl-col', 'width' );
		MMTL_API::register_setting( 'mmtl-col', 'width_md' );
		MMTL_API::register_setting( 'mmtl-col', 'width_lg' );
		MMTL_API::register_setting( 'mmtl-col', 'hide_xs' );
		MMTL_API::register_setting( 'mmtl-col', 'hide' );
		MMTL_API::register_setting( 'mmtl-col', 'hide_md' );
		MMTL_API::register_setting( 'mmtl-col', 'hide_lg' );
		MMTL_API::register_setting( 'mmtl-col', 'push_xs' );
		MMTL_API::register_setting( 'mmtl-col', 'push' );
		MMTL_API::register_setting( 'mmtl-col', 'push_md' );
		MMTL_API::register_setting( 'mmtl-col', 'push_lg' );
		MMTL_API::register_setting( 'mmtl-col', 'pull_xs' );
		MMTL_API::register_setting( 'mmtl-col', 'pull' );
		MMTL_API::register_setting( 'mmtl-col', 'pull_md' );
		MMTL_API::register_setting( 'mmtl-col', 'pull_lg' );
		
		MMTL_API::copy_settings_section( 'common', 'background', 'mmtl-col' );
		MMTL_API::copy_settings_section( 'common', 'attributes', 'mmtl-col' );
	}

	public function parse( $atts, $content = '' )
	{
		extract( shortcode_atts( array
		(
			'id'        => '',
			'class'     => '',
			'bg_image'     => '',
			'bg_position'  => '',
			'bg_repeat'    => '',
			'bg_size'      => '',
			'offset_xs' => '',
			'offset'    => '', // sm
			'offset_md' => '',
			'offset_lg' => '',
			'width_xs'  => '',
			'width'     => '', // sm
			'width_md'  => '',
			'width_lg'  => '',
			'hide_xs'   => '',
			'hide'      => '', // sm
			'hide_md'   => '',
			'hide_lg'   => '',
			'push_xs'   => '',
			'push'      => '', // sm
			'push_md'   => '',
			'push_lg'   => '',
			'pull_xs'   => '',
			'pull'      => '', // sm
			'pull_md'   => '',
			'pull_lg'   => ''
		), $atts ) );

		// removes unwantend paragraphs
		//'</p><p>some text</p><p>'

		$content = preg_replace( '/^\s*<\/p>/si' , '', $content );
 		$content = preg_replace( '/<p>\s*$/si' , '', $content );

		// classes
		
		$classes = MMTL_Common::html_class_to_array( $class );

		$my_classes = array( 'mmtl-col' );

		if ( $bg_image ) $my_classes[] = 'mmtl-has-bgimage';
		if ( $bg_image ) $my_classes[] = 'mmtl-has-overlay';

		if ( $offset_xs ) $my_classes[] = 'mmtl-col-xs-offset-' . MMTL_Common::get_column_span( $offset_xs );
		if ( $offset )    $my_classes[] = 'mmtl-col-sm-offset-' . MMTL_Common::get_column_span( $offset );
		if ( $offset_md ) $my_classes[] = 'mmtl-col-md-offset-' . MMTL_Common::get_column_span( $offset_md );
		if ( $offset_lg ) $my_classes[] = 'mmtl-col-lg-offset-' . MMTL_Common::get_column_span( $offset_lg );

		if ( $width_xs ) $my_classes[] = 'mmtl-col-xs-' . MMTL_Common::get_column_span( $width_xs );
		if ( $width )    $my_classes[] = 'mmtl-col-sm-' . MMTL_Common::get_column_span( $width );
		if ( $width_md ) $my_classes[] = 'mmtl-col-md-' . MMTL_Common::get_column_span( $width_md );
		if ( $width_lg ) $my_classes[] = 'mmtl-col-lg-' . MMTL_Common::get_column_span( $width_lg );

		if ( $hide_xs ) $my_classes[] = 'mmtl-hidden-xs';
		if ( $hide )    $my_classes[] = 'mmtl-hidden-sm';
		if ( $hide_md ) $my_classes[] = 'mmtl-hidden-md';
		if ( $hide_lg ) $my_classes[] = 'mmtl-hidden-lg';

		if ( $push_xs ) $my_classes[] = 'mmtl-col-xs-push-' . MMTL_Common::get_column_span( $push_xs );
		if ( $push )    $my_classes[] = 'mmtl-col-sm-push-' . MMTL_Common::get_column_span( $push );
		if ( $push_md ) $my_classes[] = 'mmtl-col-md-push-' . MMTL_Common::get_column_span( $push_md );
		if ( $push_lg ) $my_classes[] = 'mmtl-col-lg-push-' . MMTL_Common::get_column_span( $push_lg );

		if ( $pull_xs ) $my_classes[] = 'mmtl-col-xs-pull-' . MMTL_Common::get_column_span( $pull_xs );
		if ( $pull )    $my_classes[] = 'mmtl-col-sm-pull-' . MMTL_Common::get_column_span( $pull );
		if ( $pull_md ) $my_classes[] = 'mmtl-col-md-pull-' . MMTL_Common::get_column_span( $pull_md );
		if ( $pull_lg ) $my_classes[] = 'mmtl-col-lg-pull-' . MMTL_Common::get_column_span( $pull_lg );

		$classes = array_merge( $my_classes, $classes );
		$classes = array_unique( $classes );

		$class = implode( ' ', $classes );

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
			'class' => $class,
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

	public function print_responsiveness_section()
	{
		$args = array_merge( array
		(
			'offset_xs'   => '',
			'offset'      => '', // sm
			'offset_md'   => '',
			'offset_lg'   => '',
			'width_xs'    => '',
			'width'       => '', // sm
			'width_md'    => '',
			'width_lg'    => '',
			'hide_xs'     => '',
			'hide'        => '', // sm
			'hide_md'     => '',
			'hide_lg'     => '',
			'push_xs'     => '',
			'push'        => '', // sm
			'push_md'     => '',
			'push_lg'     => '',
			'pull_xs'     => '',
			'pull'        => '', // sm
			'pull_md'     => '',
			'pull_lg'     => ''
		), $_POST );

		extract( $args );

		?>

		<table class="mmtl-responsive-table">

			<tr>
				<th><?php _e( 'Device', 'table-layout' ); ?></th>
				<th><?php _e( 'Offset', 'table-layout' ); ?></th>
				<th><?php _e( 'Width', 'table-layout' ); ?></th>
				<th><?php _e( 'Push', 'table-layout' ); ?></th>
				<th><?php _e( 'Pull', 'table-layout' ); ?></th>
				<th><?php _e( 'Hide', 'table-layout' ); ?></th>
			</tr>

			<tr>
				<td><i class="fa fa-desktop fa-2x" title="<?php esc_attr_e( 'Large desktop', 'table-layout' ); ?>"></i><span class="screen-reader-text"><?php _e( 'Large desktop', 'table-layout' ); ?></span></td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'offset_lg',
						'name'    => 'offset_lg',
						'value'   => $offset_lg,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'width_lg',
						'name'    => 'width_lg',
						'value'   => $width_lg,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'push_lg',
						'name'    => 'push_lg',
						'value'   => $push_lg,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'pull_lg',
						'name'    => 'pull_lg',
						'value'   => $pull_lg,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<input type="checkbox" name="hide_lg" value="1"<?php checked( $hide_lg, '1' ); ?>>
				</td>
			</tr>

			<tr>
				<td><i class="fa fa-desktop fa-lg" title="<?php esc_attr_e( 'Desktop', 'table-layout' ); ?>"></i><span class="screen-reader-text"><?php _e( 'Desktop', 'table-layout' ); ?></span></td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'offset_md',
						'name'    => 'offset_md',
						'value'   => $offset_md,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'width_md',
						'name'    => 'width_md',
						'value'   => $width_md,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'push_md',
						'name'    => 'push_md',
						'value'   => $push_md,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'pull_md',
						'name'    => 'pull_md',
						'value'   => $pull_md,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<input type="checkbox" name="hide_md" value="1"<?php checked( $hide_md, '1' ); ?>>
				</td>
			</tr>

			<tr>
				<td><i class="fa fa-tablet fa-lg" title="<?php esc_attr_e( 'Tablet', 'table-layout' ); ?>"></i><span class="screen-reader-text"><?php _e( 'Tablet', 'table-layout' ); ?></span></td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'offset',
						'name'    => 'offset',
						'value'   => $offset,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td><p class="description"><?php _e( 'Value from width attribute.', 'table-layout' ); ?></p></td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'push',
						'name'    => 'push',
						'value'   => $push,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'pull',
						'name'    => 'pull',
						'value'   => $pull,
						'options' => array
						(
							''      => __( 'Inherit from smaller', 'table-layout' ),
							'0'     => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<input type="checkbox" name="hide" value="1"<?php checked( $hide, '1' ); ?>>
				</td>
			</tr>

			<tr>
				<td><i class="fa fa-mobile fa-lg" title="<?php esc_attr_e( 'Phone', 'table-layout' ); ?>"></i><span class="screen-reader-text"><?php _e( 'Phone', 'table-layout' ); ?></span></td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'offset_xs',
						'name'    => 'offset_xs',
						'value'   => $offset_xs,
						'options' => array
						(
							''      => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'width_xs',
						'name'    => 'width_xs',
						'value'   => $width_xs,
						'options' => array
						(
							''      => '',
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'push_xs',
						'name'    => 'push_xs',
						'value'   => $push_xs,
						'options' => array
						(
							''      => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<?php MMTL_Form::dropdown( array
					(
						'id'      => 'pull_xs',
						'name'    => 'pull_xs',
						'value'   => $pull_xs,
						'options' => array
						(
							''      => __( 'none', 'table-layout' ),
							'1/12'  => __( '1 column - 1/12', 'table-layout' ),
							'1/6'   => __( '2 columns - 1/6', 'table-layout' ),
							'1/4'   => __( '3 columns - 1/4', 'table-layout' ),
							'1/3'   => __( '4 columns - 1/3', 'table-layout' ),
							'5/12'  => __( '5 columns - 5/12', 'table-layout' ),
							'1/2'   => __( '6 columns - 1/2', 'table-layout' ),
							'7/12'  => __( '7 columns - 7/12', 'table-layout' ),
							'2/3'   => __( '8 columns - 2/3', 'table-layout' ),
							'3/4'   => __( '9 columns - 3/4', 'table-layout' ),
							'5/6'   => __( '10 columns - 5/6', 'table-layout' ),
							'11/12' => __( '11 columns - 11/12', 'table-layout' ),
							'1/1'   => __( '12 columns - 1/1', 'table-layout' )
						)
					)); ?>
				</td>
				<td>
					<input type="checkbox" name="hide_xs" value="1"<?php checked( $hide_xs, '1' ); ?>>
				</td>
			</tr>

		</table>

		<?php
	}

	public function print_settings_page()
	{
		?>

		<form method="post">

			<?php MMTL_API::settings_fields( 'mmtl-col' ); ?>

			<?php MMTL_API::do_settings_sections( 'mmtl-col' ); ?>

			<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

		</form>

		<?php
	}
}

MMTL_Column_Shortcode::get_instance()->init();

?>