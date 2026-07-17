</main>
<footer class="site-footer">
  <div class="footer-tuning" aria-hidden="true"><span></span><strong>STARDUST OTR</strong><span></span></div>
  <div class="wrap footer-grid">
    <section class="footer-about">
      <?php if (is_active_sidebar('footer-one')) { dynamic_sidebar('footer-one'); } else { ?>
        <h2><?php esc_html_e('Welcome to Stardust OTR', 'stardust-broadcast'); ?></h2>
        <p><?php esc_html_e('Your daily destination for a hand-picked episode from a radio series originally broadcast on this day in history.', 'stardust-broadcast'); ?></p>
      <?php } ?>
    </section>

    <section class="footer-support">
      <?php if (is_active_sidebar('footer-two')) { dynamic_sidebar('footer-two'); } else { ?>
        <h2><?php esc_html_e('Support the Station', 'stardust-broadcast'); ?></h2>
        <p><?php esc_html_e('Stardust OTR is a labor of love. Your support helps keep these timeless stories alive.', 'stardust-broadcast'); ?></p>
        <span class="coming-soon-badge"><?php esc_html_e('Coming Soon', 'stardust-broadcast'); ?></span>
      <?php } ?>
    </section>

    <?php
    $stardust_youtube_data = stardust_youtube_channel_data();
    $stardust_youtube_settings = stardust_get_youtube_settings();
    $stardust_social_links = [
      'facebook' => [
        'label' => __('Facebook', 'stardust-broadcast'),
        'description' => __('Join our community', 'stardust-broadcast'),
        'url' => get_theme_mod('stardust_facebook_url', 'https://www.facebook.com/stardustotr'),
      ],
      'youtube' => [
        'label' => __('YouTube', 'stardust-broadcast'),
        'description' => __('Watch classic broadcasts', 'stardust-broadcast'),
        'url' => get_theme_mod('stardust_youtube_url', 'https://www.youtube.com/@stardustotr'),
      ],
      'x' => [
        'label' => __('X', 'stardust-broadcast'),
        'description' => __('Daily broadcasts', 'stardust-broadcast'),
        'url' => get_theme_mod('stardust_x_url', 'https://x.com/OtrStardus14653'),
      ],
      'instagram' => [
        'label' => __('Instagram', 'stardust-broadcast'),
        'description' => __('Behind the scenes', 'stardust-broadcast'),
        'url' => get_theme_mod('stardust_instagram_url', 'https://www.instagram.com/stardustotr/'),
      ],
      'archive' => [
        'label' => __('Archive.org', 'stardust-broadcast'),
        'description' => __('Complete Broadcast Library', 'stardust-broadcast'),
        'url' => get_theme_mod('stardust_archive_url', 'https://archive.org/details/@stardust_otr'),
      ],
    ];
    ?>
    <section class="footer-social-panel">
      <h2><?php esc_html_e('Social Media', 'stardust-broadcast'); ?></h2>
      <nav class="footer-social-list" aria-label="<?php esc_attr_e('Stardust OTR social media', 'stardust-broadcast'); ?>">
        <?php foreach ($stardust_social_links as $network => $social): ?>
          <?php if (!empty($social['url'])): ?>
            <a class="footer-social-link social-<?php echo esc_attr($network); ?>" href="<?php echo esc_url($social['url']); ?>" target="_blank" rel="noopener noreferrer">
              <span class="social-icon" aria-hidden="true">
                <?php if ($network === 'facebook'): ?>
                  <svg viewBox="0 0 24 24" focusable="false"><path d="M14.2 8.1h2.7V4.3c-.5-.1-2.1-.2-4-.2-3.9 0-6.6 2.4-6.6 6.8v3.8H2v4.3h4.3V24h5.3v-5h4.1l.7-4.3h-4.8v-3.4c0-1.3.4-2.2 2.6-2.2Z"/></svg>
                <?php elseif ($network === 'youtube'): ?>
                  <svg viewBox="0 0 24 24" focusable="false"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4L15.8 12l-6.2 3.6Z"/></svg>
                <?php elseif ($network === 'x'): ?>
                  <svg viewBox="0 0 24 24" focusable="false"><path d="M18.9 2H22l-6.8 7.8L23.2 22H17l-4.9-6.4L6.5 22H3.4l7.2-8.3L.8 2H7l4.4 5.8L18.9 2Zm-1.1 17.8h1.7L6.1 4H4.3l13.5 15.8Z"/></svg>
                <?php elseif ($network === 'instagram'): ?>
                  <svg viewBox="0 0 24 24" focusable="false"><path d="M7.2 2h9.6A5.2 5.2 0 0 1 22 7.2v9.6a5.2 5.2 0 0 1-5.2 5.2H7.2A5.2 5.2 0 0 1 2 16.8V7.2A5.2 5.2 0 0 1 7.2 2Zm-.2 2A3 3 0 0 0 4 7v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm10.4 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></svg>
                <?php else: ?>
                  <svg viewBox="0 0 24 24" focusable="false"><path d="M3 4.5h18v3H3v-3Zm1.5 4.3h15v9.7h-15V8.8Zm2.2 2v5.7h2.1v-5.7H6.7Zm4.2 0v5.7H13v-5.7h-2.1Zm4.3 0v5.7h2.1v-5.7h-2.1ZM2 20h20v2H2v-2Z"/></svg>
                <?php endif; ?>
              </span>
              <span class="footer-social-copy">
                <strong><?php echo esc_html($social['label']); ?></strong>
                <small><?php echo esc_html($social['description']); ?></small>
                <?php if ($network === 'youtube' && is_array($stardust_youtube_data)): ?>
                  <?php
                    $youtube_count = (int) $stardust_youtube_data['subscriber_count'];
                    $youtube_goal = max(1, (int) $stardust_youtube_settings['goal']);
                    $youtube_progress = min(100, max(0, ($youtube_count / $youtube_goal) * 100));
                  ?>
                  <span class="youtube-live-count"><b><?php echo esc_html(number_format_i18n($youtube_count)); ?></b> <?php esc_html_e('Subscribers', 'stardust-broadcast'); ?></span>
                  <span class="youtube-goal-meter" aria-label="<?php echo esc_attr(sprintf(__('Subscriber progress: %1$s of %2$s', 'stardust-broadcast'), number_format_i18n($youtube_count), number_format_i18n($youtube_goal))); ?>"><i style="width:<?php echo esc_attr(number_format($youtube_progress, 2, '.', '')); ?>%"></i></span>
                  <span class="youtube-goal-copy"><?php echo esc_html(sprintf(__('Help us reach %s!', 'stardust-broadcast'), number_format_i18n($youtube_goal))); ?></span>
                <?php endif; ?>
              </span>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
    </section>
  </div>

  <?php if (has_nav_menu('footer')): ?>
    <nav class="footer-nav wrap" aria-label="<?php esc_attr_e('Footer Navigation', 'stardust-broadcast'); ?>">
      <?php wp_nav_menu(['theme_location'=>'footer','container'=>false,'depth'=>1]); ?>
    </nav>
  <?php endif; ?>

  <div class="footer-signoff wrap">
    <div class="footer-mission">
      <strong><?php esc_html_e('Thank you for tuning in to Stardust OTR', 'stardust-broadcast'); ?></strong>
      <span><?php esc_html_e('Every day we reopen another page of radio history.', 'stardust-broadcast'); ?></span>
    </div>
  </div>

  <section class="engineer-panel wrap" aria-label="WSTR 109 station statistics">
    <div class="engineer-title"><strong><?php echo esc_html(get_theme_mod('stardust_station_calls', 'WSTR 109')); ?></strong><span>Station Engineer’s Panel</span></div>
    <div class="gauge-grid">
      <div class="analog-gauge"><span>Signal Strength</span><div class="gauge-face"><i class="gauge-needle needle-signal"></i><b>WEAK</b><b>STRONG</b></div></div>
      <div class="counter-gauge"><span>Listeners Since Sign-On</span><strong class="odometer-counter"><?php echo esc_html(str_pad((string) stardust_station_visits(), 8, '0', STR_PAD_LEFT)); ?></strong><small>Station visits</small></div>
      <div class="analog-gauge"><span>Broadcast Power</span><div class="gauge-face"><i class="gauge-needle needle-power"></i><b>OFF</b><b>50 KW</b></div></div>
    </div>
  </section>

  <div class="footer-bottom wrap">
    <p>“<?php esc_html_e('Radio may have been yesterday’s technology, but the stories are eternal.', 'stardust-broadcast'); ?>”</p>
    <p>&copy; <?php echo esc_html(wp_date('Y')); ?> Stardust OTR</p>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
