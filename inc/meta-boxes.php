<?php
if (!defined('ABSPATH')) { exit; }

function stardust_add_episode_meta_box(): void {
    add_meta_box('stardust_episode_details', __('Stardust Episode Details', 'stardust-broadcast'), 'stardust_episode_meta_box_html', 'post', 'normal', 'high');
}
add_action('add_meta_boxes', 'stardust_add_episode_meta_box');

function stardust_episode_meta_box_html(WP_Post $post): void {
    wp_nonce_field('stardust_save_episode', 'stardust_episode_nonce');
    $fields = [
        '_stardust_series' => 'Series Name',
        '_stardust_broadcast_date' => 'Original Broadcast Date',
        '_stardust_runtime' => 'Runtime',
        '_stardust_audio_url' => 'Audio File URL',
        '_stardust_download_url' => 'Download URL',
        '_stardust_history_note' => 'Historical Note',
    ];
    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, $key, true);
        $type = $key === '_stardust_broadcast_date' ? 'date' : ($key === '_stardust_history_note' ? 'textarea' : 'text');
        echo '<p><label for="'.esc_attr($key).'"><strong>'.esc_html__($label, 'stardust-broadcast').'</strong></label><br>';
        if ($type === 'textarea') {
            echo '<textarea class="widefat" rows="3" id="'.esc_attr($key).'" name="'.esc_attr($key).'">'.esc_textarea($value).'</textarea>';
        } else {
            echo '<input class="widefat" type="'.esc_attr($type).'" id="'.esc_attr($key).'" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
        }
        echo '</p>';
    }
    echo '<p><em>'.esc_html__('The Original Broadcast Date powers the On This Day homepage. Existing embedded players may remain in the post content while the archive is updated.', 'stardust-broadcast').'</em></p>';
}

function stardust_save_episode_meta(int $post_id): void {
    if (!isset($_POST['stardust_episode_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['stardust_episode_nonce'])), 'stardust_save_episode')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    $keys = ['_stardust_series','_stardust_broadcast_date','_stardust_runtime','_stardust_audio_url','_stardust_download_url','_stardust_history_note'];
    foreach ($keys as $key) {
        if (!isset($_POST[$key])) continue;
        $raw = wp_unslash($_POST[$key]);
        $value = strpos($key, '_url') !== false ? esc_url_raw($raw) : sanitize_textarea_field($raw);
        update_post_meta($post_id, $key, $value);
    }
}
add_action('save_post', 'stardust_save_episode_meta');
