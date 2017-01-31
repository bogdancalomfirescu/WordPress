<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

define( 'MMTL_POST_EDITOR_ID', 'content' );

class MMTL_Editor
{
	private static $instance = null;

	protected $components = array();

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
		add_action( 'edit_form_after_title', array( $this, 'print_activation_buttons' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'print_scripts' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 99, 9 );
		add_action( 'admin_init', array( $this, 'register_components' ), 15 );
	}

	public function get_post_id()
	{
		if ( ! is_admin() )
		{
			return false;
		}

		if ( empty( $GLOBALS['post'] ) )
		{
			return false;
		}

		global $post;

		return $post->ID;
	}

	public function get_components()
	{
		return $this->components;
	}

	public function get_component( $component_id )
	{
		if ( empty( $this->components[ $component_id ] ) )
		{
			return null;
		}

		return $this->components[ $component_id ];
	}

	public function get_components_screen( $component_id )
	{
		$component = $this->get_component( $component_id );

		$filtered = array();

		if ( $component && ! empty( $component['accepts'] ) )
		{
			$components = $this->get_components();

			$filtered = array_intersect_key( $components, (array) array_flip( $component['accepts'] ) );

			if ( isset( $filtered[ $component['id'] ] ) )
			{
				unset( $filtered[ $component['id'] ] );
			}
		}

		$data = array
		(
			'components' => $filtered
		);

		return MMTL_Common::load_template( 'component-picklist', $data, true );
	}

	public function set_active_state( $active, $post_id )
	{
		if ( $active )
		{
			update_post_meta( $post_id, 'mmtl_active', true );
		}

		else
		{
			delete_post_meta( $post_id, 'mmtl_active' );
		}
	}

	public function is_editor_active( $post_id = 0 )
	{
		if ( ! $post_id && $this->is_editor_screen() && ! empty( $_GET['post'] ) )
		{
			$post_id = $_GET['post'];
		}

		return get_post_meta( $post_id, 'mmtl_active', true ) ? true : false;
	}

	public function is_editor_screen()
	{
		if ( ! is_admin() )
		{
			return false;
		}

		// checks if current page is single post edit screen

		$pages = array( 'post.php', 'post-new.php' );

		if ( ! is_array( $pages ) )
		{
			return false;
		}

		if ( empty( $GLOBALS['pagenow'] ) || ! in_array( $GLOBALS['pagenow'], $pages ) )
		{
			return false;
		}

		// gets post type

		if ( empty( $GLOBALS['typenow'] ) )
		{
			return false;
		}

		$post_type = $GLOBALS['typenow'];
			
		// checks if post type supports editor

		return post_type_supports( $post_type, 'editor' );
	}

	public function register_components()
	{
		$components = apply_filters( 'mmtl_editor_components', array() );

		foreach ( $components as $id => $component )
		{
			$component = array_merge( array
			(
				'id'            => $id,
				'title'         => __( 'Untitled', 'table-layout' ),
				'icon'          => '<span class="glyphicons glyphicons-cogwheel"></span>',
				'description'   => '',
				'controls'      => array(),
				'accepts'       => false
			), $component );

			$component = apply_filters( 'mmtl_component', $component );

			$this->components[ $component['id'] ] = $component;
		}
	}

	public function admin_body_class( $classes )
	{
		if ( $this->is_editor_screen() )
		{
			$classes .= 'mmtl';

			if ( ! empty( $_GET['post'] ) )
			{
				$post_id = $_GET['post'];
			}

			else
			{
				$post_id = 0;
			}

			if ( $this->is_editor_active( $post_id ) )
			{
				$classes .= ' mmtl-active';
			}

			else
			{
				$classes .= ' mmtl-inactive';
			}

			if ( MMTL_API::is_debug_active() )
			{
				$classes .= ' mmtl-debug';
			}
		}

		return $classes;
	}

	public function tiny_mce_before_init( $settings, $editor_id )
	{
		// makes sure auto paragraphs are disabled

		if ( $this->is_editor_screen() && $editor_id == MMTL_POST_EDITOR_ID )
		{
			$settings[ 'wpautop' ] = false;
		}

		return $settings;
	}

	public function print_activation_buttons()
	{
		if ( ! $this->is_editor_screen() )
		{
			return;
		}

		?>

		<p>
			<a href="#" class="button mmtl-activate"><?php esc_html_e( 'Table Layout Editor', 'table-layout' ); ?></a>
			<a href="#" class="button mmtl-deactivate"><?php esc_html_e( 'Default Editor', 'table-layout' ); ?></a>
		</p>

		<?php
	}

