<?php

require_once plugin_dir_path(__FILE__) . '/uploader.php';

function gs_register_admin_menu() {
    add_menu_page(
        'Генератор расписания',
        'Расписание',
        'manage_options',
        'gs-main',
        'gs_render_list_page',
        'dashicons-calendar-alt',
        6
    );

    add_submenu_page(
        'gs-main',
        'Выбор группы',
        'Выбор группы',
        'manage_options',
        'gs-main',
        'gs_render_list_page'
    );

    add_submenu_page(
        'gs-main',
        'Страница расписания',
        'Расписание группы',
        'manage_options',
        'gs-schedule',
        'gs_render_schedule_page'
    );

    $upload_hook = add_submenu_page(
        'gs-main',
        'Загрузить расписание',
        'Загрузить файл',
        'manage_options',
        'gs-upload',
        'gs_render_uploader_page'
    );

    add_action('admin_enqueue_scripts', function($hook) use ($upload_hook) {
        error_log("1");
        if ($hook === $upload_hook) {
            $css_path = plugin_dir_path(__FILE__) . 'assets/css/admin-style.css';
            $css_url  = plugin_dir_url(__FILE__) . 'assets/css/admin-style.css';
            error_log("2");
            if (file_exists($css_path)) {
                error_log("3");
                wp_enqueue_style(
                    'gs-admin-upload-style',
                    $css_url,
                    [],
                    filemtime($css_path)
                );
            } else {
                error_log("❌ admin-style.css не найден по пути: $css_path");
            }
        }
    });
}

add_action('admin_menu', 'gs_register_admin_menu');

function gs_render_list_page() {
    include plugin_dir_path(__FILE__) . '/views/list-page.php';
}

function gs_render_schedule_page() {
    include plugin_dir_path(__FILE__) . '/views/schedule-page.php';
}