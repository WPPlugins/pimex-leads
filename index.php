<?php
/*
* Plugin Name: Pimex
* Plugin URI: http://app.pimex.co
* Description: Integración de Pimex para su sitio web
* Version: 4.2.1
* Author: Pimex
* Author URI: http://app.pimex.co
* License: ECPT_LICENSE
* Domain Path: /lang
*/

wp_deregister_script('contact-form-7');
wp_enqueue_script( 'contact-form-7', plugin_dir_url( __FILE__ ).'js/cf7_pmx.js', array( 'jquery', 'jquery-form' ), '1.0', true );
wp_enqueue_script( 'scripts', plugin_dir_url( __FILE__ ).'js/scripts.js',	array(), true );
wp_enqueue_script( 'pimex_script', '//services.pimex.co/async.js',	array(), true );
add_action('plugins_loaded', 'lang_pimex');

global $post;


// load languages for plugin
function lang_pimex() {

  $domain = 'pimex';
  $plugin_path = dirname(plugin_basename( __FILE__ ) .'/lang/' );

  load_plugin_textdomain( 'pimex', false, plugin_basename( dirname( __FILE__ ) ) . '/lang/' );
}

add_action( 'admin_init', 'pimex_parent_contactForm' );

// Pimex look if Contact form is active
function pimex_parent_contactForm() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
        add_action( 'admin_notices', 'pimex_parent_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

// If wasn't active show admin notice
function pimex_parent_notice(){

    ?>
    <div class="error">
      <p><?php _e('To use <b>Pimex</b> it is necessary that you have installed and activated the plugin', 'pimex');?>
      <a href="'<?php get_bloginfo('url'); ?> '/wp-admin/plugin-install.php?tab=search&s=contact+form+7">Contact Form 7</a>
    </p>
  </div>
  <?php
}

//load scripts
function load_scripts() {

  wp_enqueue_script( 'pimex_scripts', plugin_dir_url( __FILE__ ) . '/js/scripts.js', array('jquery'),'1.4.0',true  );

}

function pimex_tab_callback(){
	$wpcf = WPCF7_ContactForm::get_current();

	wp_register_style( 'custom_wp_admin_css', plugins_url( '/css/style-pimex.css', __FILE__ ), false, '1.0.0' );
	wp_enqueue_style( 'custom_wp_admin_css' );
  $pmxId = (get_post_meta($wpcf->id, '_pmxId', true)) ? get_post_meta($wpcf->id, '_pmxId', true) : get_option('projectId');
  $pmxToken = (get_post_meta($wpcf->id, '_pmxtoken', true)) ? get_post_meta($wpcf->id, '_pmxtoken', true) : get_option('accessToken');
	?>
		<div class="container-pimex" style="margin-bottom:10px;">
			<div class="logo-pimex">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo_head.png';?>"><br />
				<span>Wordpress Plugin</span>
			</div>
			<p><?php _e('Note: Pimex require that youll replace the names of the fields in the form by: name, email, phone (if you have), and message.', 'pimex');?></p>
				<p><label for="_pimexId">ID</label><br>
				<input type="text" class="pxm-field" name="_pimexId" value="<?= $pmxId; ?>"></p>
				<p><label for="_pimexToken">Token</label><br />
				<input type="text" class="pxm-field" name="_pimexToken" value="<?= $pmxToken; ?>"></p>
			</div>
			<p style="text-align:center">
				<a href="http://app.pimex.co" target="_blank"><?php _e('Go to my Pimex account', 'pimex');?></a>
			</p>

	<?php
}

// define the wpcf7_editor_panels callback
function pimex_tab_cf7( $panels ) {

    $panels['pimex'] = array(
			'title'     => 'Pimex',
			'callback'  => 'pimex_tab_callback'
		);
    return $panels;

};

// add the filter
add_filter( 'wpcf7_editor_panels', 'pimex_tab_cf7', 10, 1 );

/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
function pimex_save_callback( $post_id ) {

	update_post_meta( $post_id, '_pmxId', $_POST['_pimexId'] );
	update_post_meta( $post_id, '_pmxtoken', $_POST['_pimexToken'] );

}

add_action( 'save_post', 'pimex_save_callback' );

// define the wpcf7_form_hidden_fields callback
function pimex_add_hidden( $array ) {

	$wpcf = WPCF7_ContactForm::get_current();
	$pmxId = (get_post_meta($wpcf->id, '_pmxId', true)) ? get_post_meta($wpcf->id, '_pmxId', true) : get_option('projectId');
	$pmxToken = (get_post_meta($wpcf->id, '_pmxtoken', true)) ? get_post_meta($wpcf->id, '_pmxtoken', true) : get_option('accessToken');
	$array = array(
		'_pmxId' => $pmxId,
	 	'_pmxToken' => $pmxToken
	);

  return $array;
};

// add the filter
add_filter( 'wpcf7_form_hidden_fields', 'pimex_add_hidden', 10, 1 );
