<?php
/*
Plugin Name: rpi Learnpath Block
Plugin URI: https://github.com/johappel/rpicloud
Description: Learnpath Block for individuell learnobj
Author: Joachim Happel
Version: 1.3
Author URI: https://github.com/johappel
*/

define( 'LZB_PATH', WP_PLUGIN_DIR . '/lazy-blocks/' );

if(!file_exists(LZB_PATH . 'lazy-blocks.php')){

	function dependency_error() {
		$class = 'notice notice-error is-dismissible';
		//$message = __( 'rpicloud Error! The Gutenberg block "Nextcloud folder" requires the plugin Custom Blocks Constructor - Lazy Blocks. Please install it now. Activation is not necessary!', 'rpicloud' );
		$message = __( 'rpicloud Fehler! Für den Gutenberg Block "Nextcloud Ordner" wird das Plugin Custom Blocks Constructor - Lazy Blocks benötigt. Bitte installiere es jetzt. Aktivieren ist nicht notwendig!', 'rpicloud' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
	add_action( 'admin_notices', 'dependency_error' );

	return;
}

Class RPI_Learnpath{

	static $plugin_dir;
	static $plugin_url;

	static function editor_output($output, $attributes){

		require_once "draw-svg.php";
		$lp = new learnpath($attributes, true);

		ob_start();

		echo '<div style="position: relative;">';

		$lp->draw_svg(true);

		echo '</div>';


		return ob_get_clean();
	}
	static function frontend_output($output, $attributes){

		require_once "draw-svg.php";
		$lp = new learnpath($attributes);

		ob_start();
		echo '<div style="position: relative;">';
		$lp->draw_svg();
		$lp->draw_infobox();
		$lp->draw_scripts();
		echo '</div>';

		return ob_get_clean();

	}
	static function init(){

		self::$plugin_dir = dirname(__FILE__);
		self::$plugin_url = plugin_dir_url(__FILE__);

		wp_enqueue_script('learnpathjs', plugin_dir_url(__FILE__) .  'js/learnpath.js',array(),null,false);


		if ( function_exists( 'lazyblocks' ) ) :

			add_filter( 'lazyblock/lernstrasse/editor_callback', array('RPI_Learnpath','editor_output'), 10, 2 );
			add_filter( 'lazyblock/lernstrasse/frontend_callback', array('RPI_Learnpath','frontend_output'), 10, 2 );


			lazyblocks()->add_block( array(
				'id' => 151,
				'title' => 'Lernstraße',
				'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				    <rect opacity="0.25" width="15" height="15" rx="4" transform="matrix(-1 0 0 1 22 7)" fill="currentColor" />
				    <rect width="15" height="15" rx="4" transform="matrix(-1 0 0 1 17 2)" fill="currentColor" />
				    </svg>
				    ',
				'keywords' => array(
				),
				'slug' => 'lazyblock/lernstrasse',
				'description' => '',
				'category' => 'text',
				'category_label' => 'text',
				'supports' => array(
					'customClassName' => true,
					'anchor' => false,
					'align' => array(
						0 => 'wide',
						1 => 'full',
					),
					'html' => false,
					'multiple' => true,
					'inserter' => true,
				),
				'ghostkit' => array(
					'supports' => array(
						'spacings' => false,
						'display' => false,
						'scrollReveal' => false,
						'frame' => false,
						'customCSS' => false,
					),
				),
				'controls' => array(
					'control_46894540da' => array(
						'type' => 'text',
						'name' => 'title',
						'default' => '',
						'label' => 'Titel',
						'help' => '',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_c6da8a4c35' => array(
						'type' => 'repeater',
						'name' => 'resources',
						'default' => '',
						'label' => 'Lernstationen',
						'help' => '',
						'child_of' => '',
						'placement' => 'content',
						'width' => '100',
						'hide_if_not_selected' => 'true',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'true',
						'rows_min' => '1',
						'rows_max' => '',
						'rows_label' => '{{label}}',
						'rows_add_button_label' => 'Station hinzufügen',
						'rows_collapsible' => 'true',
						'rows_collapsed' => 'true',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_940865499b' => array(
						'type' => 'image',
						'name' => 'image',
						'default' => '',
						'label' => 'Bild',
						'help' => '',
						'child_of' => 'control_c6da8a4c35',
						'placement' => 'content',
						'style' => 'display:none',
						'width' => '30',
						'hide_if_not_selected' => 'true',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'preview_size' => 'thumbnail',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_c2a8b34607' => array(
						'type' => 'text',
						'name' => 'label',
						'default' => '',
						'label' => 'Name der Station',
						'help' => '',
						'child_of' => 'control_c6da8a4c35',
						'placement' => 'content',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'true',
						'placeholder' => 'Name der Lernstation',
						'characters_limit' => '',
					),

					'control_f01ad14de8' => array(
						'type' => 'rich_text',
						'name' => 'info',
						'default' => '',
						'label' => 'Infos',
						'help' => '',
						'child_of' => 'control_c6da8a4c35',
						'placement' => 'content',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'multiline' => 'true',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_2feb0245d8' => array(
						'type' => 'text',
						'name' => 'check',
						'default' => '',
						'label' => 'Lösungswort',
						'help' => '',
						'child_of' => 'control_c6da8a4c35',
						'placement' => 'content',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'placeholder' => 'Lösungswort',
						'characters_limit' => '20',
						'checked' => 'false',
					),
					/*
					'control_85c8004a9c' => array(
						'type' => 'url',
						'name' => 'url',
						'default' => '',
						'label' => 'Url',
						'help' => '',
						'child_of' => 'control_c6da8a4c35',
						'placement' => 'content',
						'width' => '100',
						'hide_if_not_selected' => 'true',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'placeholder' => '',
						'characters_limit' => '',
					),*/

					'control_8878d64627' => array(
						'type' => 'image',
						'name' => 'svgbackground',
						'default' => '',
						'label' => 'Hintergrundbild (Lernweg)',
						'help' => '',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'true',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'preview_size' => 'full',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_d9081d4a21' => array(
						'type' => 'range',
						'name' => 'radius',
						'default' => '60',
						'label' => 'Stationsradius',
						'help' => 'Radius der Kreise auf dem Lernpfad',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'min' => '35',
						'max' => '95',
						'step' => '5',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_574a0144cc' => array(
						'type' => 'toggle',
						'name' => 'strictcode',
						'default' => '',
						'label' => 'Lösungswort',
						'help' => 'Bei Aktivierung kann der Lernpfad erst weiter bearbeitet werden, wenn ein Lösungswort gesetzt wird.',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'checked' => 'false',
						'alongside_text' => 'Fortschreiten verhindern auch, wenn kein Lösungswort hinterlegt wurde.',
						'placeholder' => '',
						'characters_limit' => '',
						'choices' => array(
							array(
								'label' => '',
								'value' => 'Weiterarbeit nicht möglich',
							),
						),
					),

					'control_faa91f4564' => array(
						'type' => 'color',
						'name' => 'bg_color',
						'default' => '',
						'label' => 'Overlay Farbe',
						'help' => '',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'alpha' => 'true',
						'placeholder' => '',
						'characters_limit' => '',
					),

				),
				'code' => array(
					'output_method' => 'php',
					'editor_html' => '',
					'editor_callback' => '',
					'editor_css' => '',
					'frontend_html' => '',
					'frontend_callback' => '',
					'frontend_css' => '',
					'show_preview' => 'always',
					'single_output' => true,
				),
				'condition' => array(
				),
			) );

		endif;

	}

}
// Include the LZB plugin.
require_once LZB_PATH . 'lazy-blocks.php';

add_action('init',array('RPI_Learnpath','init'));
function admin_style() {
	wp_enqueue_style('admin-styles', plugin_dir_url(__FILE__) .  'css/admin.css');
}
add_action('admin_enqueue_scripts', 'admin_style');
