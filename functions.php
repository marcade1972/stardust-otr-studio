<?php
if (!defined('ABSPATH')) { exit; }

define('STARDUST_VERSION', '1.7.6');

require_once get_template_directory() . '/inc/meta-boxes.php';
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/series-art.php';
require_once get_template_directory() . '/inc/series-info.php';
require_once get_template_directory() . '/inc/youtube-settings.php';
require_once get_template_directory() . '/inc/episode-ratings.php';
require_once get_template_directory() . '/inc/actors.php';

function stardust_setup(): void {
    load_theme_textdomain('stardust-broadcast', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['search-form','gallery','caption','style','script','comment-list','comment-form']);
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('custom-background', ['default-color' => '090806']);
    add_theme_support('custom-logo', ['height'=>180,'width'=>700,'flex-height'=>true,'flex-width'=>true]);
    register_nav_menus([
        'primary' => __('Primary Navigation', 'stardust-broadcast'),
        'footer'  => __('Footer Navigation', 'stardust-broadcast'),
    ]);
}
add_action('after_setup_theme', 'stardust_setup');

function stardust_assets(): void {
    wp_enqueue_style('stardust-style', get_stylesheet_uri(), [], STARDUST_VERSION);
    wp_enqueue_style('stardust-main', get_template_directory_uri() . '/assets/css/main.css', ['stardust-style'], STARDUST_VERSION);
    wp_enqueue_script('stardust-main', get_template_directory_uri() . '/assets/js/main.js', [], STARDUST_VERSION, true);
    wp_localize_script('stardust-main', 'stardustDial', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('stardust_tune_dial'),
        'loading' => __('Tuning the airwaves…', 'stardust-broadcast'),
        'error' => __('Static on the airwaves. Please try again.', 'stardust-broadcast'),
        'searching' => __('Searching the airwaves…', 'stardust-broadcast'),
        'tunedPrefix' => __('Now tuned to', 'stardust-broadcast'),
    ]);
    wp_localize_script('stardust-main', 'stardustRatings', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('stardust_episode_rating'),
        'saving' => __('Recording your vote…', 'stardust-broadcast'),
        'error' => __('Static on the line. Please try your vote again.', 'stardust-broadcast'),
    ]);
}
add_action('wp_enqueue_scripts', 'stardust_assets');

function stardust_widgets(): void {
    $areas = [
        'footer-one'   => __('Footer Column One', 'stardust-broadcast'),
        'footer-two'   => __('Footer Column Two', 'stardust-broadcast'),
        'footer-three' => __('Footer Column Three', 'stardust-broadcast'),
        'archive-side' => __('Archive Sidebar', 'stardust-broadcast'),
    ];
    foreach ($areas as $id => $name) {
        register_sidebar([
            'name' => $name,
            'id' => $id,
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
        ]);
    }
}
add_action('widgets_init', 'stardust_widgets');

function stardust_customize($wp_customize): void {
    $wp_customize->add_section('stardust_station', [
        'title' => __('Stardust Station Settings', 'stardust-broadcast'),
        'priority' => 30,
    ]);
    $settings = [
        'stardust_tagline' => ['Header Tagline', 'Classic Radio. Real Stories. Forever Timeless.', 'text', 'sanitize_text_field'],
        'stardust_on_air_text' => ['On Air Label', 'ON AIR', 'text', 'sanitize_text_field'],
        'stardust_utility_text' => ['Top Bar Message', 'Broadcasting timeless stories from the Golden Age of Radio', 'text', 'sanitize_text_field'],
        'stardust_support_url' => ['Support Button URL', '', 'url', 'esc_url_raw'],
        'stardust_facebook_url' => ['Facebook URL', 'https://www.facebook.com/stardustotr', 'url', 'esc_url_raw'],
        'stardust_x_url' => ['X URL', 'https://x.com/OtrStardus14653', 'url', 'esc_url_raw'],
        'stardust_instagram_url' => ['Instagram URL', 'https://www.instagram.com/stardustotr/', 'url', 'esc_url_raw'],
        'stardust_youtube_url' => ['YouTube URL', 'https://www.youtube.com/@stardustotr', 'url', 'esc_url_raw'],
        'stardust_archive_url' => ['Archive.org URL', 'https://archive.org/details/@stardust_otr', 'url', 'esc_url_raw'],
        'stardust_youtube_api_key' => ['YouTube API Key', '', 'text', 'sanitize_text_field'],
        'stardust_youtube_channel_id' => ['YouTube Channel ID', '', 'text', 'sanitize_text_field'],
        'stardust_station_calls' => ['Station Call Letters', 'WSTR 109', 'text', 'sanitize_text_field'],
        'stardust_navy_color' => ['Primary Navy Color', '#061729', 'color', 'sanitize_hex_color'],
        'stardust_gold_color' => ['Accent Gold Color', '#d9a441', 'color', 'sanitize_hex_color'],
    ];
    foreach ($settings as $id => $args) {
        $wp_customize->add_setting($id, ['default'=>$args[1], 'sanitize_callback'=>$args[3]]);
        $wp_customize->add_control($id, ['label'=>__($args[0], 'stardust-broadcast'), 'section'=>'stardust_station', 'type'=>$args[2]]);
    }
}
add_action('customize_register', 'stardust_customize');

