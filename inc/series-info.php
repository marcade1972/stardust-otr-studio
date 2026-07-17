<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Central Series Information Library.
 * Series are represented by WordPress categories so the information can be
 * shared automatically by every episode assigned to that category.
 */
function stardust_series_info_fields(): array {
    return [
        '_stardust_series_tagline' => ['label' => __('Series Tagline', 'stardust-broadcast'), 'type' => 'text'],
        '_stardust_series_run' => ['label' => __('Original Run', 'stardust-broadcast'), 'type' => 'text'],
        '_stardust_series_network' => ['label' => __('Network(s)', 'stardust-broadcast'), 'type' => 'text'],
        '_stardust_series_creator' => ['label' => __('Creator / Producer', 'stardust-broadcast'), 'type' => 'text'],
        '_stardust_series_cast' => ['label' => __('Main Cast', 'stardust-broadcast'), 'type' => 'textarea'],
        '_stardust_series_host' => ['label' => __('Host / Announcer', 'stardust-broadcast'), 'type' => 'text'],
        '_stardust_series_genre' => ['label' => __('Genre', 'stardust-broadcast'), 'type' => 'text'],
        '_stardust_series_episode_count' => ['label' => __('Known Episode Count', 'stardust-broadcast'), 'type' => 'text'],
        '_stardust_series_sponsors' => ['label' => __('Sponsors', 'stardust-broadcast'), 'type' => 'textarea'],
        '_stardust_series_overview' => ['label' => __('Series Overview / History', 'stardust-broadcast'), 'type' => 'editor'],
        '_stardust_series_facts' => ['label' => __('Did You Know? (one fact per line)', 'stardust-broadcast'), 'type' => 'textarea'],
        '_stardust_series_related' => ['label' => __('Related Series (comma separated)', 'stardust-broadcast'), 'type' => 'text'],
    ];
}

