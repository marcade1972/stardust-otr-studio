<?php
/**
 * Visitor episode ratings and homepage Top 5 list.
 *
 * Votes are stored in a dedicated table. A privacy-friendly SHA-256 hash is
 * generated from the visitor's IP address, browser signature, and WordPress
 * salts; the raw IP address is never stored.
 */
if (!defined('ABSPATH')) { exit; }

function stardust_ratings_table_name(): string {
    global $wpdb;
    return $wpdb->prefix . 'stardust_episode_ratings';
}

function stardust_install_ratings_table(): void {
    global $wpdb;
    $table = stardust_ratings_table_name();
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        post_id bigint(20) unsigned NOT NULL,
        voter_hash char(64) NOT NULL,
        vote tinyint(2) NOT NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY post_voter (post_id, voter_hash),
        KEY post_vote (post_id, vote)
    ) {$charset};";

    dbDelta($sql);
    update_option('stardust_ratings_db_version', '1.0');
}
add_action('after_switch_theme', 'stardust_install_ratings_table');

function stardust_maybe_install_ratings_table(): void {
    if (get_option('stardust_ratings_db_version') !== '1.0') {
        stardust_install_ratings_table();
    }
}
add_action('init', 'stardust_maybe_install_ratings_table', 5);

function stardust_rating_voter_hash(): string {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    return hash('sha256', $ip . '|' . $agent . '|' . wp_salt('auth'));
}

function stardust_get_episode_rating_counts(int $post_id): array {
    global $wpdb;
    $table = stardust_ratings_table_name();
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT
            SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END) AS likes,
            SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END) AS dislikes
         FROM {$table} WHERE post_id = %d",
        $post_id
    ), ARRAY_A);

    return [
        'likes' => isset($row['likes']) ? (int) $row['likes'] : 0,
        'dislikes' => isset($row['dislikes']) ? (int) $row['dislikes'] : 0,
    ];
}

function stardust_get_current_episode_vote(int $post_id): int {
    global $wpdb;
    $table = stardust_ratings_table_name();
    $vote = $wpdb->get_var($wpdb->prepare(
        "SELECT vote FROM {$table} WHERE post_id = %d AND voter_hash = %s LIMIT 1",
        $post_id,
        stardust_rating_voter_hash()
    ));
    return $vote === null ? 0 : (int) $vote;
}

function stardust_render_episode_rating(int $post_id): string {
    if (get_post_type($post_id) !== 'post') { return ''; }
    $counts = stardust_get_episode_rating_counts($post_id);
    $current = stardust_get_current_episode_vote($post_id);

    ob_start(); ?>
    <section class="episode-rating studio-panel" data-post-id="<?php echo esc_attr($post_id); ?>" aria-labelledby="episode-rating-title">
      <div class="rating-copy">
        <p class="eyebrow"><?php esc_html_e('Listener Rating', 'stardust-broadcast'); ?></p>
        <h2 id="episode-rating-title"><?php esc_html_e('Did you enjoy this broadcast?', 'stardust-broadcast'); ?></h2>
        <p><?php esc_html_e('Cast your vote and help other radio fans discover listener favorites.', 'stardust-broadcast'); ?></p>
      </div>
      <div class="rating-actions" role="group" aria-label="<?php esc_attr_e('Rate this episode', 'stardust-broadcast'); ?>">
        <button type="button" class="episode-vote-button vote-up<?php echo $current === 1 ? ' is-selected' : ''; ?>" data-vote="1" aria-pressed="<?php echo $current === 1 ? 'true' : 'false'; ?>">
          <span class="vote-icon" aria-hidden="true">👍</span>
          <span class="vote-label"><?php esc_html_e('Thumbs Up', 'stardust-broadcast'); ?></span>
          <strong class="vote-count vote-likes"><?php echo esc_html(number_format_i18n($counts['likes'])); ?></strong>
        </button>
        <button type="button" class="episode-vote-button vote-down<?php echo $current === -1 ? ' is-selected' : ''; ?>" data-vote="-1" aria-pressed="<?php echo $current === -1 ? 'true' : 'false'; ?>">
          <span class="vote-icon" aria-hidden="true">👎</span>
          <span class="vote-label"><?php esc_html_e('Thumbs Down', 'stardust-broadcast'); ?></span>
          <strong class="vote-count vote-dislikes"><?php echo esc_html(number_format_i18n($counts['dislikes'])); ?></strong>
        </button>
      </div>
      <p class="rating-status" aria-live="polite"><?php echo $current ? esc_html__('Your vote has been recorded. You may change it at any time.', 'stardust-broadcast') : ''; ?></p>
    </section>
    <?php
    return (string) ob_get_clean();
}