function stardust_menu_fallback(): void {
    echo '<ul id="primary-menu" class="menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'stardust-broadcast') . '</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('stardust_actor')) . '">' . esc_html__('Actors', 'stardust-broadcast') . '</a></li>';
    wp_list_categories(['title_li'=>'', 'number'=>5, 'orderby'=>'count', 'order'=>'DESC']);
    echo '</ul>';
}

function stardust_body_classes(array $classes): array {
    if (is_front_page()) { $classes[] = 'stardust-home'; }
    if (is_singular('post')) { $classes[] = 'stardust-episode'; }
    return $classes;
}
add_filter('body_class', 'stardust_body_classes');

/**
 * Return a plain-text series label for a post.
 * Uses the custom Series field first, then the first assigned category.
 */
function stardust_series_label(int $post_id): string {
    $series = trim((string) get_post_meta($post_id, '_stardust_series', true));
    if ($series !== '') {
        return $series;
    }

    $categories = get_the_category($post_id);
    if (!empty($categories) && !is_wp_error($categories)) {
        return (string) $categories[0]->name;
    }

    return __('Stardust OTR', 'stardust-broadcast');
}



/**
 * Date shown on Tune the Dial cards.
 * Prefer the historical broadcast date; otherwise identify the archive date.
 */
function stardust_tuned_result_date(int $post_id): string {
    $broadcast = stardust_broadcast_date($post_id);
    if ($broadcast !== '') {
        return $broadcast;
    }
    return sprintf(__('Archive post: %s', 'stardust-broadcast'), get_the_date('F j, Y', $post_id));
}


/**
 * Return true when an attachment is one of the retired "The Whistler" images.
 * The old artwork may still exist in the Media Library or term metadata after a
 * theme upgrade, so the theme blocks it everywhere instead of trusting the URL.
 */
