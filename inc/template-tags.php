<?php
if (!defined('ABSPATH')) { exit; }

function stardust_get_on_this_day_posts(int $limit = 10, ?int $month = null, ?int $day = null): WP_Query {
    $month = $month ?: (int) current_time('n');
    $day = $day ?: (int) current_time('j');
    $needle = sprintf('-%02d-%02d', $month, $day);
    return new WP_Query([
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
    ]);
}

function stardust_episode_audio(int $post_id): string {
    // Prefer the dedicated Stardust audio URL when one has been entered.
    $url = trim((string) get_post_meta($post_id, '_stardust_audio_url', true));
    if ($url) {
        return wp_audio_shortcode([
            'src'     => esc_url($url),
            'preload' => 'none',
        ]);
    }

    // Support older posts that use the standard WordPress [audio] shortcode.
    $content = (string) get_post_field('post_content', $post_id);
    if ($content && has_shortcode($content, 'audio')) {
        $pattern = get_shortcode_regex(['audio']);
        if (preg_match('/' . $pattern . '/s', $content, $matches)) {
            $rendered = do_shortcode($matches[0]);
            if (trim($rendered)) {
                return $rendered;
            }
        }
    }

    // Support Gutenberg audio blocks and audio supplied by existing plugins.
    if ($content) {
        $rendered_content = apply_filters('the_content', $content);
        if (preg_match('/<audio\b[^>]*>.*?<\/audio>/is', $rendered_content, $matches)) {
            return $matches[0];
        }

        // Fall back to the first direct audio-file link found in the post.
        if (preg_match('/https?:\/\/[^\s"\'<>]+\.(?:mp3|m4a|ogg|wav)(?:\?[^\s"\'<>]*)?/i', $content, $matches)) {
            return wp_audio_shortcode([
                'src'     => esc_url_raw($matches[0]),
                'preload' => 'none',
            ]);
        }
    }

    // Finally, use the first audio attachment associated with the post.
    $attachments = get_attached_media('audio', $post_id);
    if ($attachments) {
        $attachment = reset($attachments);
        $attachment_url = wp_get_attachment_url($attachment->ID);
        if ($attachment_url) {
            return wp_audio_shortcode([
                'src'     => esc_url($attachment_url),
                'preload' => 'none',
            ]);
        }
    }

    return '';
}

function stardust_broadcast_date(int $post_id, string $format = 'F j, Y'): string {
    $raw = get_post_meta($post_id, '_stardust_broadcast_date', true);
    if (!$raw) return '';
    $time = strtotime($raw);
    return $time ? wp_date($format, $time) : '';
}

/**
 * Remove embedded podcast/audio markup and exposed audio-file URLs from episode copy.
 * The theme renders one dedicated player separately, so duplicate players are omitted.
 */
function stardust_strip_episode_audio_content(string $html): string {
    if ($html === '') {
        return '';
    }

    // Remove standard audio shortcodes before they are rendered.
    $html = preg_replace('/\[audio\b[^\]]*\](?:.*?\[\/audio\])?/is', '', $html);

    // Remove Gutenberg audio blocks, native audio elements, and common PowerPress wrappers.
    $patterns = [
        '/<!--\s*wp:audio\b.*?<!--\s*\/wp:audio\s*-->/is',
        '/<audio\b[^>]*>.*?<\/audio>/is',
        '/<div\b[^>]*class=((["\'])[^"\']*\bpowerpress_player\b[^"\']*\2)[^>]*>.*?<\/div>/is',
        '/<p\b[^>]*class=((["\'])[^"\']*\bpowerpress_links\b[^"\']*\2)[^>]*>.*?<\/p>/is',
        '/<div\b[^>]*class=((["\'])[^"\']*\bpowerpress_links\b[^"\']*\2)[^>]*>.*?<\/div>/is',
    ];
    $html = preg_replace($patterns, '', $html);

    // Remove exposed direct links to audio files, whether linked or plain text.
    $html = preg_replace(
        '/<a\b[^>]*href=(["\'])https?:\/\/[^"\']+\.(?:mp3|m4a|ogg|wav)(?:\?[^"\']*)?\1[^>]*>.*?<\/a>/is',
        '',
        $html
    );
    $html = preg_replace('/https?:\/\/[^\s<]+\.(?:mp3|m4a|ogg|wav)(?:\?[^\s<]*)?/i', '', $html);

    // Remove the common plugin-generated podcast links line if it remains as text.
    $html = preg_replace('/(?:<p[^>]*>)?\s*Podcast:\s*Play in new window\s*\|\s*Download\s*(?:<\/p>)?/i', '', $html);

    // Tidy empty paragraphs and excess whitespace left behind.
    $html = preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?\s*>)*<\/p>/i', '', $html);
    return trim((string) $html);
}

/**
 * Return clean episode body copy while preserving ordinary formatting and embeds.
 */
function stardust_episode_content_without_audio(int $post_id): string {
    $content = (string) get_post_field('post_content', $post_id);
    $content = stardust_strip_episode_audio_content($content);
    if ($content === '') {
        return '';
    }

    // Run normal content formatting, then remove any audio injected by podcast plugins.
    $rendered = apply_filters('the_content', $content);
    return stardust_strip_episode_audio_content((string) $rendered);
}

/**
 * Return a clean archive-card excerpt without players, podcast links, or raw MP3 URLs.
 */
function stardust_episode_excerpt_without_audio(int $post_id, int $word_limit = 42): string {
    $manual_excerpt = (string) get_post_field('post_excerpt', $post_id);
    $source = $manual_excerpt !== '' ? $manual_excerpt : (string) get_post_field('post_content', $post_id);
    $source = stardust_strip_episode_audio_content($source);
    $text = wp_strip_all_tags(strip_shortcodes($source), true);
    $text = trim(preg_replace('/\s+/', ' ', $text));

    if ($text === '') {
        return '';
    }

    return wpautop(esc_html(wp_trim_words($text, $word_limit, '…')));
}
