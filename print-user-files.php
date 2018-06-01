<?php
/*
Plugin Name: Print — User files
Description: Используй шорткод [print_form], чтобы вывести форму загрузки файла
Version: 1.0
Author: Kate
Author URI: http://kutalo.com/
*/
?><?php

require 'PrintUserClass.php';
require 'shortcodes.php';
require 'ajax.php';

// инициализация
function bit_updater_install () {
    global $wpdb;
//    $wpdb->query(
//        "INSERT INTO `{$wpdb->prefix}bit_vendor` (`id`, `name`) VALUES
//            (1, 'Vitalbet'),
//            (2, 'Betting'),
//            (3, 'Mbitcasino');"
//    );
//    $wpdb->query(
//        "CREATE TABLE IF NOT EXISTS IF EXISTS `{$wpdb->prefix}bit_vendor_odd_rel` (
//          `id` int(11) NOT NULL AUTO_INCREMENT,
//          `id_vendor` int(11) DEFAULT NULL,
//          `id_odd` int(11) NOT NULL,
//          `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
//          `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
//          PRIMARY KEY (`id`),
//          KEY `id_vendor` (`id_vendor`),
//          KEY `id_odd` (`id_odd`),
//          CONSTRAINT `{$wpdb->prefix}bit_vendor_odd_rel_ibfk_1` FOREIGN KEY (`id_vendor`) REFERENCES `{$wpdb->prefix}bit_vendor` (`id`) ON DELETE SET NULL,
//          CONSTRAINT `{$wpdb->prefix}bit_vendor_odd_rel_ibfk_2` FOREIGN KEY (`id_odd`) REFERENCES `{$wpdb->prefix}bit_odd` (`id`) ON DELETE CASCADE
//        )"
//    );
}
register_activation_hook( __FILE__, 'bit_updater_install');

// деактивация
function bit_updater_deactivate () {
}
register_deactivation_hook( __FILE__, 'bit_updater_deactivate' );


//// action function for above hook
//function bit_updater_add_pages() {
//    add_menu_page(dirname(__FILE__) .'/admin-page.php', 'Bit DataUpdater', 'Bit DataUpdater', 8);
//    add_options_page('Bit DataUpdater', 'Bit DataUpdater', 8, dirname(__FILE__) .'/admin-page.php');
//}
//add_action('admin_menu', 'bit_updater_add_pages');

function tvgag_post_ajaxurl_scripts () {
    wp_localize_script('jquery', 'print_form',
        array(
            'url' => admin_url('admin-ajax.php')
        ));

    wp_register_script('print-user-files', plugins_url('/html/js/print-user-files.js', __FILE__));
    wp_enqueue_script('print-user-files');

    wp_register_script('print-user-modal', plugins_url('/html/js/print-user-modal.js', __FILE__));
    wp_enqueue_script('print-user-modal');

    wp_register_style('print-user-files', plugins_url('/html/css/print-user-files.css', __FILE__));
    wp_enqueue_style('print-user-files');
}
add_action('wp_enqueue_scripts', 'tvgag_post_ajaxurl_scripts', 40);

function print_tr ($name)
{
    $iso9_table = array(
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Ѓ' => 'G`',
        'Ґ' => 'G`', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Є' => 'YE',
        'Ж' => 'ZH', 'З' => 'Z', 'Ѕ' => 'Z', 'И' => 'I', 'Й' => 'Y',
        'Ј' => 'J', 'І' => 'I', 'Ї' => 'YI', 'К' => 'K', 'Ќ' => 'K',
        'Л' => 'L', 'Љ' => 'L', 'М' => 'M', 'Н' => 'N', 'Њ' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ў' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS',
        'Ч' => 'CH', 'Џ' => 'DH', 'Ш' => 'SH', 'Щ' => 'SH', 'Ъ' => '',
        'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ѓ' => 'g',
        'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'є' => 'ye',
        'ж' => 'zh', 'з' => 'z', 'ѕ' => 'z', 'и' => 'i', 'й' => 'y',
        'ј' => 'j', 'і' => 'i', 'ї' => 'yi', 'к' => 'k', 'ќ' => 'k',
        'л' => 'l', 'љ' => 'l', 'м' => 'm', 'н' => 'n', 'њ' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'џ' => 'dh', 'ш' => 'sh', 'щ' => 'sh', 'ь' => '',
        'ы' => 'y', 'ъ' => "", 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    );
    return strtr($name, $iso9_table);
}