function stardust_is_retired_whistler_attachment(int $attachment_id): bool {
    if ($attachment_id <= 0) { return false; }

    $parts = [
        (string) get_the_title($attachment_id),
        (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
        (string) get_post_field('post_excerpt', $attachment_id),
        (string) get_post_field('post_content', $attachment_id),
        (string) get_attached_file($attachment_id),
    ];
    $haystack = strtolower(implode(' ', $parts));
    return strpos($haystack, 'whistler') !== false;
}

/** Return true when a URL points to retired Whistler artwork. */
function stardust_is_retired_whistler_url(string $url): bool {
    if ($url === '') { return false; }
    $decoded = strtolower(rawurldecode($url));
    return strpos($decoded, 'whistler') !== false;
}

/** Get a featured image URL while suppressing retired Whistler artwork. */
function stardust_safe_thumbnail_url(int $post_id, string $size = 'medium_large'): string {
    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    if (!$thumbnail_id || stardust_is_retired_whistler_attachment($thumbnail_id)) { return ''; }
    $url = (string) wp_get_attachment_image_url($thumbnail_id, $size);
    return stardust_is_retired_whistler_url($url) ? '' : $url;
}

/** Return bundled Thriller art for any legacy Whistler series/category. */
function stardust_legacy_whistler_replacement_url(): string {
    return get_template_directory_uri() . '/assets/images/genres/thriller.jpg';
}

/**
 * Render compact result cards for Tune the Dial.
 */
function stardust_render_dial_results(WP_Query $query, int $month, int $day): string {
    $asset_uri = get_template_directory_uri() . '/assets/images';
    ob_start();
    if ($query->have_posts()) {
        echo '<div class="dial-results-heading">';
        echo '<h2>' . esc_html(sprintf(__('Broadcasts for %s %d', 'stardust-broadcast'), wp_date('F', mktime(0, 0, 0, $month, 1)), $day)) . '</h2>';
        echo '<span>' . esc_html(sprintf(_n('%d broadcast found', '%d broadcasts found', $query->found_posts, 'stardust-broadcast'), $query->found_posts)) . '</span>';
        echo '</div>';
        if (!empty($query->stardust_used_archive_dates)) {
            echo '<p class="dial-fallback-notice">' . esc_html__('These matches use your existing WordPress post dates. Add Original Broadcast Dates over time for historically exact year information.', 'stardust-broadcast') . '</p>';
        }
        echo '<div class="dial-result-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $image = stardust_get_series_art_url($post_id, 'medium_large');
            if (!$image) {
                $image = stardust_safe_thumbnail_url($post_id, 'medium_large');
            }
            if (!$image) {
                $image = $asset_uri . '/featured-fallback.jpg';
            }
            echo '<article class="dial-result-card">';
            echo '<a class="dial-result-art" href="' . esc_url(get_permalink()) . '" style="background-image:url(\'' . esc_url($image) . '\')"></a>';
            echo '<div class="dial-result-copy">';
            echo '<small>' . esc_html(stardust_tuned_result_date($post_id)) . '</small>';
            echo '<p>' . esc_html(stardust_series_label($post_id)) . '</p>';
            echo '<h3><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
            echo '<a class="dial-listen-link" href="' . esc_url(get_permalink()) . '">' . esc_html__('Listen now ›', 'stardust-broadcast') . '</a>';
            echo '</div></article>';
        }
        echo '</div>';
    } else {
        echo '<div class="dial-no-results"><span aria-hidden="true">〰</span><h2>' . esc_html__('Static on the Airwaves…', 'stardust-broadcast') . '</h2><p>' . esc_html__('No dated broadcast was found for this selection. Try another day or let the dial surprise you.', 'stardust-broadcast') . '</p></div>';
    }
    wp_reset_postdata();
    return (string) ob_get_clean();
}

function stardust_get_tuned_posts(int $month, int $day, int $category_id = 0, int $limit = 12): WP_Query {
    $needle = sprintf('-%02d-%02d', $month, $day);
    $meta_args = [
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'meta_query' => [[
            'key' => '_stardust_broadcast_date',
            'value' => $needle,
            'compare' => 'LIKE',
        ]],
        'meta_key' => '_stardust_broadcast_date',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'ignore_sticky_posts' => true,
    ];
    if ($category_id > 0) {
        $meta_args['cat'] = $category_id;
    }

    $query = new WP_Query($meta_args);
    $query->stardust_used_archive_dates = false;

    // Existing Stardust posts predate the custom Original Broadcast Date field.
    // When no metadata match exists, use the WordPress publication month/day.
    // This fits the site's established workflow of posting an anniversary episode
    // on the same calendar day on which it originally aired.
    if (!$query->have_posts()) {
        $archive_args = [
            'post_type' => 'post',
            'posts_per_page' => $limit,
            'date_query' => [[
                'monthnum' => $month,
                'day' => $day,
                'inclusive' => true,
            ]],
            'orderby' => 'date',
            'order' => 'DESC',
            'ignore_sticky_posts' => true,
        ];
        if ($category_id > 0) {
            $archive_args['cat'] = $category_id;
        }
        $query = new WP_Query($archive_args);
        $query->stardust_used_archive_dates = true;
    }

    return $query;
}


/** Return the current public YouTube subscriber count when available. */
function stardust_youtube_subscriber_count(): ?int {
    $data = stardust_youtube_channel_data();
    return is_array($data) && isset($data['subscriber_count']) ? (int) $data['subscriber_count'] : null;
}

/**
 * WSTR 109 station audience counter.
 * Starts at 25,798 and records one visit per browser session (30 minutes).
 */
function stardust_station_counter_seed(): int {
    return 25798;
}

function stardust_seed_station_counter(): void {
    $current = (int) get_option('stardust_station_visits', 0);
    if ($current < stardust_station_counter_seed()) {
        update_option('stardust_station_visits', stardust_station_counter_seed(), false);
    }
}
add_action('after_switch_theme', 'stardust_seed_station_counter');

