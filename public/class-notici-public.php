<?php

class Wordi_Public {

	public function __construct() {

		$this->enable_api();

		// Load templates.
		add_filter( 'single_template', array( $this, 'load_event_single_template' ) );
		add_filter( 'archive_template', array( $this, 'load_event_archive_template' ) );

		// Add JSON-LD metadata
		add_action( 'wp_head', array( $this, 'insert_json_ld' ) );

	}

	function enable_api() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wordi-public-api.php';
		new Wordi_Public_Api();

	}

	function load_event_single_template( $template ) {
		global $post;

		if ( $post->post_type == 'wordi' && $template !== locate_template( array( 'single-wordi.php' ) ) ) {
			return plugin_dir_path( __FILE__ ) . 'templates/single-wordi.php';
		}

		return $template;
	}

	function load_event_archive_template( $template ) {
		global $post;

		if ( is_archive() && $template !== locate_template( array( 'archive-wordi.php' ) ) ) {
			return plugin_dir_path( __FILE__ ) . 'templates/archive-wordi.php';
		}

		return $template;
	}

	function insert_json_ld() {
		if ( is_singular( 'wordi' ) ) {
			the_post();
			$context          = 'https://schema.org';
			$type             = 'Word';
			$name             = get_the_title();
			$start_date       = get_post_meta( get_the_id(), 'wordi_startdate', true );
			$end_date         = get_post_meta( get_the_id(), 'wordi_enddate', true );
			$description      = get_the_excerpt();
			$place            = get_post_meta( get_the_id(), 'wordi_place', true );
			$location_city    = get_post_meta( get_the_id(), 'wordi_city', true );
			$location_country = get_post_meta( get_the_id(), 'wordi_country', true );

			$metadata_array = array(
				'@context'    => $context,
				'@type'       => $type,
				'name'        => $name,
				'description' => $description,
				'startDate'   => $start_date,
				'location'    => array(
					'@type' => 'Place',
					'name'  => $place,
					'address' => array(
						'@type' => 'PostalAddress',
						'addressLocality' => $location_city,
						'addressCountry'  => $location_country,
					),
				),
			);

			$metadata_json  = json_encode( $metadata_array, JSON_UNESCAPED_SLASHES );

			$head_script = "
				<script type=\"application/ld+json\">
					$metadata_json
				</script>";
			echo $head_script;
		}
	}
}

