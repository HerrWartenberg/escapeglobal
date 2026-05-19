<?php
/**
 * Template für die Kategorie "bildschirm" – nur Content, kein Theme-Chrome.
 * Datei ablegen in: wp-content/themes/DEIN-THEME/category-bildschirm.php
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schulnews</title>
  <?php wp_head(); ?>
  <style>
    /* Alle Theme-Styles überschreiben – nur Content zeigen */
    * { box-sizing: border-box; }

    body {
      margin: 0;
      padding: 16px;
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #ffffff;
      color: #0a1a3a;
    }

    .news-list {
      display: flex;
      flex-direction: column;
      gap: 24px;
      max-width: 100%;
    }

    .news-item {
      border-bottom: 1px solid #dde8f4;
      padding-bottom: 24px;
    }

    .news-item:last-child { border-bottom: none; }

    .news-date {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: #5a7a9a;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .news-date::before {
      content: '';
      display: inline-block;
      width: 6px; height: 6px;
      background: #f5a623;
      border-radius: 50%;
    }

    .news-title {
      font-size: 18px;
      font-weight: 700;
      color: #0c2594;
      margin: 0 0 10px;
      line-height: 1.3;
    }

    .news-title a {
      color: inherit;
      text-decoration: none;
    }

    .news-title a:hover { text-decoration: underline; }

    .news-thumbnail {
      width: 100%;
      max-height: 260px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 12px;
      display: block;
    }

    .news-content {
      font-size: 14px;
      line-height: 1.65;
      color: #2a3a5a;
    }

    .news-content img {
      max-width: 100%;
      height: auto;
      border-radius: 6px;
      margin: 8px 0;
    }

    .news-content p { margin: 0 0 10px; }
    .news-content p:last-child { margin-bottom: 0; }

    .no-posts {
      text-align: center;
      padding: 48px 16px;
      color: #5a7a9a;
      font-size: 15px;
    }
  </style>
</head>
<body>

<?php if (have_posts()) : ?>
  <div class="news-list">
  <?php while (have_posts()) : the_post(); ?>
    <div class="news-item">

      <div class="news-date">
        <?php echo get_the_date('j. F Y'); ?>
      </div>

      <h2 class="news-title">
        <a href="<?php the_permalink(); ?>" target="_blank" rel="noopener">
          <?php the_title(); ?>
        </a>
      </h2>

      <?php if (has_post_thumbnail()) : ?>
        <img class="news-thumbnail"
          src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>"
          alt="">
      <?php endif; ?>

      <div class="news-content">
        <?php the_content(); ?>
      </div>

    </div>
  <?php endwhile; ?>
  </div>

<?php else : ?>
  <div class="no-posts">Keine Beiträge vorhanden.</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
