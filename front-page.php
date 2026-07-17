<?php
get_header();

$selected_month = isset($_GET['otr_month']) ? max(1, min(12, (int) $_GET['otr_month'])) : (int) current_time('n');
$selected_day   = isset($_GET['otr_day']) ? max(1, min(31, (int) $_GET['otr_day'])) : (int) current_time('j');
$episodes       = stardust_get_on_this_day_posts(12, $selected_month, $selected_day);
$using_fallback = false;

if (!$episodes->have_posts()) {
    $using_fallback = true;
    $episodes = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 12,
        'ignore_sticky_posts' => true,
    ]);
}

$featured    = $episodes->have_posts() ? $episodes->posts[0] : null;
$month_name  = wp_date('F', mktime(0, 0, 0, $selected_month, 1));
$display_year = $featured ? get_post_meta($featured->ID, '_stardust_broadcast_date', true) : '';
$display_year = $display_year ? wp_date('Y', strtotime($display_year)) : wp_date('Y');
$asset_uri   = get_template_directory_uri() . '/assets/images';
?>
<section class="genre-showcase wrap" aria-labelledby="genre-heading">
  <div class="section-bar genre-title-bar"><span aria-hidden="true"></span><h2 id="genre-heading">Genres</h2><span aria-hidden="true"></span></div>
  <div class="blueprint-genres">
  <?php
  $genres = [
    'Adventure'=>'adventure.jpg',
    'Comedy'=>'comedy.jpg',
    'Drama'=>'drama.jpg',
    'Mystery'=>'mystery.jpg',
    'Science Fiction'=>'sci-fi.jpg',
    'Western'=>'western.jpg',
    'Horror'=>'horror.jpg',
    'Romance'=>'romance.jpg',
    'Detective'=>'detective.jpg',
    'Juvenile'=>'juvenile.jpg'
  ];
  foreach ($genres as $name=>$image) {
      $cat = get_category_by_slug(sanitize_title($name));
      if (!$cat) { $cat = get_term_by('name',$name,'category'); }
      $url = ($cat && !is_wp_error($cat)) ? get_category_link($cat) : home_url('/?s=' . rawurlencode($name));
      ?>
      <a class="blueprint-genre" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($name); ?>" style="background-image:url('<?php echo esc_url($asset_uri . '/genres/' . $image); ?>')"></a>
      <?php
  }
  ?>
  </div>
</section>

<section class="lower-deck wrap">
  <div class="recent-panel studio-panel">
    <div class="section-bar"><h2><?php echo $using_fallback ? esc_html__('Recently Added Broadcasts','stardust-broadcast') : esc_html(sprintf(__('Broadcasts for %s %d','stardust-broadcast'),$month_name,$selected_day)); ?></h2><a href="<?php echo esc_url(home_url('/')); ?>">View All Episodes</a></div>
    <div class="compact-episodes">
    <?php foreach (array_slice($episodes->posts,0,4) as $post): setup_postdata($post); $img=stardust_get_series_art_url(get_the_ID(),'medium_large'); ?>
      <article class="compact-card">
        <a class="compact-thumb" href="<?php the_permalink(); ?>" style="background-image:url('<?php echo esc_url($img ? $img : $asset_uri . '/featured-fallback.jpg'); ?>')"></a>
        <div><small><?php echo esc_html(stardust_broadcast_date(get_the_ID())); ?></small><p><?php echo esc_html(stardust_series_label(get_the_ID())); ?></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3></div>
      </article>
    <?php endforeach; wp_reset_postdata(); ?>
    </div>
  </div>

  <aside class="right-rail">
    <?php if (function_exists('stardust_render_top_rated_episodes')) echo stardust_render_top_rated_episodes(5); ?>
    <section class="studio-panel featured-series-panel">
      <div class="section-bar"><h2>Featured Series</h2></div>
      <?php
      $series_cats = get_categories(['hide_empty'=>true,'number'=>3,'orderby'=>'count','order'=>'DESC']);
      if ($series_cats) { foreach ($series_cats as $cat): ?>
        <a class="series-row" href="<?php echo esc_url(get_category_link($cat)); ?>"><span><?php echo esc_html($cat->name); ?></span><small>View All Episodes ›</small></a>
      <?php endforeach; } else { ?>
        <p class="empty-note">Featured series will appear as your archive grows.</p>
      <?php } ?>
    </section>
    <section class="studio-panel support-panel">
      <div class="support-mic" aria-hidden="true">🎙</div>
      <div><h2>Support Stardust OTR</h2><p>We’re a labor of love, preserving the Golden Age of Radio for future generations.</p><span class="coming-soon-badge">Coming Soon</span></div>
    </section>
  </aside>
</section>
<?php get_footer(); ?>
