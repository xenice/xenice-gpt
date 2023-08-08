<?php

/**
 * Plugin Name: Xenice GPT
 * Plugin URI: https://www.xenice.com/plugins/xenice-gpt
 * Description: ChatGPT Plugin
 * Version: 1.0.4
 * Author: Xenice
 * Author URI: https://www.xenice.com
 * Text Domain: xenice-gpt
 * Domain Path: /languages
 */

namespace xenice\gpt;

 /**
 * autoload class
 */
function __autoload($classname){
    $classname = str_replace('\\','/',$classname);
    $namespace = 'xenice/gpt';
    if(strpos($classname, $namespace) === 0){
        $filename = str_replace($namespace, '', $classname);
        require  __DIR__ .  $filename . '.php';
    }
}

 /**
 * get option
 */
function get($name, $key='xenice_gpt')
{
    
    static $option = [];
    if(!$option){
        $options = get_option($key)?:[];
        foreach($options as $o){
            $option = array_merge($option, $o);
        }
    }
    return $option[$name]??'';
}


 /**
 * set option
 */
function set($name, $value, $key='xenice_gpt')
{
    $options = get_option($key)?:[];
    foreach($options as $id=>&$o){
        if(isset($o[$name])){
            $o[$name] = $value;
            update_option($key, $options);
            return;
        }
    }
}

function scripts()
{

}

function admin_menu(){
    add_options_page(__('ChatGPT','xenice-gpt'), __('ChatGPT','xenice-gpt'), 'manage_options', 'xenice-gpt', function(){
        (new Config)->show();
    });
}

/**
* auto execute when active this plugin
*/
register_activation_hook( __FILE__, function(){
    spl_autoload_register('xenice\gpt\__autoload');

    (new Config)->active();
    (new models\Messages)->create();

});

// load page templates

add_action('init', function(){
    add_filter( 'page_template', function($page_template){
        if ( get_page_template_slug() == 'xenice-chatgpt' ) {
    		$page_template = dirname( __FILE__ ) . '/templates/xenice-chatgpt.php';
    	}
    	return $page_template;
    });

    add_filter( 'theme_page_templates', function($post_templates, $wp_theme, $post, $post_type){
        $post_templates['xenice-chatgpt'] = __( 'ChatGPT', 'xenice-gpt' );
    	return $post_templates;
    }, 10, 4 );
});

add_action( 'plugins_loaded', function(){
    !isset($_SESSION) && session_start();
    spl_autoload_register('xenice\gpt\__autoload');
    date_default_timezone_set(get_option('timezone_string'));
    $plugin_name = basename(__DIR__);
    load_plugin_textdomain( $plugin_name, false , $plugin_name . '/languages/' );
    
    // add setting menus
    add_action( 'admin_menu', 'xenice\gpt\admin_menu');
    
    // Add setting button
    $plugin = plugin_basename (__FILE__);
    add_filter("plugin_action_links_$plugin" , function($links)use($plugin_name){
        $settings_link = '<a href="options-general.php?page='.$plugin_name.'">' . __( 'Settings', 'xenice-gpt') . '</a>' ;
        array_push($links , $settings_link);
        return $links;
    });
    
    add_action( 'wp_enqueue_scripts', 'xenice\gpt\scripts');
    new ajax\ChatAjax;
    new Shortcode;
});


