<?php
// In catch-base-child/functions.php am Ende anhängen.
// Versteckt auf der Startseite nur Beiträge, die AUSSCHLIESSLICH
// die Kategorie "bildschirm" (ID 34) haben.

add_action('pre_get_posts', function($query) {
    if (!$query->is_main_query() || !$query->is_home()) return;

    // Alle Beiträge aus Kategorie 34 holen
    $cat34_ids = get_posts(array(
        'fields'           => 'ids',
        'posts_per_page'   => -1,
        'cat'              => 34,
        'suppress_filters' => true,
    ));

    // Nur die herausfiltern, die keine weitere Kategorie haben
    $exclude = array();
    foreach ($cat34_ids as $post_id) {
        $cats = wp_get_post_categories($post_id);
        if (count($cats) === 1) {
            $exclude[] = $post_id;
        }
    }

    if (!empty($exclude)) {
        $query->set('post__not_in', $exclude);
    }
});