function stardust_series_info_edit_fields(WP_Term $term): void {
    wp_nonce_field('stardust_save_series_info', 'stardust_series_info_nonce');
    foreach (stardust_series_info_fields() as $key => $config) {
        $value = (string) get_term_meta($term->term_id, $key, true);
        ?>
        <tr class="form-field stardust-series-info-field">
            <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($config['label']); ?></label></th>
            <td>
                <?php if ($config['type'] === 'textarea'): ?>
                    <textarea class="large-text" rows="4" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>"><?php echo esc_textarea($value); ?></textarea>
                <?php elseif ($config['type'] === 'editor'): ?>
                    <?php wp_editor($value, ltrim($key, '_'), [
                        'textarea_name' => $key,
                        'textarea_rows' => 10,
                        'media_buttons' => false,
                        'teeny' => true,
                    ]); ?>
                <?php else: ?>
                    <input class="regular-text" type="text" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
    ?>
    <tr class="form-field">
        <th scope="row"><?php esc_html_e('How this is used', 'stardust-broadcast'); ?></th>
        <td><p class="description"><?php esc_html_e('This information creates the public series profile and the About This Series panel on every episode assigned to this category. Leave fields blank when information is uncertain.', 'stardust-broadcast'); ?></p></td>
    </tr>
    <?php
}
add_action('category_edit_form_fields', 'stardust_series_info_edit_fields', 20);

function stardust_save_series_info(int $term_id): void {
    if (!current_user_can('manage_categories')) { return; }
    if (!isset($_POST['stardust_series_info_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['stardust_series_info_nonce'])), 'stardust_save_series_info')) { return; }

    foreach (stardust_series_info_fields() as $key => $config) {
        if (!isset($_POST[$key])) { continue; }
        $raw = wp_unslash($_POST[$key]);
        $value = $config['type'] === 'editor' ? wp_kses_post($raw) : sanitize_textarea_field($raw);
        if ($value !== '') {
            update_term_meta($term_id, $key, $value);
        } else {
            delete_term_meta($term_id, $key);
        }
    }
}
add_action('edited_category', 'stardust_save_series_info');

function stardust_series_has_profile(int $term_id): bool {
    foreach (['_stardust_series_overview','_stardust_series_tagline','_stardust_series_run','_stardust_series_network','_stardust_series_cast'] as $key) {
        if (trim((string) get_term_meta($term_id, $key, true)) !== '') { return true; }
    }
    return false;
}

function stardust_series_profile_data(int $term_id): array {
    $data = [];
    foreach (array_keys(stardust_series_info_fields()) as $key) {
        $data[$key] = (string) get_term_meta($term_id, $key, true);
    }
    return $data;
}

function stardust_series_term_for_post(int $post_id) {
    if (function_exists('stardust_get_series_term')) {
        $term = stardust_get_series_term($post_id);
        if ($term && !is_wp_error($term)) { return $term; }
    }
    $categories = get_the_category($post_id);
    return (!empty($categories) && !is_wp_error($categories)) ? $categories[0] : null;
}

function stardust_render_series_facts(string $facts): string {
    $lines = preg_split('/\r\n|\r|\n/', $facts);
    $lines = array_values(array_filter(array_map('trim', (array) $lines)));
    if (!$lines) { return ''; }
    $html = '<ul class="series-facts">';
    foreach ($lines as $line) { $html .= '<li>' . esc_html($line) . '</li>'; }
    return $html . '</ul>';
}

function stardust_render_about_series_panel(int $post_id): string {
    $term = stardust_series_term_for_post($post_id);
    if (!$term || !stardust_series_has_profile((int) $term->term_id)) { return ''; }
    $data = stardust_series_profile_data((int) $term->term_id);
    $image = function_exists('stardust_get_series_art_url') ? stardust_get_series_art_url($post_id, 'medium_large') : '';
    $overview = trim(wp_strip_all_tags($data['_stardust_series_overview']));
    if (strlen($overview) > 520) { $overview = wp_trim_words($overview, 75, '…'); }

    ob_start(); ?>
    <aside class="about-series-panel">
        <?php if ($image): ?><a class="about-series-art" href="<?php echo esc_url(get_category_link($term)); ?>" style="background-image:url('<?php echo esc_url($image); ?>')" aria-label="<?php echo esc_attr($term->name); ?>"></a><?php endif; ?>
        <div class="about-series-copy">
            <p class="eyebrow"><?php esc_html_e('About This Series', 'stardust-broadcast'); ?></p>
            <h2><a href="<?php echo esc_url(get_category_link($term)); ?>"><?php echo esc_html($term->name); ?></a></h2>
            <?php if ($data['_stardust_series_tagline']): ?><p class="series-tagline"><?php echo esc_html($data['_stardust_series_tagline']); ?></p><?php endif; ?>
            <?php if ($overview): ?><p><?php echo esc_html($overview); ?></p><?php endif; ?>
            <a class="button button-small" href="<?php echo esc_url(get_category_link($term)); ?>"><?php esc_html_e('Explore the Series', 'stardust-broadcast'); ?></a>
        </div>
    </aside>
    <?php return (string) ob_get_clean();
}

function stardust_register_series_info_library(): void {
    add_theme_page(
        __('Series Information Library', 'stardust-broadcast'),
        __('Series Information Library', 'stardust-broadcast'),
        'manage_categories',
        'stardust-series-info',
        'stardust_render_series_info_library'
    );
}
add_action('admin_menu', 'stardust_register_series_info_library');

function stardust_render_series_info_library(): void {
    if (!current_user_can('manage_categories')) { return; }
    $categories = get_categories(['hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC']);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Stardust Series Information Library', 'stardust-broadcast'); ?></h1>
        <p><?php esc_html_e('Build each series profile once and Stardust will reuse it on the public series page and on every related episode.', 'stardust-broadcast'); ?></p>
        <table class="widefat striped stardust-series-table">
            <thead><tr><th><?php esc_html_e('Series / Category', 'stardust-broadcast'); ?></th><th><?php esc_html_e('Profile Status', 'stardust-broadcast'); ?></th><th><?php esc_html_e('Episodes', 'stardust-broadcast'); ?></th><th><?php esc_html_e('Action', 'stardust-broadcast'); ?></th></tr></thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><strong><?php echo esc_html($category->name); ?></strong></td>
                    <td><?php echo stardust_series_has_profile((int) $category->term_id) ? '<span style="color:#167a35;font-weight:600">' . esc_html__('Profile started', 'stardust-broadcast') . '</span>' : '<span style="color:#8a5a00">' . esc_html__('Not started', 'stardust-broadcast') . '</span>'; ?></td>
                    <td><?php echo esc_html((string) $category->count); ?></td>
                    <td><a class="button" href="<?php echo esc_url(get_edit_term_link($category->term_id, 'category')); ?>"><?php echo stardust_series_has_profile((int) $category->term_id) ? esc_html__('Edit Profile', 'stardust-broadcast') : esc_html__('Add Information', 'stardust-broadcast'); ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="description"><?php esc_html_e('Tip: genre-only categories can remain blank. Add profiles to categories that represent actual radio series.', 'stardust-broadcast'); ?></p>
    </div>
    <?php
}
