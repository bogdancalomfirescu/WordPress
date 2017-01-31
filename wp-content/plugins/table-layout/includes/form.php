<?php

class MMTL_Form
{
	static public function input( $args = '' )
	{
		extract( wp_parse_args( $args, array
		(
			'type'  => '',
			'id'    => '',
			'class' => '',
			'name'  => '',
			'value' => '',
			'extra' => ''
		)));

		printf( '<input%s>', MMTL_Common::parse_html_attributes(array
		(
			'type'  => $type,
			'id'    => $id,
			'class' => $class,
			'name'  => $name,
			'value' => $value
		), $extra ));
	}

	static public function textfield( $args = '' )
	{
		$args = wp_parse_args( $args );

		$args['type'] = 'text';

		self::input( $args );
	}

	static public function checkbox( $args = '' )
	{
		$args = wp_parse_args( $args );

		$args['type'] = 'checkbox';

		$args['extra'] = checked( $args['value'], 1, false );

		$args['value'] = 1;

		self::input( $args );
	}

	static public function radio( $args = '' )
	{
		extract( wp_parse_args( $args, array
		(
			'name'    => '',
			'value'   => '',
			'options' => array()
		)));

		if ( empty( $options ) )
		{
			return;
		}
			
		echo '<ul class="mmtl-radio">';

		foreach ( $options as $option_value => $option_text )
		{
			echo '<li>';

			echo '<label>';

			self::input(array
			(
				'type'  => 'radio',
				'name'  => $name,
				'value' => $option_value,
				'extra' => checked( $option_value, $value, false )
			));

			echo $option_text;

			echo '</label>';

			echo '</li>';
		}

		echo '</ul>';
		
	}

	static public function textarea( $args = '' )
	{
		extract( wp_parse_args( $args, array
		(
			'id'    => '',
			'class' => '',
			'name'  => '',
			'value' => '',
			'extra' => ''
		)));

		printf( '<textarea%s>%s</textarea>', MMTL_Common::parse_html_attributes(array
		(
			'id'    => $id,
			'class' => $class,
			'name'  => $name
		), $extra ), esc_textarea( $value ) );
	}

	static public function options( $options, $selected = '' )
	{
		foreach ( $options as $option_value => $option_text )
		{
			$extra = selected( $option_value, $selected, true, false );

			printf( '<option value="%s"%s>%s</option>', 
				esc_attr( $option_value ), $extra, esc_attr( $option_text ) );
		}
	}
	
	static public function dropdown( $args = '' )
	{
		extract( wp_parse_args( $args, array
		(
			'id'      => '',
			'class'   => '',
			'name'    => '',
			'value'   => '',
			'options' => array(),
			'extra'   => ''
		)));

		printf( '<select%s>', MMTL_Common::parse_html_attributes(array
		(
			'id'    => $id,
			'class' => $class,
			'name'  => $name,
		), $extra ));
		
		self::options( $options, $value );

		echo '</select>';
	}

	static public function editor( $args = '' )
	{
		$args = array_merge( array
		(
			'name'  => '',
			'value' => ''
		), $args );

		$args['textarea_name'] = $args['name'];
		
		unset( $args['name'] );

		$value = $args['value'];

		unset( $args['value'] );

		wp_editor( $value , 'mmtl_content', $args );
	}

	static public function image_picker( $args = '' )
	{
		$args = wp_parse_args( $args, array
		(
			'name'  => '',
			'value' => ''
		));

		echo '<div class="mmtl-media">';

		$args['type']  = 'hidden';
		$args['class'] = 'mmtl-media-field';

		self::input( $args );

		printf( '<a href="#" class="mmtl-media-add dashicons dashicons-plus" title="%s"></a>', __( 'Add', 'table-layout' ) );
		printf( '<a href="#" class="mmtl-media-remove dashicons dashicons-minus" title="%s"></a>', __( 'Remove', 'table-layout' ) );
		
		echo '<img class="mmtl-media-image">';

		echo '</div>';
	}
}

?>