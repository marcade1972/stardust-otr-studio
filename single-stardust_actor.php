<?php get_header(); while(have_posts()): the_post();
$id=get_the_ID();
$born=get_post_meta($id,'_stardust_actor_birth',true);
$died=get_post_meta($id,'_stardust_actor_death',true);
$birthplace=get_post_meta($id,'_stardust_actor_birthplace',true);
$known=stardust_actor_lines($id,'_stardust_actor_known_for');
$timeline=stardust_actor_lines($id,'_stardust_actor_timeline');
$related=stardust_actor_lines($id,'_stardust_actor_related');
?>
<article class="actor-profile wrap">
  <nav class="actor-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> › <a href="<?php echo esc_url(get_post_type_archive_link('stardust_actor')); ?>">Actors</a> › <?php the_title(); ?></nav>
  <header class="actor-profile-heading">
    <p class="eyebrow">Stardust Actor Museum</p>
    <h1><?php the_title(); ?></h1>
    <?php if($born||$died): ?><p><?php echo esc_html(trim($born.($died?' – '.$died:''))); ?></p><?php endif; ?>
    <?php if($birthplace): ?><small><?php echo esc_html($birthplace); ?></small><?php endif; ?>
  </header>
  <div class="actor-profile-grid">
    <aside class="actor-portrait-panel studio-panel">
      <?php if (has_post_thumbnail()) { the_post_thumbnail('large', ['class'=>'actor-portrait']); } else { ?><div class="actor-portrait actor-placeholder">🎙</div><?php } ?>
    </aside>
    <section class="actor-biography studio-panel">
      <h2>Biography</h2>
      <div class="entry-content"><?php the_content(); ?></div>
      <?php if($known): ?><h2>Known For</h2><ul><?php foreach($known as $item): ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul><?php endif; ?>
    </section>
    <aside class="actor-facts studio-panel">
      <?php if($timeline): ?><h2>Timeline</h2><ul class="actor-timeline"><?php foreach($timeline as $item): ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if($related): ?><h2>Related People</h2><ul><?php foreach($related as $item): ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul><?php endif; ?>
    </aside>
  </div>
</article>
<?php endwhile; get_footer(); ?>
