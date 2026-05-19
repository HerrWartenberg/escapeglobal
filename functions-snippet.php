<?php
// Diesen Block in die functions.php des Child-Themes einfügen
// (catch-base-child/functions.php) – am Ende der Datei anhängen.

add_filter('template_include', function($template) {
    if (is_category('bildschirm') || is_category(34)) {
        $custom = get_stylesheet_directory() . '/category-bildschirm.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
});
