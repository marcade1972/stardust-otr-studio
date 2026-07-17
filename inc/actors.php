<?php
if (!defined('ABSPATH')) { exit; }

function stardust_register_actor_post_type(): void {
    register_post_type('stardust_actor', [
        'labels' => [
            'name' => __('Actors', 'stardust-broadcast'),
            'singular_name' => __('Actor', 'stardust-broadcast'),
            'add_new_item' => __('Add New Actor', 'stardust-broadcast'),
            'edit_item' => __('Edit Actor', 'stardust-broadcast'),
            'search_items' => __('Search Actors', 'stardust-broadcast'),
            'not_found' => __('No actors found.', 'stardust-broadcast'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-microphone',
        'supports' => ['title','editor','thumbnail','excerpt','custom-fields'],
        'has_archive' => 'actors',
        'rewrite' => ['slug'=>'actors','with_front'=>false],
        'menu_position' => 6,
    ]);
}
add_action('init', 'stardust_register_actor_post_type');

function stardust_actor_meta_boxes(): void {
    add_meta_box('stardust-actor-details', __('Actor Details', 'stardust-broadcast'), 'stardust_actor_details_box', 'stardust_actor', 'normal', 'high');
}
add_action('add_meta_boxes', 'stardust_actor_meta_boxes');

function stardust_actor_details_box(WP_Post $post): void {
    wp_nonce_field('stardust_actor_details', 'stardust_actor_nonce');
    $fields = [
        '_stardust_actor_birth' => __('Born', 'stardust-broadcast'),
        '_stardust_actor_death' => __('Died', 'stardust-broadcast'),
        '_stardust_actor_birthplace' => __('Birthplace', 'stardust-broadcast'),
        '_stardust_actor_known_for' => __('Known For (one item per line)', 'stardust-broadcast'),
        '_stardust_actor_timeline' => __('Timeline (one event per line)', 'stardust-broadcast'),
        '_stardust_actor_related' => __('Related People (one name per line)', 'stardust-broadcast'),
    ];
    echo '<div class="stardust-actor-admin">';
    foreach ($fields as $key=>$label) {
        $value = (string) get_post_meta($post->ID, $key, true);
        echo '<p><label style="display:block;font-weight:700;margin-bottom:5px" for="'.esc_attr($key).'">'.esc_html($label).'</label>';
        if (in_array($key, ['_stardust_actor_known_for','_stardust_actor_timeline','_stardust_actor_related'], true)) {
            echo '<textarea style="width:100%;min-height:90px" id="'.esc_attr($key).'" name="'.esc_attr($key).'">'.esc_textarea($value).'</textarea>';
        } else {
            echo '<input style="width:100%" type="text" id="'.esc_attr($key).'" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
        }
        echo '</p>';
    }
    echo '</div>';
}

function stardust_save_actor_details(int $post_id): void {
    if (!isset($_POST['stardust_actor_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['stardust_actor_nonce'])), 'stardust_actor_details')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    $single = ['_stardust_actor_birth','_stardust_actor_death','_stardust_actor_birthplace'];
    $multi = ['_stardust_actor_known_for','_stardust_actor_timeline','_stardust_actor_related'];
    foreach ($single as $key) {
        if (isset($_POST[$key])) update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$key])));
    }
    foreach ($multi as $key) {
        if (isset($_POST[$key])) update_post_meta($post_id, $key, sanitize_textarea_field(wp_unslash($_POST[$key])));
    }
}
add_action('save_post_stardust_actor', 'stardust_save_actor_details');

function stardust_actor_lines(int $post_id, string $key): array {
    $value = (string) get_post_meta($post_id, $key, true);
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value))));
}
