<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Settings
{
	private static $instance = null;

	protected $settings = array();
	protected $pages    = array();
	protected $sections = array();
	protected $fields   = array();

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
		add_action( 'wp_ajax_mmtl_sanitize_options', array( $this, 'sanitize_options' ) );
	}

	public function register_setting( $option_group, $option_name, $sanitize_callback = null )
	{
		$setting = array
		(
			'option_group'      => $option_group,
			'option_name'       => $option_name,
			'sanitize_callback' => $sanitize_callback
		);

		$settings = apply_filters( 'mmtl_settings_setting', $setting );

		$this->settings[] = $settings;
	}

	public function add_settings_page( $id, $title, $callback )
	{
		$page = array
		(
			'id'       => $id,
			'title'    => $title,
			'callback' => $callback
		);

		$page = apply_filters( 'mmtl_settings_page', $page );

		$this->pages[ $page['id'] ] = $page;
	}

	public function add_settings_section( $id, $title, $description, $page )
	{
		$section = array
		(
			'id'          => $id,
			'title'       => $title,
			'description' => $description,
			'page'        => $page
		);

		$section = apply_filters( 'mmtl_settings_section', $section );

		$this->sections[] = $section;
	}

	public function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args = null )
	{
		$field = array
		(
			'id'       => $id,
			'title'    => $title,
			'callback' => $callback,
			'page'     => $page,
			'section'  => $section,
			'args'     => $args
		);

		$field = apply_filters( 'mmtl_settings_field', $field );

		$this->fields[] = $field;
	}

	public function do_settings_sections( $page )
	{
		$sections = wp_filter_object_list( $this->sections, array( 'page' => $page ) );

		foreach ( $sections as $section )
		{
			if ( ! empty( $section['title'] ) )
			{
				printf( '<h3>%s</h3>', $section['title'] );
			}

			if ( ! empty( $section['description'] ) )
			{
				echo $section['description'];
			}

			$this->do_settings_fields( $page, $section['id'] );
		}
	}

	public function do_settings_fields( $page, $section )
	{
		$fields = wp_filter_object_list( $this->fields, array( 'page' => $page, 'section' => $section ) );

		foreach ( $fields as $field )
		{
			printf( '<div class="mmtl-field mmtl-field-%s">', esc_attr( $field['id'] ) );

			if ( $field['title'] )
			{
				if ( ! empty( $field['args']['label_for'] ) )
				{
					$label_id = $field['args']['label_for'];
				}

				else
				{
					$label_id = $field['id'];
				}

				printf( '<label for="%s">%s</label><br>', esc_attr( $label_id ), $field['title'] );
			}
			
			// value

			if ( ! empty( $field['args']['name'] ) )
			{
				$field_name = $field['args']['name'];

				// checks for setting

				$settings = wp_filter_object_list( $this->settings, array
				(
					'option_group' => $field['page'],
					'option_name'  => $field['args']['name']
				));

				$setting = reset( $settings );

				if ( $setting && isset( $_POST[ $setting['option_name'] ] ) )
				{
					$field['args']['value'] = $_POST[ $setting['option_name'] ];
				}
			}

			// field

			call_user_func( $field['callback'], $field['args'] );

			// description

			if ( ! empty( $field['args']['description'] ) )
			{
				printf( '<p class="description">%s</p>', $field['args']['description'] );
			}
		
			echo '</div>'; // .mmtl-field
		}
	}

	public function copy_settings_section( $page, $section, $new_page, $field_filter = array() )
	{
		$sections = wp_filter_object_list( $this->sections, array( 'page' => $page, 'id' => $section ) );
	
		$section = reset( $sections );

		if ( ! $section )
		{
			return;
		}

		$this->add_settings_section( $section['id'], $section['title'], $section['description'], $new_page );

		// checks if we need to copy the fields

		if ( ! is_array( $field_filter ) )
		{
			return;
		}

		// copies fields

		$fields = wp_filter_object_list( $this->fields, array( 'page' => $section['page'], 'section' => $section['id'] ) );

		foreach ( $fields as $field )
		{
			if ( ! empty( $field_filter ) && ! in_array( $field['id'], $field_filter ) )
			{
				continue;
			}

			// copies setting

			$settings = wp_filter_object_list( $this->settings, array( 'option_group' => $field['page'], 'option_name' => $field['id'] ) );

			$setting = reset( $settings );

			if ( $setting )
			{
				$this->register_setting( $new_page, $field['id'], $setting['sanitize_callback'] );
			}

			$this->copy_settings_field( $field['page'], $field['section'], $field['id'], $new_page, $field['section'] );
		}
	}

	public function copy_settings_field( $page, $section, $field, $new_page, $new_section )
	{
		$my_fields = wp_filter_object_list( $this->fields, array( 'page' => $page, 'section' => $section, 'id' => $field ), 'and' );

		$my_field = reset( $my_fields );

		if ( ! $my_field )
		{
			return;
		}

		$this->add_settings_field( $my_field['id'], $my_field['title'], $my_field['callback'], $new_page, $new_section, $my_field['args'] );
	}

	static public function settings_fields( $option_group )
	{
		wp_nonce_field( 'editor', MMTL_NONCE_NAME );

		?>
		
		<input type="hidden" name="action" value="mmtl_sanitize_options">
		<input type="hidden" name="_option_group" value="<?php echo esc_attr( $option_group ); ?>">

		<?php
	}

	public function sanitize_options()
	{
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
		{
			return;
		}

		check_admin_referer( 'editor', MMTL_NONCE_NAME );

		$option_group = ! empty( $_POST['_option_group'] ) ? $_POST['_option_group'] : null;

		$options = array();

		if ( $option_group )
		{
			$settings = wp_filter_object_list( $this->settings, array( 'option_group' => $option_group ) );
			
			foreach ( $settings as $setting )
			{
				if ( isset( $_POST[ $setting['option_name'] ] ) )
				{
					$option_value = $_POST[ $setting['option_name'] ];
				}

				else
				{
					$option_value = ''; // for checkboxes
				}

				if ( $setting['sanitize_callback'] )
				{
					if ( is_array( $setting['sanitize_callback'] ) )
					{
						$callbacks = array( $setting['sanitize_callback'] );
					}

					else
					{
						$callbacks = explode( '|', $setting['sanitize_callback'] );
					}

					foreach ( $callbacks as $callback )
					{
						$option_value = call_user_func( $callback, $option_value );
					}

					if ( $option_value === null )
					{
						continue;
					}
				}

				$options[ $setting['option_name'] ] = $option_value;
			}
		}

		$options = apply_filters( 'mmtl_sanitize_options', $options, $option_group );
			
		wp_send_json_success( $options );
	}

	public function get_settings_page( $page )
	{
		$p = $this->pages[ $page ];

		ob_start();
		
		call_user_func( $p['callback'] );

		$content = ob_get_clean();

		$data = array
		(
			'title'   => $p['title'],
			'content' => $content
		);

		$html = MMTL_Common::load_template( 'modal', $data, true );

		return $html;
	}
}

MMTL_Settings::get_instance()->init();

?>