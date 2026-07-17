<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url(get_template_directory_uri().'/assets/icons/favicon-32x32.png'); ?>">
<link rel="apple-touch-icon" href="<?php echo esc_url(get_template_directory_uri().'/assets/icons/apple-touch-icon.png'); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e('Skip to content', 'stardust-broadcast'); ?></a>
<header class="site-header blueprint-header">
<?php if (is_front_page()):
  $header_featured_query = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 1,
    'ignore_sticky_posts' => true,
    'meta_key' => '_stardust_broadcast_date',
    'orderby' => ['meta_value' => 'DESC', 'date' => 'DESC'],
  ]);
  $header_featured = $header_featured_query->have_posts() ? $header_featured_query->posts[0] : null;
  if (!$header_featured) {
    $header_fallback_query = new WP_Query(['post_type'=>'post','posts_per_page'=>1,'ignore_sticky_posts'=>true]);
    $header_featured = $header_fallback_query->have_posts() ? $header_fallback_query->posts[0] : null;
  }
?>
  <div class="stardust-masthead">
    <a class="stardust-signature-stage" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
      <img src="<?php echo esc_url(get_template_directory_uri().'/assets/images/stardust-signature-stage.png'); ?>" alt="Stardust OTR with a glowing vintage microphone and Art Deco skyline">
      <span class="stardust-arriving-star" aria-hidden="true">★</span>
      <span class="stardust-on-air" aria-label="On Air"><span aria-hidden="true"></span>ON AIR</span>
    </a>
    <div class="stardust-header-featured">
      <?php if ($header_featured):
        $header_series = function_exists('stardust_series_label') ? stardust_series_label($header_featured->ID) : '';
        $header_date = function_exists('stardust_broadcast_date') ? stardust_broadcast_date($header_featured->ID) : '';
        $header_art = function_exists('stardust_get_series_art_url') ? stardust_get_series_art_url($header_featured->ID, 'medium') : get_the_post_thumbnail_url($header_featured, 'medium');
      ?>
        <div class="header-featured-copy">
          <span class="header-featured-kicker">Featured Broadcast</span>
          <?php if ($header_series): ?><p><?php echo esc_html($header_series); ?></p><?php endif; ?>
          <h2><?php echo esc_html(get_the_title($header_featured)); ?></h2>
          <?php if ($header_date): ?><time><?php echo esc_html($header_date); ?></time><?php endif; ?>
          <a class="header-listen-button" href="<?php echo esc_url(get_permalink($header_featured)); ?>">Listen Now <span aria-hidden="true">▶</span></a>
        </div>
        <a class="header-featured-art" href="<?php echo esc_url(get_permalink($header_featured)); ?>" style="background-image:url('<?php echo esc_url($header_art); ?>')" aria-label="<?php echo esc_attr(get_the_title($header_featured)); ?>"></a>
      <?php else: ?>
        <div class="header-featured-copy"><span class="header-featured-kicker">Featured Broadcast</span><h2>The station is warming up.</h2></div>
      <?php endif; ?>
    </div>
  </div>
  <?php wp_reset_postdata(); ?>
<?php else: ?>
  <div class="stardust-compact-header wrap">
    <a class="compact-wordmark" href="<?php echo esc_url(home_url('/')); ?>"><strong>STARDUST</strong> <b>OTR</b></a>
    <span><?php echo esc_html(get_theme_mod('stardust_header_tagline', 'Timeless Radio. Endless Memories.')); ?></span>
  </div>
<?php endif; ?>
  <nav class="main-nav blueprint-nav" aria-label="<?php esc_attr_e('Primary Navigation', 'stardust-broadcast'); ?>">
    <div class="wrap nav-row">
      <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu"><span aria-hidden="true">☰</span> <?php esc_html_e('Station Menu', 'stardust-broadcast'); ?></button>
      <?php wp_nav_menu(['theme_location'=>'primary','container'=>false,'menu_id'=>'primary-menu','fallback_cb'=>'stardust_menu_fallback']); ?>
      <div class="nav-search"><?php get_search_form(); ?></div>
    </div>
  </nav>
</header>
<main id="main-content" class="site-main">