function stardust_record_station_visit(): void {
    if (is_admin() || wp_doing_ajax() || headers_sent()) { return; }
    if (!empty($_COOKIE['stardust_station_visitor'])) { return; }

    $count = max(stardust_station_counter_seed(), (int) get_option('stardust_station_visits', 0));
    update_option('stardust_station_visits', $count + 1, false);

    // Count a returning listener again after a 30-minute listening session expires.
    setcookie('stardust_station_visitor', '1', time() + (30 * MINUTE_IN_SECONDS), COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
}
add_action('template_redirect', 'stardust_record_station_visit');

function stardust_station_visits(): int {
    return max(stardust_station_counter_seed(), (int) get_option('stardust_station_visits', 0));
}

/** Rename legacy child/youth genre categories to the canonical Juvenile genre. */
function stardust_migrate_juvenile_genre(): void {
    if (get_option('stardust_juvenile_genre_migrated_129')) { return; }

    $taxonomy = 'category';
    $legacy_slugs = ['children-youth', 'children-and-youth', 'youth-and-children', 'youth-children'];
    $juvenile = get_term_by('slug', 'juvenile', $taxonomy);

    foreach ($legacy_slugs as $slug) {
        $legacy = get_term_by('slug', $slug, $taxonomy);
        if (!$legacy || is_wp_error($legacy)) { continue; }

        if ($juvenile && !is_wp_error($juvenile) && (int) $juvenile->term_id !== (int) $legacy->term_id) {
            $post_ids = get_objects_in_term((int) $legacy->term_id, $taxonomy);
            if (!is_wp_error($post_ids)) {
                foreach ($post_ids as $post_id) {
                    wp_set_post_terms((int) $post_id, [(int) $juvenile->term_id], $taxonomy, true);
                    wp_remove_object_terms((int) $post_id, (int) $legacy->term_id, $taxonomy);
                }
            }
        } else {
            $updated = wp_update_term((int) $legacy->term_id, $taxonomy, [
                'name' => 'Juvenile',
                'slug' => 'juvenile',
            ]);
            if (!is_wp_error($updated)) {
                $juvenile = get_term((int) $updated['term_id'], $taxonomy);
            }
        }
    }

    if (!$juvenile || is_wp_error($juvenile)) {
        $created = wp_insert_term('Juvenile', $taxonomy, ['slug' => 'juvenile']);
        if (!is_wp_error($created)) { $juvenile = get_term((int) $created['term_id'], $taxonomy); }
    }

    update_option('stardust_juvenile_genre_migrated_129', 1, false);
}
add_action('init', 'stardust_migrate_juvenile_genre', 25);

/** Return the bundled poster filename for a recognized genre category. */
function stardust_genre_art_filename($term): string {
    if (!($term instanceof WP_Term)) { return ''; }

    $map = [
        'western' => 'western.jpg',
        'thriller' => 'thriller.jpg',
        'mystery' => 'mystery.jpg',
        'comedy' => 'comedy.jpg',
        'drama' => 'drama.jpg',
        'science-fiction' => 'sci-fi.jpg',
        'sci-fi' => 'sci-fi.jpg',
        'adventure' => 'adventure.jpg',
        'crime' => 'crime.jpg',
        'horror' => 'horror.jpg',
        'romance' => 'romance.jpg',
        'detective' => 'detective.jpg',
        'juvenile' => 'juvenile.jpg',
        'children-youth' => 'children-youth.jpg',
        'children-and-youth' => 'children-youth.jpg',
        'youth-and-children' => 'children-youth.jpg',
        'youth-children' => 'children-youth.jpg',
    ];

    $slug = sanitize_title($term->slug ?: $term->name);
    if (isset($map[$slug])) { return $map[$slug]; }

    $name_slug = sanitize_title($term->name);
    return $map[$name_slug] ?? '';
}

function stardust_genre_art_url($term): string {
    $filename = stardust_genre_art_filename($term);
    return $filename ? get_template_directory_uri() . '/assets/images/genres/' . $filename : '';
}

/** Use the bundled Stardust identity for the login screen. */
function stardust_login_logo_css(): void {
    $logo = esc_url(get_template_directory_uri() . '/assets/images/stardust-login-logo.png');
    echo '<style>.login h1 a{background-image:url(' . $logo . ')!important;background-size:contain!important;width:260px!important;height:120px!important}</style>';
}
add_action('login_enqueue_scripts', 'stardust_login_logo_css');
add_filter('login_headerurl', function () { return home_url('/'); });
add_filter('login_headertext', function () { return get_bloginfo('name'); });

/** Prefer the bundled favicon unless WordPress Site Icon has been configured. */
function stardust_favicon_links(): void {
    if (has_site_icon()) { return; }
    $base = esc_url(get_template_directory_uri() . '/assets/icons');
    echo '<link rel="icon" href="' . $base . '/favicon-32x32.png" sizes="32x32">';
    echo '<link rel="icon" href="' . $base . '/favicon-16x16.png" sizes="16x16">';
    echo '<link rel="apple-touch-icon" href="' . $base . '/apple-touch-icon.png">';
}
add_action('wp_head', 'stardust_favicon_links', 2);

function stardust_ajax_tune_dial(): void {
    check_ajax_referer('stardust_tune_dial', 'nonce');
    $month = isset($_POST['month']) ? max(1, min(12, (int) $_POST['month'])) : (int) current_time('n');
    $day = isset($_POST['day']) ? max(1, min(31, (int) $_POST['day'])) : (int) current_time('j');
    $genre = isset($_POST['genre']) ? max(0, (int) $_POST['genre']) : 0;
    $query = stardust_get_tuned_posts($month, $day, $genre, 24);
    wp_send_json_success([
        'html' => stardust_render_dial_results($query, $month, $day),
        'month' => $month,
        'day' => $day,
        'count' => (int) $query->found_posts,
        'usedArchiveDates' => !empty($query->stardust_used_archive_dates),
    ]);
}
add_action('wp_ajax_stardust_tune_dial', 'stardust_ajax_tune_dial');
add_action('wp_ajax_nopriv_stardust_tune_dial', 'stardust_ajax_tune_dial');

function stardust_ajax_surprise_dial(): void {
    check_ajax_referer('stardust_tune_dial', 'nonce');
    global $wpdb;
    $dates = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> '' ORDER BY RAND() LIMIT 40",
        '_stardust_broadcast_date'
    ));
    if (empty($dates)) {
        $dates = $wpdb->get_col(
            "SELECT DISTINCT DATE_FORMAT(post_date, '%Y-%m-%d') FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY RAND() LIMIT 40"
        );
    }
    $month = (int) current_time('n');
    $day = (int) current_time('j');
    foreach ($dates as $date) {
        $time = strtotime((string) $date);
        if ($time) {
            $month = (int) wp_date('n', $time);
            $day = (int) wp_date('j', $time);
            break;
        }
    }
    $query = stardust_get_tuned_posts($month, $day, 0, 24);
    wp_send_json_success([
        'html' => stardust_render_dial_results($query, $month, $day),
        'month' => $month,
        'day' => $day,
        'count' => (int) $query->found_posts,
        'usedArchiveDates' => !empty($query->stardust_used_archive_dates),
    ]);
}
add_action('wp_ajax_stardust_surprise_dial', 'stardust_ajax_surprise_dial');
add_action('wp_ajax_nopriv_stardust_surprise_dial', 'stardust_ajax_surprise_dial');


