<?php
/*
Plugin Name: Swell Scale for WordPress
Plugin URI: http://michelepaolino.me
Description: Utility tool that helps you effortlessly generate color palettes, manage font pairings, and create typographic scales.
Version: 1.0
Author: Michele Paolino
Author URI: http://michelepaolino.me
Text Domain: michelepaolino.me
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once('vendor/autoload.php');
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

add_action('admin_menu', 'swell_scales_add_admin_menu');

function swell_scales_add_admin_menu() {
    add_menu_page(
        'Swell Scales Settings', // Title of the page
        'Swell Scales',         // Title of the menu
        'manage_options',       // Capability
        'swell-scales',         // Menu slug
        'swell_scales_settings_page' // Function that renders the configuration page
    );
}

function swell_scales_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Swell Scales Settings</h1>';
    echo '<form action="" method="post">';
    $font_pairing_files = glob(__DIR__ . '/partials/font-pairing/font-pairing-list/*.css');
    echo '<select name="selected_font_pairing">';
    foreach ($font_pairing_files as $file) {
        $file_name = basename($file);
        echo "<option value='$file_name'>$file_name</option>";
    }
    echo '</select>';
    echo '<input type="submit" name="compile" value="Compila SASS" />';
    echo '</form>';
    echo '</div>';

    if (isset($_POST['compile'])) {
        $compiler = new Compiler();
        $compiler->setImportPaths(__DIR__);  // Set the import path
        $compiler->setOutputStyle(OutputStyle::COMPRESSED); // Set the output style as compressed

        try {
            $result = $compiler->compileString('@import "style.scss";');
            file_put_contents(get_template_directory() . '/style.min.css', $result->getCss());
            echo "<p>Compilazione SASS completata!</p>";
        } catch (\Exception $e) {
            echo "<p>Errore durante la compilazione: " . $e->getMessage() . "</p>";
        }
    }
}

add_action('wp_enqueue_scripts', 'swell_scales_enqueue_styles');

function swell_scales_enqueue_styles() {
    // Enqueue Tailwind CSS from CDN
    wp_enqueue_style('tailwindcss', 'https://cdn.jsdelivr.net/npm/tailwindcss@^3.0/dist/tailwind.min.css');

    // Check if a font pairing file was selected and enqueue it
    if (isset($_POST['selected_font_pairing'])) {
        $selected_font_pairing = sanitize_text_field($_POST['selected_font_pairing']);
        $path_to_font_pairing = get_template_directory_uri() . '/partials/font-pairing/font-pairing-list/' . $selected_font_pairing;
        wp_enqueue_style('swell-scales-font-pairing', $path_to_font_pairing, array('tailwindcss'));
    }

    // Enqueue the compiled minified CSS, making sure it loads after Tailwind CSS
    $path_to_min_css = get_template_directory_uri() . '/style.min.css';
    wp_enqueue_style('swell-scales-style', $path_to_min_css, array('tailwindcss', 'swell-scales-font-pairing'));
}

add_action('wp_enqueue_scripts', 'swell_scales_enqueue_styles');


?>
