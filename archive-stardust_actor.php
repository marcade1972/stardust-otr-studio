<?php get_header(); ?>
<section class="actor-directory wrap">
  <header class="actor-directory-header">
    <p class="eyebrow">Voices of the Golden Age</p>
    <h1>Actor Database</h1>
    <p>Explore the performers who brought old-time radio to life.</p>
  </header>
  <form class="actor-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
    <input type="hidden" name="post_type" value="stardust_actor">
    <label class="screen-reader-text" for="actor-search-field">Search actors</label>
    <input id="actor-search-field" type="search" name="s" placeholder="Search the actor collection…" value="<?php echo esc_attr(get_search_query()); ?>">
    <button type="submit">Search</button>
  </form>
  <?php if (have_posts()): ?>
  <div class="actor-card-grid">
    <?php while (have_posts()): the_post(); ?>
      <article class="actor-card">
        <a class="actor-card-photo" href="<?php the_permalink(); ?>" style="background-image:url('<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(),'medium_large') ?: get_template_directory_uri().'/assets/images/featured-fallback.jpg'); ?>')"></a>
        <div class="actor-card-copy">
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <?php $life=array_filter([get_post_meta(get_the_ID(),'_stardust_actor_birth',true),get_post_meta(get_the_ID(),'_stardust_actor_death',true)]); if($life): ?><p><?php echo esc_html(implode(' – ',$life)); ?></p><?php endif; ?>
          <p><?php echo esc_html(wp_trim_words(get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 23)); ?></p>
          <a class="actor-read-more" href="<?php the_permalink(); ?>">Read Biography →</a>
        </div>
      </article>
    <?php endwhile; ?>
  </div>
  <?php the_posts_pagination(); else: ?>
    <div class="studio-panel actor-empty"><h2>The museum is being prepared.</h2><p>Add your first Actor from the WordPress dashboard.</p></div>
  <?php endif; ?>
</section>
<?php get_footer(); ?>
