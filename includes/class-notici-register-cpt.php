<?php


class Wordi_Register_Cpt {
	private $plugin_name;
	private $version;

	function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Register the custom post type
		add_action( 'init', array( $this, 'wordi_register_cpt' ) );

		// Add event categories
		add_action( 'init', array( $this, 'wordicategory_taxonomy' ), 0 );

		// Change columns in admin
		add_filter( 'manage_wordi_posts_columns', array( $this, 'wordi_edit_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'wordi_custom_columns' ) );

		// Add meta box
		add_action( 'admin_init', array( $this, 'wordi_add_metabox' ) );

		// Styles and scripts
		$this->wordi_styles_and_scripts();

		// Save post
		add_action( 'save_post', array( $this, 'save_wordi' ) );
		add_filter( 'post_updated_messages', array( $this, 'wordi_updated_messages' ) );
	}

	function wordi_register_cpt() {

		$labels = array(
			'name'               => _x( 'Words', 'post type general name' ),
			'singular_name'      => _x( 'Word', 'post type singular name' ),
			'add_new'            => _x( 'Add New', 'words' ),
			'add_new_item'       => __( 'Add New Word' ),
			'edit_item'          => __( 'Edit Word' ),
			'new_item'           => __( 'New Word' ),
			'view_item'          => __( 'View Word' ),
			'search_items'       => __( 'Search Words' ),
			'not_found'          => __( 'No words found' ),
			'not_found_in_trash' => __( 'No words found in Trash' ),
			'parent_item_colon'  => '',
		);

		$args = array(
			'label'             => __( 'Words' ),
			'labels'            => $labels,
			'public'            => true,
			'show_in_rest'      => true,
			'has_archive'       => true,
			'can_export'        => true,
			'show_ui'           => true,
			'_builtin'          => false,
			'capability_type'   => 'post',
			'menu_icon'         => 'dashicons-calendar-alt',
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => get_option( 'wordi_slug' ) ),
			'supports'          => array( 'title', 'thumbnail', 'excerpt', 'editor' ),
			'show_in_nav_menus' => true,
			'taxonomies'        => array( 'wordicategory', 'post_tag' ),
		);

		register_post_type( 'wordi', $args );
		flush_rewrite_rules();

	}