	public function print_scripts()
	{
		if ( ! $this->is_editor_screen() )
		{
			return;
		}

		?>

		<script type="text/html" id="tmpl-mmtl-main">
			<div class="mmtl-header">
				<h2 class="mmtl-title"><?php _e( 'Table Layout', 'table-layout' ); ?></h2>
				<?php echo MMTL_Common::ajax_loader(); ?>
				<# if ( data.controls ) { #><div class="mmtl-controls mmtl-header-controls"><ul>{{{ data.controls }}}</ul></div><# } #>
			</div>
			<div class="mmtl-content"></div>
			<div class="mmtl-footer">
				<a class="mmtl-add-component-button dashicons-before dashicons-plus" title="<?php esc_attr_e( 'Add Row', 'table-layout' ); ?>" data-type="mmtl-row" href="#"><?php esc_html_e( 'Add Row', 'table-layout' ); ?></a>
			</div>
		</script>

		<script type="text/html" id="tmpl-mmtl-component">
			<div id="mmtl-component-{{ data.id }}" class="mmtl-component mmtl-component-{{ data.type }}" data-type="{{ data.tag }}" data-id="{{ data.id }}"{{{ data.attrs }}}>
				<div class="mmtl-component-inner">
					<div class="mmtl-component-header">
						<# if ( data.controls ) { #><div class="mmtl-controls mmtl-component-controls"><ul>{{{ data.controls }}}</ul></div><# } #>
						<# if ( data.meta ) { #><div class="mmtl-component-meta">{{{ data.meta }}}</div><# } #>
					</div>
					<div class="mmtl-component-content">{{{ data.content }}}</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="tmpl-mmtl-control">
			<li class="mmtl-control-{{ data.id }}-wrap"><a href="#" class="mmtl-control mmtl-control-{{ data.id }} dashicons-before dashicons-{{ data.icon }}" title="{{ data.title }}" data-type="{{ data.id }}"><span class="screen-reader-text">{{{ data.text }}}</span></a></li> 
		</script>

		<script type="text/html" id="tmpl-mmtl-component-source-edit">

			<div class="mmtl-screen">

				<div class="mmtl-screen-header">
					<h2><?php _e( 'Source Code', 'table-layout' ); ?></h2>
				</div>

				<div class="mmtl-screen-content">

					<form method="post">

						<?php MMTL_Common::notice( __( 'Invalid code', 'table-layout' ), 'type=error&class=mmtl-hide' ); ?>

						<div class="mmtl-field">
							<textarea id="mmtl-source" class="large-text" name="source" cols="60" rows="10">{{ data.source }}</textarea>
							<p class="description"><?php _e( "Edit or paste this code into another component's source. (Only code of a component with the same type can be used.)" ); ?></p>
						</div>

						<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

					</form>

				</div>

			</div>

		</script>

		<script type="text/html" id="tmpl-mmtl-meta">
			<span class="mmtl-meta" title="{{ data.title }}" data-type="{{ data.type }}">{{{ data.text }}}</span> 
		</script>

		<?php
	}

	public function enqueue_scripts()
	{
		if ( ! $this->is_editor_screen() )
		{
			return;
		}

		// Styles

		wp_enqueue_style( 'glyphicons' );
		wp_enqueue_style( 'font-awesome' );
		wp_enqueue_style( 'jquery-ui-structure' );

		wp_enqueue_style( 'table-layout' );
		wp_enqueue_style( 'table-layout-admin' );
		wp_enqueue_style( 'table-layout-editor', plugins_url( 'css/editor.min.css', MMTL_FILE ) );
		wp_enqueue_style( 'table-layout-editor-style', plugins_url( 'css/editor-style.min.css', MMTL_FILE ) );
		
		// Scripts

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'featherlight', plugins_url( 'js/featherlight.min.js', MMTL_FILE ), array( 'jquery' ), '1.3.4', true );
		wp_enqueue_script( 'jquery-sticky-kit', plugins_url( 'js/jquery.sticky-kit.min.js', MMTL_FILE ), array( 'jquery' ), '1.1.2', true );

		wp_enqueue_script( 'table-layout-editor', plugins_url( 'js/editor.min.js', MMTL_FILE ), null, false, true );
		wp_enqueue_script( 'table-layout-editor-common', plugins_url( 'js/editor-common.min.js', MMTL_FILE ), null, false, true );
		
		$post_id = $this->get_post_id();

		$options = apply_filters( 'mmtl_editor_options', array
		(
			'post_id'                  => $post_id,
			'post_editor_id'           => MMTL_POST_EDITOR_ID,
			'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
			'noncename'                => MMTL_NONCE_NAME,
			'nonce'                    => wp_create_nonce( 'editor' ),
			'confirm_delete'           => __( 'Are you sure you want to delete this component?', 'table-layout' ),
			'control_label_add'        => __( 'Add', 'table-layout' ),
			'control_label_edit'       => __( 'Edit', 'table-layout' ),
			'control_label_copy'       => __( 'Copy', 'table-layout' ),
			'control_label_delete'     => __( 'Delete', 'table-layout' ),
			'control_label_toggle'     => __( 'Toggle', 'table-layout' ),
			'control_label_fullscreen' => __( 'Toggle full screen', 'table-layout' ),
			'control_label_col_width'  => __( 'Width', 'table-layout' ),
			'control_label_col_increase_width' => __( 'Increase width', 'table-layout' ),
			'control_label_col_decrease_width' => __( 'Decrease width', 'table-layout' ),
			'control_label_source'     => __( 'source Code', 'table-layout' ),
			'meta_title_id'            => __( 'ID', 'table-layout' ),
			'meta_title_class'         => __( 'Class', 'table-layout' ),
			'meta_title_bg_image'      => __( 'Background image', 'table-layout' ),
			'meta_title_push'          => __( 'Push', 'table-layout' ),
			'meta_title_pull'          => __( 'Pull', 'table-layout' ),
			'components'               => $this->get_components(),
			'debug'                    => MMTL_API::is_debug_active()
		));

		wp_localize_script( 'table-layout-editor-common', 'MMTL_Options', $options );

		remove_editor_styles();
	}
}

MMTL_Editor::get_instance()->init();

?>