<?php get_header(); while(have_posts()): the_post(); ?>
<article class="single-episode wrap">
  <header><p class="eyebrow"><?php echo esc_html(get_post_meta(get_the_ID(),'_stardust_series',true)); ?></p><h1><?php the_title(); ?></h1><p class="broadcast-date"><?php echo esc_html(stardust_broadcast_date(get_the_ID())); ?></p></header>
  <?php
  $single_art = function_exists('stardust_safe_thumbnail_url') ? stardust_safe_thumbnail_url(get_the_ID(), 'large') : '';
  if (!$single_art && function_exists('stardust_get_series_art_url')) { $single_art = stardust_get_series_art_url(get_the_ID(), 'large'); }
  if ($single_art): ?><img class="single-art" src="<?php echo esc_url($single_art); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"><?php endif; ?>
  <div class="single-player"><?php echo stardust_episode_audio(get_the_ID()); ?></div>
  <?php $clean_episode_content = stardust_episode_content_without_audio(get_the_ID()); ?>
  <?php if ($clean_episode_content): ?><div class="entry-content"><?php echo $clean_episode_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
  <?php if (function_exists('stardust_render_episode_rating')) echo stardust_render_episode_rating(get_the_ID()); ?>
  <?php if (function_exists('stardust_render_about_series_panel')) echo stardust_render_about_series_panel(get_the_ID()); ?>
  <?php if($note=get_post_meta(get_the_ID(),'_stardust_history_note',true)): ?><aside class="history-callout"><h2>Radio History Note</h2><p><?php echo nl2br(esc_html($note)); ?></p></aside><?php endif; ?>
  <nav class="post-nav"><div><?php previous_post_link('%link','← Previous Broadcast'); ?></div><div><?php next_post_link('%link','Next Broadcast →'); ?></div></nav>
</article>
<?php endwhile; get_footer(); ?>
