<?php
get_header();
$term = get_queried_object();
$term_id = ($term instanceof WP_Term) ? (int) $term->term_id : 0;
$genre_image = function_exists('stardust_genre_art_url') ? stardust_genre_art_url($term) : '';
$is_genre_archive = $genre_image !== '';
$has_profile = !$is_genre_archive && $term_id && function_exists('stardust_series_has_profile') && stardust_series_has_profile($term_id);
$data = $has_profile ? stardust_series_profile_data($term_id) : [];
$image_id = $term_id ? (int) get_term_meta($term_id, '_stardust_series_art_id', true) : 0;
$image = '';
if ($term instanceof WP_Term && strpos(strtolower((string) $term->name), 'whistler') !== false) {
    $image = function_exists('stardust_legacy_whistler_replacement_url') ? stardust_legacy_whistler_replacement_url() : stardust_genre_art_url(get_term_by('slug', 'thriller', 'category'));
} elseif ($image_id && (!function_exists('stardust_is_retired_whistler_attachment') || !stardust_is_retired_whistler_attachment($image_id))) {
    $candidate = (string) wp_get_attachment_image_url($image_id, 'large');
    if (!function_exists('stardust_is_retired_whistler_url') || !stardust_is_retired_whistler_url($candidate)) { $image = $candidate; }
}
?>
<section class="content-wrap wrap series-profile-page">
    <?php if ($has_profile): ?>
        <header class="series-profile-hero">
            <?php if ($image): ?><div class="series-profile-art" style="background-image:url('<?php echo esc_url($image); ?>')"></div><?php endif; ?>
            <div class="series-profile-intro">
                <p class="eyebrow"><?php esc_html_e('Stardust Series Library', 'stardust-broadcast'); ?></p>
                <h1><?php single_cat_title(); ?></h1>
                <?php if ($data['_stardust_series_tagline']): ?><p class="series-profile-tagline"><?php echo esc_html($data['_stardust_series_tagline']); ?></p><?php endif; ?>
                <dl class="series-profile-stats">
                    <?php
                    $stats = [
                        '_stardust_series_run' => __('Original Run', 'stardust-broadcast'),
                        '_stardust_series_network' => __('Network', 'stardust-broadcast'),
                        '_stardust_series_genre' => __('Genre', 'stardust-broadcast'),
                        '_stardust_series_episode_count' => __('Known Episodes', 'stardust-broadcast'),
                    ];
                    foreach ($stats as $key => $label): if (!$data[$key]) continue; ?>
                        <div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html($data[$key]); ?></dd></div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </header>

        <div class="series-profile-grid">
            <main class="series-profile-main">
                <?php if ($data['_stardust_series_overview']): ?>
                    <section class="series-profile-section"><h2><?php esc_html_e('About the Series', 'stardust-broadcast'); ?></h2><div class="entry-content"><?php echo wp_kses_post(wpautop($data['_stardust_series_overview'])); ?></div></section>
                <?php endif; ?>
                <?php if ($data['_stardust_series_facts']): ?>
                    <section class="series-profile-section"><h2><?php esc_html_e('Did You Know?', 'stardust-broadcast'); ?></h2><?php echo stardust_render_series_facts($data['_stardust_series_facts']); ?></section>
                <?php endif; ?>
            </main>
            <aside class="series-profile-details">
                <h2><?php esc_html_e('Behind the Microphone', 'stardust-broadcast'); ?></h2>
                <?php
                $details = [
                    '_stardust_series_creator' => __('Creator / Producer', 'stardust-broadcast'),
                    '_stardust_series_host' => __('Host / Announcer', 'stardust-broadcast'),
                    '_stardust_series_cast' => __('Main Cast', 'stardust-broadcast'),
                    '_stardust_series_sponsors' => __('Sponsors', 'stardust-broadcast'),
                    '_stardust_series_related' => __('Related Series', 'stardust-broadcast'),
                ];
                foreach ($details as $key => $label): if (!$data[$key]) continue; ?>
                    <div class="series-detail-row"><h3><?php echo esc_html($label); ?></h3><p><?php echo nl2br(esc_html($data[$key])); ?></p></div>
                <?php endforeach; ?>
            </aside>
        </div>
        <header class="series-episodes-heading"><p class="eyebrow"><?php esc_html_e('Listen Now', 'stardust-broadcast'); ?></p><h2><?php printf(esc_html__('Available %s Episodes', 'stardust-broadcast'), esc_html($term->name)); ?></h2></header>
    <?php elseif ($is_genre_archive): ?>
        <header class="genre-archive-hero">
            <div class="genre-archive-poster" style="background-image:url('<?php echo esc_url($genre_image); ?>')" role="img" aria-label="<?php echo esc_attr($term->name); ?> genre artwork"></div>
            <div class="genre-archive-copy">
                <p class="eyebrow"><?php esc_html_e('WSTR 109 Genre Archives', 'stardust-broadcast'); ?></p>
                <h1><?php single_cat_title(); ?></h1>
                <?php the_archive_description('<div class="archive-description">','</div>'); ?>
                <p class="genre-archive-note"><?php esc_html_e('Tune in to broadcasts from this collection of Golden Age radio.', 'stardust-broadcast'); ?></p>
            </div>
        </header>
        <?php
        $genre_slug = ($term instanceof WP_Term) ? (string) $term->slug : '';
        $genre_name_lower = ($term instanceof WP_Term) ? strtolower((string) $term->name) : '';
        $is_scifi_genre = in_array($genre_slug, ['sci-fi', 'scifi', 'science-fiction'], true) || strpos($genre_name_lower, 'sci') !== false;
        if ($is_scifi_genre):
            $x1_page = get_page_by_path('x-minus-one-episode-library');
            $dimensionx_page = get_page_by_path('dimension-x-episode-library');
            $x1_url = $x1_page ? get_permalink($x1_page) : home_url('/x-minus-one-episode-library/');
            $dimensionx_url = $dimensionx_page ? get_permalink($dimensionx_page) : home_url('/dimension-x-episode-library/');
        ?>
            <section class="genre-complete-series" aria-labelledby="complete-scifi-series-title">
                <div class="genre-complete-series-heading">
                    <p class="eyebrow"><?php esc_html_e('Complete Series Libraries', 'stardust-broadcast'); ?></p>
                    <h2 id="complete-scifi-series-title"><?php esc_html_e('Explore Complete Sci-Fi Series', 'stardust-broadcast'); ?></h2>
                    <p><?php esc_html_e('Search and listen to every available episode from these featured collections.', 'stardust-broadcast'); ?></p>
                </div>
                <div class="genre-series-card-grid">
                    <a class="genre-series-card" href="<?php echo esc_url($x1_url); ?>">
                        <span class="genre-series-card-kicker"><?php esc_html_e('Complete Library', 'stardust-broadcast'); ?></span>
                        <strong><?php esc_html_e('X Minus One', 'stardust-broadcast'); ?></strong>
                        <span><?php esc_html_e('Browse and search the full series', 'stardust-broadcast'); ?> →</span>
                    </a>
                    <a class="genre-series-card" href="<?php echo esc_url($dimensionx_url); ?>">
                        <span class="genre-series-card-kicker"><?php esc_html_e('Complete Library', 'stardust-broadcast'); ?></span>
                        <strong><?php esc_html_e('Dimension X', 'stardust-broadcast'); ?></strong>
                        <span><?php esc_html_e('Browse and search the full series', 'stardust-broadcast'); ?> →</span>
                    </a>
                </div>
            </section>
        <?php endif; ?>
        <?php if ($is_genre_archive && $term instanceof WP_Term && in_array(strtolower((string) $term->slug), ['mystery','thriller'], true)):
            $cbsrmt_page = get_page_by_path('cbs-radio-mystery-theater-episode-library');
            if (!$cbsrmt_page) { $cbsrmt_page = get_page_by_path('cbs-radio-mystery-theater'); }
            $cbsrmt_url = $cbsrmt_page ? get_permalink($cbsrmt_page) : home_url('/cbs-radio-mystery-theater-episode-library/');
        ?>
            <section class="genre-complete-series" aria-labelledby="complete-mystery-series-title">
                <div class="genre-complete-series-heading">
                    <p class="eyebrow"><?php esc_html_e('Complete Series Library', 'stardust-broadcast'); ?></p>
                    <h2 id="complete-mystery-series-title"><?php esc_html_e('Explore CBS Radio Mystery Theater', 'stardust-broadcast'); ?></h2>
                    <p><?php esc_html_e('Search and listen to the complete collection, organized across its original broadcast years.', 'stardust-broadcast'); ?></p>
                </div>
                <div class="genre-series-card-grid">
                    <a class="genre-series-card" href="<?php echo esc_url($cbsrmt_url); ?>">
                        <span class="genre-series-card-kicker"><?php esc_html_e('Complete Library', 'stardust-broadcast'); ?></span>
                        <strong><?php esc_html_e('CBS Radio Mystery Theater', 'stardust-broadcast'); ?></strong>
                        <span><?php esc_html_e('Browse more than 1,400 episodes', 'stardust-broadcast'); ?> →</span>
                    </a>
                    <?php
                    $shadow_page = get_page_by_path('the-shadow-episode-library');
                    if (!$shadow_page) { $shadow_page = get_page_by_path('the-shadow'); }
                    $shadow_url = $shadow_page ? get_permalink($shadow_page) : home_url('/the-shadow-episode-library/');
                    ?>
                    <a class="genre-series-card" href="<?php echo esc_url($shadow_url); ?>">
                        <span class="genre-series-card-kicker"><?php esc_html_e('Complete Library', 'stardust-broadcast'); ?></span>
                        <strong><?php esc_html_e('The Shadow', 'stardust-broadcast'); ?></strong>
                        <span><?php esc_html_e('Enter the world of mystery and crime', 'stardust-broadcast'); ?> →</span>
                    </a>
                </div>
            </section>
        <?php endif; ?>
        <?php
        global $wp_query;
        $genre_preview_posts = [];
        if (!is_paged() && isset($wp_query->posts) && is_array($wp_query->posts)) {
            $genre_preview_posts = array_slice($wp_query->posts, 0, 8);
        }
        ?>
        <?php if ($genre_preview_posts): ?>
            <section class="genre-preview" aria-labelledby="genre-preview-title">
                <div class="genre-preview-heading">
                    <p class="eyebrow"><?php esc_html_e('A Sampling from the Archive', 'stardust-broadcast'); ?></p>
                    <h2 id="genre-preview-title"><?php esc_html_e('Broadcasts You’ll Find Here', 'stardust-broadcast'); ?></h2>
                </div>
                <ul class="genre-preview-list">
                    <?php foreach ($genre_preview_posts as $preview_post):
                        $preview_id = (int) $preview_post->ID;
                        $preview_series = function_exists('stardust_series_label') ? stardust_series_label($preview_id) : '';
                    ?>
                        <li>
                            <a href="<?php echo esc_url(get_permalink($preview_id)); ?>">
                                <span class="genre-preview-title"><?php echo esc_html(get_the_title($preview_id)); ?></span>
                                <?php if ($preview_series): ?><span class="genre-preview-series"><?php echo esc_html($preview_series); ?></span><?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
        <header class="series-episodes-heading"><p class="eyebrow"><?php esc_html_e('Listen Now', 'stardust-broadcast'); ?></p><h2><?php printf(esc_html__('%s Broadcasts', 'stardust-broadcast'), esc_html($term->name)); ?></h2></header>
    <?php else: ?>
        <header class="archive-header"><p class="eyebrow"><?php esc_html_e('Stardust OTR Archives', 'stardust-broadcast'); ?></p><h1><?php single_cat_title(); ?></h1><?php the_archive_description('<div class="archive-description">','</div>'); ?></header>
    <?php endif; ?>

    <div class="post-list series-episode-list">
        <?php if (have_posts()): while (have_posts()): the_post();
            $card_image = function_exists('stardust_get_series_art_url') ? stardust_get_series_art_url(get_the_ID(), 'medium_large') : '';
        ?>
            <article <?php post_class('list-card'); ?>>
                <a class="list-card-image" href="<?php the_permalink(); ?>" <?php if ($card_image) echo 'style="background-image:url(' . esc_url($card_image) . ')"'; ?> aria-label="<?php echo esc_attr(get_the_title()); ?>"></a>
                <div class="list-card-body">
                    <p class="series"><?php echo esc_html(stardust_series_label(get_the_ID())); ?></p>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p class="broadcast-date"><?php echo esc_html(stardust_broadcast_date(get_the_ID())); ?></p>
                    <?php echo stardust_episode_excerpt_without_audio(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php $episode_player = stardust_episode_audio(get_the_ID()); ?>
                    <?php if ($episode_player): ?>
                        <div class="list-card-player"><?php echo $episode_player; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    <?php endif; ?>
                    <a class="text-link" href="<?php the_permalink(); ?>"><?php echo $episode_player ? esc_html__('Episode details →', 'stardust-broadcast') : esc_html__('Tune in →', 'stardust-broadcast'); ?></a>
                </div>
            </article>
        <?php endwhile; the_posts_pagination(); else: ?><p><?php esc_html_e('No broadcasts found.', 'stardust-broadcast'); ?></p><?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
