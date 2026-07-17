<?php get_header(); ?>
<section class="content-wrap wrap">
  <header class="archive-header">
    <p class="eyebrow"><?php esc_html_e('Stardust OTR Archives', 'stardust-broadcast'); ?></p>
    <h1><?php is_archive() ? the_archive_title() : esc_html_e('Latest Broadcasts','stardust-broadcast'); ?></h1>
    <?php the_archive_description('<div class="archive-description">','</div>'); ?>
  </header>
  <div class="archive-layout">
    <div class="post-list">
      <?php if(have_posts()): while(have_posts()): the_post(); ?>
        <article <?php post_class('list-card'); ?>>
          <?php $card_image = function_exists('stardust_get_series_art_url') ? stardust_get_series_art_url(get_the_ID(), 'medium_large') : (function_exists('stardust_safe_thumbnail_url') ? stardust_safe_thumbnail_url(get_the_ID(), 'medium_large') : ''); ?>
          <a class="list-card-image" href="<?php the_permalink(); ?>" <?php if($card_image) echo 'style="background-image:url('.esc_url($card_image).')"'; ?> aria-label="<?php echo esc_attr(get_the_title()); ?>"></a>
          <div class="list-card-body"><p class="series"><?php echo esc_html(get_post_meta(get_the_ID(),'_stardust_series',true)); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p class="broadcast-date"><?php echo esc_html(stardust_broadcast_date(get_the_ID())); ?></p><?php the_excerpt(); ?><a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e('Tune in →', 'stardust-broadcast'); ?></a></div>
        </article>
      <?php endwhile; the_posts_pagination(); else: ?><p><?php esc_html_e('No broadcasts found.', 'stardust-broadcast'); ?></p><?php endif; ?>
    </div>
    <?php if (is_active_sidebar('archive-side')): ?><aside class="archive-sidebar"><?php dynamic_sidebar('archive-side'); ?></aside><?php endif; ?>
  </div>
</section>
<?php get_footer(); ?>