/**
 * Stardust 1.3.0 data cleanup.
 * - Academy Award Theater belongs to Drama, never War.
 * - Remove retired Whistler artwork references from posts and category profiles.
 * - Keep the user's War category intact in WordPress, but the theme no longer displays it.
 */
function stardust_migrate_genres_and_retired_art_130(): void {
    if (get_option('stardust_genre_art_cleanup_130')) { return; }

    $drama = get_term_by('slug', 'drama', 'category');
    if (!$drama || is_wp_error($drama)) {
        $created = wp_insert_term('Drama', 'category', ['slug' => 'drama']);
        if (!is_wp_error($created)) { $drama = get_term((int) $created['term_id'], 'category'); }
    }
    $war = get_term_by('slug', 'war', 'category');

    $academy_posts = get_posts([
        'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1,
        's' => 'Academy Award Theater', 'fields' => 'ids',
    ]);
    $academy_meta_posts = get_posts([
        'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1,
        'meta_query' => [[
            'key' => '_stardust_series', 'value' => 'Academy Award Theater', 'compare' => 'LIKE',
        ]], 'fields' => 'ids',
    ]);
    $academy_posts = array_unique(array_map('intval', array_merge($academy_posts, $academy_meta_posts)));

    foreach ($academy_posts as $post_id) {
        if ($drama && !is_wp_error($drama)) {
            wp_set_post_terms($post_id, [(int) $drama->term_id], 'category', true);
        }
        if ($war && !is_wp_error($war)) {
            wp_remove_object_terms($post_id, [(int) $war->term_id], 'category');
        }
        $thumb = (int) get_post_thumbnail_id($post_id);
        // Academy Award Theater previously inherited the wrong Whistler image.
        if ($thumb) { delete_post_thumbnail($post_id); }
    }

    // Remove old Whistler artwork assignments from every category profile.
    $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
    if (!is_wp_error($categories)) {
        foreach ($categories as $category) {
            $image_id = (int) get_term_meta($category->term_id, '_stardust_series_art_id', true);
            $is_academy = strpos(strtolower((string) $category->name), 'academy award theater') !== false
                || strpos(strtolower((string) $category->name), 'academy award theatre') !== false;
            if ($is_academy || ($image_id && stardust_is_retired_whistler_attachment($image_id))) {
                delete_term_meta($category->term_id, '_stardust_series_art_id');
            }
        }
    }

    // Remove retired Whistler featured-image assignments from posts sitewide.
    $thumb_posts = get_posts([
        'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1,
        'meta_key' => '_thumbnail_id', 'fields' => 'ids',
    ]);
    foreach ($thumb_posts as $post_id) {
        $thumb = (int) get_post_thumbnail_id((int) $post_id);
        if ($thumb && stardust_is_retired_whistler_attachment($thumb)) {
            delete_post_thumbnail((int) $post_id);
        }
    }

    update_option('stardust_genre_art_cleanup_130', 1, false);
}
add_action('init', 'stardust_migrate_genres_and_retired_art_130', 40);