function stardust_ajax_rate_episode(): void {
    check_ajax_referer('stardust_episode_rating', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $vote = isset($_POST['vote']) ? (int) $_POST['vote'] : 0;
    if (!$post_id || get_post_status($post_id) !== 'publish' || get_post_type($post_id) !== 'post' || !in_array($vote, [-1, 1], true)) {
        wp_send_json_error(['message' => __('That vote could not be recorded.', 'stardust-broadcast')], 400);
    }

    global $wpdb;
    $table = stardust_ratings_table_name();
    $now = current_time('mysql');
    $result = $wpdb->replace(
        $table,
        [
            'post_id' => $post_id,
            'voter_hash' => stardust_rating_voter_hash(),
            'vote' => $vote,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        ['%d', '%s', '%d', '%s', '%s']
    );

    if ($result === false) {
        wp_send_json_error(['message' => __('The station could not save your vote. Please try again.', 'stardust-broadcast')], 500);
    }

    $counts = stardust_get_episode_rating_counts($post_id);
    wp_send_json_success([
        'likes' => $counts['likes'],
        'dislikes' => $counts['dislikes'],
        'vote' => $vote,
        'message' => __('Thanks! Your listener vote has been recorded.', 'stardust-broadcast'),
    ]);
}
add_action('wp_ajax_stardust_rate_episode', 'stardust_ajax_rate_episode');
add_action('wp_ajax_nopriv_stardust_rate_episode', 'stardust_ajax_rate_episode');

function stardust_get_top_rated_episodes(int $limit = 5): array {
    global $wpdb;
    $table = stardust_ratings_table_name();
    $limit = max(1, min(10, $limit));

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT r.post_id,
                SUM(CASE WHEN r.vote = 1 THEN 1 ELSE 0 END) AS likes,
                SUM(CASE WHEN r.vote = -1 THEN 1 ELSE 0 END) AS dislikes
         FROM {$table} r
         INNER JOIN {$wpdb->posts} p ON p.ID = r.post_id
         WHERE p.post_type = 'post' AND p.post_status = 'publish'
         GROUP BY r.post_id
         HAVING likes > 0
         ORDER BY likes DESC, dislikes ASC, MAX(r.updated_at) DESC
         LIMIT %d",
        $limit
    ), ARRAY_A);

    return is_array($rows) ? $rows : [];
}

function stardust_render_top_rated_episodes(int $limit = 5): string {
    $episodes = stardust_get_top_rated_episodes($limit);
    ob_start(); ?>
    <section class="studio-panel listener-favorites-panel">
      <div class="section-bar"><h2><?php esc_html_e('Listener Favorites', 'stardust-broadcast'); ?></h2></div>
      <p class="favorites-intro"><?php esc_html_e('Top 5 episodes ranked by thumbs up.', 'stardust-broadcast'); ?></p>
      <?php if ($episodes): ?>
        <ol class="listener-favorites-list">
          <?php foreach ($episodes as $index => $episode):
              $post_id = (int) $episode['post_id'];
              $series = stardust_series_label($post_id);
          ?>
            <li>
              <span class="favorite-rank"><?php echo esc_html((string) ($index + 1)); ?></span>
              <div class="favorite-details">
                <small><?php echo esc_html($series); ?></small>
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
              </div>
              <strong class="favorite-likes"><span aria-hidden="true">👍</span> <?php echo esc_html(number_format_i18n((int) $episode['likes'])); ?></strong>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php else: ?>
        <div class="favorites-empty">
          <span aria-hidden="true">👍</span>
          <p><?php esc_html_e('No listener votes yet. Rate an episode to begin the chart!', 'stardust-broadcast'); ?></p>
        </div>
      <?php endif; ?>
    </section>
    <?php
    return (string) ob_get_clean();
}
