<?php
if (!defined('ABSPATH')) { exit; }

/** Register the Appearance > YouTube Settings screen. */
function stardust_youtube_admin_menu(): void {
    add_theme_page(
        __('Stardust YouTube Settings', 'stardust-broadcast'),
        __('YouTube Settings', 'stardust-broadcast'),
        'manage_options',
        'stardust-youtube-settings',
        'stardust_render_youtube_settings_page'
    );
}
add_action('admin_menu', 'stardust_youtube_admin_menu');

function stardust_youtube_settings_defaults(): array {
    return [
        'api_key' => '',
        'handle' => 'StardustOTR',
        'channel_id' => '',
        'goal' => 1000,
    ];
}

function stardust_get_youtube_settings(): array {
    $saved = get_option('stardust_youtube_settings', []);
    if (!is_array($saved)) { $saved = []; }
    $settings = wp_parse_args($saved, stardust_youtube_settings_defaults());

    // Bring forward values from the earlier Customizer fields when present.
    if ($settings['api_key'] === '') {
        $settings['api_key'] = trim((string) get_theme_mod('stardust_youtube_api_key', ''));
    }
    if ($settings['channel_id'] === '') {
        $settings['channel_id'] = trim((string) get_theme_mod('stardust_youtube_channel_id', ''));
    }
    return $settings;
}

function stardust_sanitize_youtube_settings($input): array {
    $old = stardust_get_youtube_settings();
    $input = is_array($input) ? $input : [];
    $new_key = isset($input['api_key']) ? trim(sanitize_text_field(wp_unslash($input['api_key']))) : '';

    $settings = [
        // Leaving the password box blank preserves the existing key.
        'api_key' => $new_key !== '' ? $new_key : (string) $old['api_key'],
        'handle' => isset($input['handle']) ? ltrim(sanitize_text_field(wp_unslash($input['handle'])), '@') : 'StardustOTR',
        'channel_id' => isset($input['channel_id']) ? sanitize_text_field(wp_unslash($input['channel_id'])) : '',
        'goal' => isset($input['goal']) ? max(1, absint($input['goal'])) : 1000,
    ];

    if (!empty($input['remove_api_key'])) {
        $settings['api_key'] = '';
    }

    stardust_clear_youtube_cache();
    add_settings_error('stardust_youtube_messages', 'stardust_youtube_saved', __('YouTube settings saved.', 'stardust-broadcast'), 'updated');
    return $settings;
}

function stardust_register_youtube_settings(): void {
    register_setting('stardust_youtube_settings_group', 'stardust_youtube_settings', [
        'type' => 'array',
        'sanitize_callback' => 'stardust_sanitize_youtube_settings',
        'default' => stardust_youtube_settings_defaults(),
    ]);
}
add_action('admin_init', 'stardust_register_youtube_settings');

function stardust_clear_youtube_cache(): void {
    delete_transient('stardust_youtube_channel_data');
    delete_option('stardust_youtube_last_good_data');
}