/** Force the correct category image for Academy Award Theater in every template. */
function stardust_is_academy_award_theater_post(int $post_id): bool {
    $series = strtolower(trim((string) get_post_meta($post_id, '_stardust_series', true)));
    $title = strtolower((string) get_the_title($post_id));
    return strpos($series, 'academy award theater') !== false
        || strpos($series, 'academy award theatre') !== false
        || strpos($title, 'academy award theater') !== false
        || strpos($title, 'academy award theatre') !== false;
}

function stardust_brand_color_css(): void {
    $navy = get_theme_mod('stardust_navy_color', '#061729');
    $gold = get_theme_mod('stardust_gold_color', '#d9a441');
    echo '<style id="stardust-brand-colors">:root{--stardust-navy:'.esc_attr($navy).';--stardust-gold:'.esc_attr($gold).'}</style>';
}
add_action('wp_head','stardust_brand_color_css',30);

/**
 * Make genre archives include the complete back catalogue.
 *
 * Older episodes may have been assigned only to their series category (for
 * example, X Minus One) while newer episodes were also assigned directly to
 * the Sci-Fi genre. For recognized genre pages, include the genre category,
 * its descendants, and any series categories whose saved Genre field matches.
 */
function stardust_expand_genre_archive_query(WP_Query $query): void {
    if (is_admin() || !$query->is_main_query() || !$query->is_category()) { return; }

    $term = get_queried_object();
    if (!($term instanceof WP_Term) || $term->taxonomy !== 'category') { return; }
    if (!function_exists('stardust_genre_art_filename') || stardust_genre_art_filename($term) === '') { return; }

    $genre_ids = [(int) $term->term_id];
    $children = get_term_children((int) $term->term_id, 'category');
    if (!is_wp_error($children)) {
        $genre_ids = array_merge($genre_ids, array_map('intval', $children));
    }

    $canonical_slug = sanitize_title((string) $term->slug);
    $canonical_name = sanitize_title((string) $term->name);
    $aliases = array_unique(array_filter([$canonical_slug, $canonical_name]));

    if (in_array($canonical_slug, ['sci-fi', 'science-fiction'], true)) {
        $aliases = array_merge($aliases, ['sci-fi', 'science-fiction', 'science fiction', 'scifi']);
    }
    if (in_array($canonical_slug, ['juvenile', 'children-youth', 'children-and-youth', 'youth-and-children', 'youth-children'], true)) {
        $aliases = array_merge($aliases, ['juvenile', 'children-youth', 'children and youth', 'children & youth', 'youth and children']);
    }

    $categories = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => false,
        'fields' => 'ids',
    ]);

    if (!is_wp_error($categories)) {
        foreach ($categories as $category_id) {
            $saved_genre = strtolower(trim((string) get_term_meta((int) $category_id, '_stardust_series_genre', true)));
            if ($saved_genre === '') { continue; }
            $saved_slug = sanitize_title($saved_genre);

            foreach ($aliases as $alias) {
                $alias_text = strtolower((string) $alias);
                $alias_slug = sanitize_title($alias_text);
                if ($saved_slug === $alias_slug || strpos($saved_genre, $alias_text) !== false) {
                    $genre_ids[] = (int) $category_id;
                    $series_children = get_term_children((int) $category_id, 'category');
                    if (!is_wp_error($series_children)) {
                        $genre_ids = array_merge($genre_ids, array_map('intval', $series_children));
                    }
                    break;
                }
            }
        }
    }

    $genre_ids = array_values(array_unique(array_filter(array_map('intval', $genre_ids))));

    // Replace the single-category restriction with the complete related set.
    $query->set('cat', 0);
    $query->set('category_name', '');
    $query->set('category__in', $genre_ids);
    $query->set('posts_per_page', 24);
    $query->set('ignore_sticky_posts', true);
}
add_action('pre_get_posts', 'stardust_expand_genre_archive_query', 20);

