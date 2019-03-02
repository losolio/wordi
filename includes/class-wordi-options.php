<?php
class Wordi_Options {

	public function __construct() {
		// Hook into the admin menu
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );

		// Add Settings and Fields
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );

		// Set up defaults
		$this->set_default_settings();
	}

	public function create_plugin_settings_page() {
		$parent_slug = 'edit.php?post_type=wordi';
		$page_title  = 'Wordi configurations';
		$menu_title  = 'Configuration';
		$capability  = 'manage_options';
		$slug        = 'wordi_options';
		$callback    = array( $this, 'plugin_settings_page_content' );

		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $slug, $callback );
	}

	public function plugin_settings_page_content() {?>
		<div class="wrap">
			<h2>Wordi options page</h2>
			<?php
			if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
				$this->admin_notice();
			}
			?>
			<form method="POST" action="options.php">
				<?php
					settings_fields( 'wordi_options' );
					do_settings_sections( 'wordi_options' );
					submit_button();
				?>
			</form>
		</div> 
		<?php
	}

	public function admin_notice() {
		?>
		<div class="notice notice-success is-dismissible">
			<p>Your settings have been updated!</p>
		</div>
		<?php
	}

	public function setup_sections() {
		add_settings_section( 'slug_section', 'My First Section Title', array( $this, 'section_callback' ), 'wordi_options' );
		add_settings_section( 'features_section', 'My Second Section Title', array( $this, 'section_callback' ), 'wordi_options' );
	}

	public function section_callback( $arguments ) {
		switch ( $arguments['id'] ) {
			case 'slug_section':
				echo 'This is the first description here!';
				break;
			case 'features_section':
				echo 'This one is number two';
				break;
		}
	}

	public function setup_fields() {
		$fields = array(
			array(
				'uid'          => 'wordi_slug',
				'label'        => 'Slug (url for words)',
				'section'      => 'slug_section',
				'type'         => 'text',
				'placeholder'  => 'Some text',
				'supplimental' => 'Changing the url may break links to old pages',
			),
			array(
				'uid'     => 'wordi_features',
				'label'   => 'Features enabled',
				'section' => 'features_section',
				'type'    => 'checkbox',
				'options' => array(
					'option1' => 'Option 1',
					'option2' => 'Option 2',
					'option3' => 'Option 3',
					'option4' => 'Option 4',
					'option5' => 'Option 5',
				),
				'default' => array(),
			),
		);
		foreach ( $fields as $field ) {

			add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'wordi_options', $field['section'], $field );
			register_setting( 'wordi_options', $field['uid'] );
		}
	}

	public function set_default_settings() {
		$wordi_slug = get_option( 'wordi_slug' );
		if ( false === $wordi_slug ) { // Nothing yet saved
			update_option( 'wordi_slug', 'words' );
		}
	}

	public function field_callback( $arguments ) {

		$value = get_option( $arguments['uid'] );

		if ( ! $value ) {
			$value = $arguments['default'];
		}

		switch ( $arguments['type'] ) {
			case 'text':
			case 'password':
			case 'number':
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
				break;
			case 'textarea':
				printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
				break;
			case 'select':
			case 'multiselect':
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$attributes     = '';
					$options_markup = '';
					foreach ( $arguments['options'] as $key => $label ) {
						$options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
					}
					if ( $arguments['type'] === 'multiselect' ) {
						$attributes = ' multiple="multiple" ';
					}
					printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
				}
				break;
			case 'radio':
			case 'checkbox':
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					$iterator       = 0;
					foreach ( $arguments['options'] as $key => $label ) {
						$iterator++;
						$options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator );
					}
					printf( '<fieldset>%s</fieldset>', $options_markup );
				}
				break;
		}

		if ( $helper = $arguments['helper'] ) {
			printf( '<span class="helper"> %s</span>', $helper );
		}

		if ( $supplimental = $arguments['supplimental'] ) {
			printf( '<p class="description">%s</p>', $supplimental );
		}

	}

}