	function wordicategory_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Categories', 'taxonomy general name' ),
			'singular_name'              => _x( 'Category', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Categories' ),
			'popular_items'              => __( 'Popular Categories' ),
			'all_items'                  => __( 'All Categories' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Category' ),
			'update_item'                => __( 'Update Category' ),
			'add_new_item'               => __( 'Add New Category' ),
			'new_item_name'              => __( 'New Category Name' ),
			'separate_items_with_commas' => __( 'Separate categories with commas' ),
			'add_or_remove_items'        => __( 'Add or remove categories' ),
			'choose_from_most_used'      => __( 'Choose from the most used categories' ),
		);

		register_taxonomy(
			'wordicategory',
			'wordi',
			array(
				'label'        => __( 'Word Category' ),
				'labels'       => $labels,
				'hierarchical' => true,
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => 'event-category' ),
			)
		);
	}

	function wordi_edit_columns( $columns ) {

		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'title'           => 'Word',
			'wordi_col_desc' => 'Description',
			'wordi_col_date' => 'Dates',
			'wordi_col_cat'  => 'Category',
		);
		return $columns;
	}

	function wordi_custom_columns( $column ) {
		global $post;
		$custom      = get_post_custom();
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		switch ( $column ) {
			case 'wordi_col_desc':
				the_excerpt();
				break;
			case 'wordi_col_date':
				$meta_startdate = $custom['wordi_startdate'][0];
				$meta_enddate   = $custom['wordi_enddate'][0];
				$meta_starttime = $custom['wordi_starttime'][0];
				$meta_endtime   = $custom['wordi_endtime'][0];

				$formatted_time = date_i18n( $date_format, strtotime( $meta_startdate ) );
				if ( null != $meta_starttime ) {
					$formatted_time .= ': ' . date_i18n( $time_format, strtotime( $meta_starttime ) );
				}

				if ( null != $meta_enddate ) {
					if ( $meta_startdate != $meta_enddate ) {
						$formatted_time .= '<br> &mdash; ' . date_i18n( $date_format, strtotime( $meta_enddate ) );
					} else {
						$formatted_time .= '-';
					}

					if ( null != $meta_endtime ) {
						$formatted_time .= date_i18n( $time_format, strtotime( $meta_endtime ) );
					}
				}

				echo $formatted_time;
				break;
			case 'wordi_col_cat':
				// - show taxonomy terms -
				$eventcats      = get_the_terms( $post->ID, 'wordicategory' );
				$eventcats_html = array();
				if ( $eventcats ) {
					foreach ( $eventcats as $eventcat ) {
						array_push( $eventcats_html, $eventcat->name );
					}
					echo implode( $eventcats_html, ', ' );
				} else {
					_e( 'None', 'wordi' );

				}
				break;

		}
	}

	function wordi_add_metabox() {
		add_meta_box( 'wordi_render_admin_metabox', 'Word time', array( $this, 'wordi_render_admin_metabox' ), 'wordi' );
	}

	function wordi_render_admin_metabox() {

		// Get post meta.
		global $post;
		$custom         = get_post_custom( $post->ID );
		$meta_startdate = $custom['wordi_startdate'][0];
		$meta_enddate   = $custom['wordi_enddate'][0];
		$meta_starttime = $custom['wordi_starttime'][0];
		$meta_endtime   = $custom['wordi_endtime'][0];

		// WP nonce
		echo '<input type="hidden" name="wordi-words-nonce" id="wordi-words-nonce" value="' .
		wp_create_nonce( 'wordi-words-nonce' ) . '" />';
		?>
			<div class="tf-meta">
			<ul>
				<li><label>Start Date</label><input name="wordi_startdate" class="tfdate" value="<?php echo esc_attr( $meta_startdate ); ?>" /><em> YYYY-MM-DD, like 2019-12-31</em></li>
				<li><label>Start Time</label><input name="wordi_starttime" value="<?php echo esc_attr( $meta_starttime ); ?>" /><em> Use 24h format (7pm = 19:00)</em></li>
				<li><label>End Date</label><input name="wordi_enddate" class="tfdate" value="<?php echo esc_attr( $meta_enddate ); ?>" /><em> YYYY-MM-DD, like 2019-12-31</em></li>
				<li><label>End Time</label><input name="wordi_endtime" value="<?php echo esc_attr( $meta_endtime ); ?>" /><em> Use 24h format (7pm = 19:00)</em></li>
			</ul>
			</div>
		<?php
	}

	function wordi_styles_and_scripts() {
		add_action( 'admin_print_styles-post.php', array( $this, 'words_styles' ), 1000 );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'words_styles' ), 1000 );

		add_action( 'admin_print_scripts-post.php', array( $this, 'words_scripts' ), 1000 );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'words_scripts' ), 1000 );
	}


	function words_styles() {
		global $post_type;
		if ( 'wordi' != $post_type ) {
			return;
		}
		wp_enqueue_style( 'jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( $this->plugin_name . '-wordi-admin', plugin_dir_url( __DIR__ ) . 'admin/css/wordi-admin.css', array(), $this->version, 'all' );
	}

	function words_scripts() {
		global $post_type;
		if ( 'wordi' != $post_type ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name . '-wordi-admin', plugin_dir_url( __DIR__ ) . 'admin/js/wordi-admin.js', array( 'jquery', 'jquery-ui-datepicker' ) );
	}



	function save_wordi() {

		global $post;

		// Require nonce
		if ( ! wp_verify_nonce( $_POST['wordi-words-nonce'], 'wordi-words-nonce' ) ) {
			return $post->ID;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		// Start date is mandatory
		if ( ! isset( $_POST['wordi_startdate'] ) ) :
			return $post;
		endif;

		// Update start date.
		$update_startdate = strtotime( sanitize_text_field( $_POST['wordi_startdate'] ) );
		update_post_meta( $post->ID, 'wordi_startdate', date( 'Y-m-d', $update_startdate ) );

		// Update end date if submitted.
		if ( null != $_POST['wordi_enddate'] ) {
			$update_enddate = strtotime( sanitize_text_field( $_POST['wordi_enddate'] ) );
			update_post_meta( $post->ID, 'wordi_enddate', date( 'Y-m-d', $update_enddate ) );
		} else {
			update_post_meta( $post->ID, 'wordi_enddate', null );
		}

		// Update start and end time if matches regex pattern.
		$update_starttime = sanitize_text_field( $_POST['wordi_starttime'] );
		if ( preg_match( '/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $update_starttime ) ) {
			update_post_meta( $post->ID, 'wordi_starttime', $update_starttime );
		} else {
			update_post_meta( $post->ID, 'wordi_starttime', null );
		}

		$update_endtime = sanitize_text_field( $_POST['wordi_endtime'] );
		if ( preg_match( '/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $update_endtime ) ) {
			update_post_meta( $post->ID, 'wordi_endtime', $update_endtime );
		} else {
			update_post_meta( $post->ID, 'wordi_endtime', null );
		}

	}

	function wordi_updated_messages( $messages ) {

		global $post, $post_ID;

		$messages['wordi'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Word updated. <a href="%s">View item</a>' ), esc_url( get_permalink( $post_ID ) ) ),
			2  => __( 'Custom field updated.' ),
			3  => __( 'Custom field deleted.' ),
			4  => __( 'Word updated.' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Word restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Word published. <a href="%s">View event</a>' ), esc_url( get_permalink( $post_ID ) ) ),
			7  => __( 'Word saved.' ),
			8  => sprintf( __( 'Word submitted. <a target="_blank" href="%s">Preview event</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9  => sprintf(
				__( 'Word scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),
			10 => sprintf( __( 'Word draft updated. <a target="_blank" href="%s">Preview event</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

}