/**
 * Return the public origin of the site without the WordPress installation path.
 * This is used for legacy MP3 archives that live at the domain root while
 * WordPress itself is installed in a subdirectory such as /wordpress/.
 */
function stardust_public_origin_url(): string {
    $home = home_url('/');
    $scheme = wp_parse_url($home, PHP_URL_SCHEME);
    $host = wp_parse_url($home, PHP_URL_HOST);
    $port = wp_parse_url($home, PHP_URL_PORT);

    if (!$scheme || !$host) {
        return trailingslashit($home);
    }

    $origin = $scheme . '://' . $host;
    if ($port) {
        $origin .= ':' . $port;
    }

    return trailingslashit($origin);
}

/** Build a URL to a legacy archive folder at the public domain root. */
function stardust_archive_url(string $folder): string {
    return trailingslashit(stardust_public_origin_url() . ltrim($folder, '/'));
}

/**
 * Ensure the dedicated Lone Ranger library template is used for the canonical
 * series page even if WordPress resets or loses the saved page-template value.
 */
function stardust_force_lone_ranger_library_template(string $template): string {
    if (!is_page()) { return $template; }

    $page = get_queried_object();
    if (!($page instanceof WP_Post)) { return $template; }

    $slug = sanitize_title((string) $page->post_name);
    $title = sanitize_title((string) $page->post_title);
    if ($slug !== 'the-lone-ranger' && $title !== 'the-lone-ranger') { return $template; }

    $library_template = get_theme_file_path('/page-the-lone-ranger-library.php');
    return is_readable($library_template) ? $library_template : $template;
}
add_filter('template_include', 'stardust_force_lone_ranger_library_template', 99);

/**
 * Repair the saved template assignment and remove an accidentally inherited
 * Whistler featured image from the Lone Ranger page.
 */
function stardust_repair_lone_ranger_page_1931(): void {
    if (get_option('stardust_lone_ranger_page_repair_1931')) { return; }

    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'numberposts' => -1,
        's' => 'The Lone Ranger',
    ]);

    foreach ($pages as $page) {
        if (!($page instanceof WP_Post)) { continue; }
        $slug = sanitize_title((string) $page->post_name);
        $title = sanitize_title((string) $page->post_title);
        if ($slug !== 'the-lone-ranger' && $title !== 'the-lone-ranger') { continue; }

        update_post_meta($page->ID, '_wp_page_template', 'page-the-lone-ranger-library.php');
        $thumbnail_id = (int) get_post_thumbnail_id($page->ID);
        if ($thumbnail_id && stardust_is_retired_whistler_attachment($thumbnail_id)) {
            delete_post_thumbnail($page->ID);
        }
    }

    update_option('stardust_lone_ranger_page_repair_1931', 1, false);
}
add_action('init', 'stardust_repair_lone_ranger_page_1931', 30);