function stardust_render_youtube_settings_page(): void {
    if (!current_user_can('manage_options')) { return; }
    $settings = stardust_get_youtube_settings();
    $status = stardust_youtube_channel_data(true);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Stardust YouTube Settings', 'stardust-broadcast'); ?></h1>
        <p><?php esc_html_e('Connect the Stardust OTR YouTube channel so the footer can display its public subscriber count.', 'stardust-broadcast'); ?></p>
        <?php settings_errors('stardust_youtube_messages'); ?>

        <form method="post" action="options.php">
            <?php settings_fields('stardust_youtube_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stardust-youtube-api-key"><?php esc_html_e('YouTube API Key', 'stardust-broadcast'); ?></label></th>
                    <td>
                        <input id="stardust-youtube-api-key" name="stardust_youtube_settings[api_key]" type="password" class="regular-text" value="" autocomplete="new-password" placeholder="<?php echo $settings['api_key'] !== '' ? esc_attr__('API key already saved — leave blank to keep it', 'stardust-broadcast') : esc_attr__('Paste your replacement API key here', 'stardust-broadcast'); ?>">
                        <p class="description"><?php esc_html_e('The saved key is never printed back into this page. Leaving this field blank keeps the current key.', 'stardust-broadcast'); ?></p>
                        <?php if ($settings['api_key'] !== ''): ?>
                            <label><input type="checkbox" name="stardust_youtube_settings[remove_api_key]" value="1"> <?php esc_html_e('Remove the saved API key', 'stardust-broadcast'); ?></label>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stardust-youtube-handle"><?php esc_html_e('YouTube Handle', 'stardust-broadcast'); ?></label></th>
                    <td>
                        <div style="display:flex;align-items:center;gap:4px"><span>@</span><input id="stardust-youtube-handle" name="stardust_youtube_settings[handle]" type="text" class="regular-text" value="<?php echo esc_attr($settings['handle']); ?>"></div>
                        <p class="description"><?php esc_html_e('For your channel, use StardustOTR.', 'stardust-broadcast'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stardust-youtube-channel-id"><?php esc_html_e('Channel ID (optional)', 'stardust-broadcast'); ?></label></th>
                    <td>
                        <input id="stardust-youtube-channel-id" name="stardust_youtube_settings[channel_id]" type="text" class="regular-text" value="<?php echo esc_attr($settings['channel_id']); ?>">
                        <p class="description"><?php esc_html_e('Leave blank. The theme can find the channel from its handle. Use this only as a fallback.', 'stardust-broadcast'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stardust-youtube-goal"><?php esc_html_e('Subscriber Goal', 'stardust-broadcast'); ?></label></th>
                    <td><input id="stardust-youtube-goal" name="stardust_youtube_settings[goal]" type="number" min="1" step="1" value="<?php echo esc_attr((string) $settings['goal']); ?>"></td>
                </tr>
            </table>
            <?php submit_button(__('Save YouTube Settings', 'stardust-broadcast')); ?>
        </form>

        <hr>
        <h2><?php esc_html_e('Connection Status', 'stardust-broadcast'); ?></h2>
        <?php if ($settings['api_key'] === ''): ?>
            <p><strong><?php esc_html_e('Not connected:', 'stardust-broadcast'); ?></strong> <?php esc_html_e('Paste an API key above and save.', 'stardust-broadcast'); ?></p>
        <?php elseif (is_array($status) && isset($status['subscriber_count'])): ?>
            <p><strong><?php esc_html_e('Connected successfully.', 'stardust-broadcast'); ?></strong></p>
            <p><?php echo esc_html($status['title']); ?> — <strong><?php echo esc_html(number_format_i18n((int) $status['subscriber_count'])); ?></strong> <?php esc_html_e('subscribers', 'stardust-broadcast'); ?></p>
        <?php else: ?>
            <p><strong><?php esc_html_e('The channel could not be read.', 'stardust-broadcast'); ?></strong> <?php esc_html_e('Confirm the API key is active and restricted to YouTube Data API v3, then save again.', 'stardust-broadcast'); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

/** Retrieve and cache public channel information for one hour. */
function stardust_youtube_channel_data(bool $force_refresh = false): ?array {
    $settings = stardust_get_youtube_settings();
    $api_key = trim((string) $settings['api_key']);
    if ($api_key === '') { return null; }

    if ($force_refresh) { delete_transient('stardust_youtube_channel_data'); }
    $cached = get_transient('stardust_youtube_channel_data');
    if (is_array($cached)) { return $cached; }

    $args = ['part' => 'snippet,statistics', 'key' => $api_key];
    $channel_id = trim((string) $settings['channel_id']);
    $handle = ltrim(trim((string) $settings['handle']), '@');
    if ($channel_id !== '') {
        $args['id'] = $channel_id;
    } elseif ($handle !== '') {
        $args['forHandle'] = $handle;
    } else {
        return null;
    }

    $url = add_query_arg($args, 'https://www.googleapis.com/youtube/v3/channels');
    $response = wp_remote_get($url, ['timeout' => 10, 'redirection' => 3]);
    if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
        $last = get_option('stardust_youtube_last_good_data', null);
        return is_array($last) ? $last : null;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $item = isset($body['items'][0]) && is_array($body['items'][0]) ? $body['items'][0] : null;
    if (!$item || !empty($item['statistics']['hiddenSubscriberCount']) || !isset($item['statistics']['subscriberCount'])) {
        return null;
    }

    $data = [
        'channel_id' => sanitize_text_field((string) ($item['id'] ?? '')),
        'title' => sanitize_text_field((string) ($item['snippet']['title'] ?? 'Stardust OTR')),
        'subscriber_count' => (int) $item['statistics']['subscriberCount'],
        'fetched_at' => time(),
    ];
    set_transient('stardust_youtube_channel_data', $data, HOUR_IN_SECONDS);
    update_option('stardust_youtube_last_good_data', $data, false);
    return $data;
}
