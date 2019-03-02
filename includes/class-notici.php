<?php

class Wordi {

	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version     = NOTICI_VERSION;
		$this->plugin_name = 'wordi';

		$this->options_page();
		$this->register_cpt();
		$this->register_shortcodes();
	}

	private function options_page() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordi-options.php';
		new Wordi_Options();

	}

	private function register_cpt() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordi-register-cpt.php';
		new Wordi_Register_Cpt( $this->plugin_name, $this->plugin_version );

	}

	private function register_shortcodes() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wordi-public.php';
		new Wordi_Public();

	}



}
