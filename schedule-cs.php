<?php
/*
Plugin Name: Schedule CS
Description: Генерация и просмотр расписания для групп и подгрупп.
Version: 2.2
Author: Неупокоев Дмитрий
*/

require_once plugin_dir_path(__FILE__) . 'admin-menu.php';

function schedulecs_enqueue_custom_styles() {
    wp_enqueue_style(
        'schedulecs-custom-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        [],
        '1.0'
    );
}
add_action('wp_enqueue_scripts', 'schedulecs_enqueue_custom_styles');
function schedulecs_register_assets() {
    wp_register_style(
        'schedulecs-main-style',
        plugin_dir_url(__FILE__) . 'assets/css/schedule-page.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/schedule-page.css')
    );
    
    wp_register_style(
        'schedulecs-admin-style',
        plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/admin-style.css')
    );
    
    wp_register_script(
        'schedulecs-main-js',
        plugin_dir_url(__FILE__) . 'assets/js/schedule-page.js',
        ['jquery'], 
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/schedule-page.js'),
        true 
    );
}
add_action('wp_enqueue_scripts', 'schedulecs_register_assets');
add_action('admin_enqueue_scripts', 'schedulecs_register_assets');

function gs_schedule_selector_shortcode() {
    wp_enqueue_style('schedulecs-main-style');
    wp_enqueue_script('schedulecs-main-js');
    
    ob_start();
    include plugin_dir_path(__FILE__) . 'views/list-page.php';
    return ob_get_clean();
}
add_shortcode('gs_schedule_selector', 'gs_schedule_selector_shortcode');

function gs_schedule_shortcode() {
    wp_enqueue_style('schedulecs-main-style');
    wp_enqueue_script('schedulecs-main-js');
    
    ob_start();
    include plugin_dir_path(__FILE__) . 'views/schedule-page.php';
    return ob_get_clean();
}
add_shortcode('gs_schedule', 'gs_schedule_shortcode');


function gs_enqueue_schedule_styles($hook) {
    if ($hook === 'toplevel_page_schedulecs-admin-menu' || 
        is_page('schedule/wordpress/расписание')) {
        wp_enqueue_style('schedulecs-admin-style');
    }
}

add_action('admin_enqueue_scripts', 'gs_enqueue_schedule_styles');
add_action('wp_enqueue_scripts', 'gs_enqueue_schedule_styles');

function schedulecs_enqueue_list_page_assets() {
    if (has_shortcode(get_the_content(), 'gs_schedule_selector')) {
        wp_enqueue_style('schedulecs-list-style', 
            plugin_dir_url(__FILE__) . 'assets/css/list-page.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/css/list-page.css')
        );
        
        wp_enqueue_script('schedulecs-list-js',
            plugin_dir_url(__FILE__) . 'assets/js/list-page.js',
            ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/list-page.js'),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'schedulecs_enqueue_list_page_assets');
