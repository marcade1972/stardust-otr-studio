<?php
/**
 * Template Name: X Minus One Episode Library
 * Template Post Type: page
 *
 * Searchable library generated from MP3 files stored in /x-1/.
 *
 * @package Stardust_Broadcast
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Locate the public /x-1/ directory on common WordPress hosting layouts.
 *
 * @return string Empty when no readable directory can be found.
 */
function stardust_x1_library_directory(): string {
    $document_root = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : '';
    $candidates = [
        trailingslashit(ABSPATH) . 'x-1',
        trailingslashit(dirname(untrailingslashit(ABSPATH))) . 'x-1',
    ];

    if ($document_root !== '') {
        $candidates[] = trailingslashit($document_root) . 'x-1';
    }

    $upload_dir = wp_get_upload_dir();
    if (!empty($upload_dir['basedir'])) {
        $candidates[] = trailingslashit((string) $upload_dir['basedir']) . 'x-1';
    }

    $candidates = array_values(array_unique(array_map('wp_normalize_path', $candidates)));
    foreach ($candidates as $candidate) {
        if (is_dir($candidate) && is_readable($candidate)) {
            return untrailingslashit($candidate);
        }
    }

    return '';
}

/**
 * Convert an X Minus One MP3 filename into display information.
 * Expected example: 550714_009_Dr_Grimshaws_Sanitorium.mp3
 *
 * @param string $filename MP3 filename.
 * @return array<string,string|int>
 */
function stardust_x1_parse_filename(string $filename): array {
    $stem = pathinfo($filename, PATHINFO_FILENAME);
    $date = '';
    $date_sort = '99999999';
    $episode = '';
    $title_stem = $stem;

    if (preg_match('/^(\d{6})[_\- ]+(\d{1,4})[_\- ]+(.+)$/', $stem, $matches)) {
        $raw_date = $matches[1];
        $episode = str_pad($matches[2], 3, '0', STR_PAD_LEFT);
        $title_stem = $matches[3];

        $yy = (int) substr($raw_date, 0, 2);
        $year = $yy >= 30 ? 1900 + $yy : 2000 + $yy;
        $month = (int) substr($raw_date, 2, 2);
        $day = (int) substr($raw_date, 4, 2);
        if (checkdate($month, $day, $year)) {
            $timestamp = mktime(12, 0, 0, $month, $day, $year);
            $date = wp_date(get_option('date_format'), $timestamp);
            $date_sort = sprintf('%04d%02d%02d', $year, $month, $day);
        }
    } elseif (preg_match('/^(\d{1,4})[_\- ]+(.+)$/', $stem, $matches)) {
        $episode = str_pad($matches[1], 3, '0', STR_PAD_LEFT);
        $title_stem = $matches[2];
    }

    $title = preg_replace('/[_\-]+/', ' ', $title_stem);
    $title = preg_replace('/\s+/', ' ', (string) $title);
    $title = trim((string) $title);
    $title = ucwords(strtolower($title));

    // Restore common title punctuation and abbreviations without changing source files.
    $title = preg_replace('/\bDr\b/', 'Dr.', $title);
    $title = preg_replace('/\bMr\b/', 'Mr.', $title);
    $title = preg_replace('/\bMrs\b/', 'Mrs.', $title);

    return [
        'filename' => $filename,
        'episode' => $episode,
        'title' => $title !== '' ? $title : $stem,
        'date' => $date,
        'date_sort' => $date_sort,
        'search' => strtolower(trim($episode . ' ' . $title . ' ' . $date . ' ' . $filename)),
    ];
}

$library_directory = stardust_x1_library_directory();
$episodes = [];

if ($library_directory !== '') {
    $files = scandir($library_directory);
    if (is_array($files)) {
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') { continue; }
            $full_path = $library_directory . DIRECTORY_SEPARATOR . $file;
            if (!is_file($full_path) || strtolower((string) pathinfo($file, PATHINFO_EXTENSION)) !== 'mp3') { continue; }
            $episodes[] = stardust_x1_parse_filename($file);
        }
    }
}

usort($episodes, static function (array $a, array $b): int {
    if ($a['date_sort'] !== $b['date_sort']) {
        return strcmp((string) $a['date_sort'], (string) $b['date_sort']);
    }
    if ($a['episode'] !== $b['episode']) {
        return strnatcasecmp((string) $a['episode'], (string) $b['episode']);
    }
    return strnatcasecmp((string) $a['title'], (string) $b['title']);
});

$audio_base_url = stardust_archive_url('x-1/');
get_header();
?>
<section class="content-wrap wrap x1-library-page series-profile-page series-profile-x1">
    <header class="series-spotlight series-spotlight-scifi">
      <div class="series-spotlight-copy"><p class="eyebrow">Stardust Series Spotlight</p><h1><?php echo esc_html(get_the_title()); ?></h1><blockquote>“Countdown for blastoff… X minus five, four, three, two… X minus one… Fire!”</blockquote><p>X Minus One brought the finest modern science-fiction stories to NBC radio from 1955 through 1958. Evolving from Dimension X, it adapted work by leading writers including Ray Bradbury, Isaac Asimov, Robert A. Heinlein, Philip K. Dick, Frederik Pohl, and Robert Sheckley.</p></div>
      <div class="series-emblem series-emblem-rocket" aria-hidden="true"><span>✦</span></div>
    </header>
    <section class="series-facts" aria-label="X Minus One series facts"><div><span>Original Run</span><strong>1955–1958</strong></div><div><span>Network</span><strong>NBC</strong></div><div><span>Genre</span><strong>Science Fiction</strong></div><div><span>Archive</span><strong><?php echo esc_html(number_format_i18n(count($episodes))); ?> Episodes</strong></div></section>
    <section class="series-starters"><div><p class="eyebrow">A Great Place to Start</p><h2>Begin the countdown</h2></div><ul><li>Mars Is Heaven</li><li>The Tunnel Under the World</li><li>Universe</li><li>Nightfall</li><li>The Green Hills of Earth</li></ul></section>
    <section class="series-related"><div class="series-related-heading"><p class="eyebrow">Continue Exploring</p><h2>More Like This Series</h2><p>More imaginative journeys through time, space, and the unknown.</p></div><div class="series-related-grid">
    <?php foreach ([['Dimension X','dimension-x','The groundbreaking NBC series that came before X Minus One.'],['Escape','escape','Adventure, danger, and speculative tales from distant worlds.'],['Quiet, Please','quiet-please','Thoughtful fantasy, horror, and science fiction.'],['CBS Radio Mystery Theater','cbs-radio-mystery-theater','Later radio drama with mystery, horror, and science-fiction stories.']] as $item): $rp=get_page_by_path($item[1]); $ru=$rp?get_permalink($rp):home_url('/'.$item[1].'/'); ?><a class="series-related-card" href="<?php echo esc_url($ru); ?>"><span>Recommended Series</span><strong><?php echo esc_html($item[0]); ?></strong><p><?php echo esc_html($item[2]); ?></p><b>Explore Series →</b></a><?php endforeach; ?>
    </div></section>

    <?php if ($library_directory === ''): ?>
        <section class="x1-library-notice" role="alert">
            <h2><?php esc_html_e('The X Minus One folder could not be located.', 'stardust-broadcast'); ?></h2>
            <p><?php esc_html_e('The page template is working, but the server could not find a readable /x-1/ directory. Confirm that the MP3 folder is located beside the WordPress folder or in the website document root.', 'stardust-broadcast'); ?></p>
            <?php if (current_user_can('manage_options')): ?>
                <p><strong><?php esc_html_e('Administrator note:', 'stardust-broadcast'); ?></strong> <?php esc_html_e('Expected common paths include public_html/x-1 or the folder directly beside the WordPress installation.', 'stardust-broadcast'); ?></p>
            <?php endif; ?>
        </section>
    <?php elseif (!$episodes): ?>
        <section class="x1-library-notice" role="status">
            <h2><?php esc_html_e('No MP3 files were found.', 'stardust-broadcast'); ?></h2>
            <p><?php esc_html_e('The /x-1/ folder was found, but it currently contains no files ending in .mp3.', 'stardust-broadcast'); ?></p>
        </section>
    <?php else: ?>
        <section class="x1-library-console" aria-label="<?php esc_attr_e('X Minus One episode controls', 'stardust-broadcast'); ?>">
            <div class="x1-search-group">
                <label for="x1-episode-search"><?php esc_html_e('Search this series', 'stardust-broadcast'); ?></label>
                <input id="x1-episode-search" type="search" placeholder="<?php esc_attr_e('Try “Mars,” “009,” or “1955”…', 'stardust-broadcast'); ?>" autocomplete="off">
            </div>
            <div class="x1-library-status" id="x1-library-status" aria-live="polite">
                <?php printf(esc_html__('%s episodes ready to browse.', 'stardust-broadcast'), esc_html(number_format_i18n(count($episodes)))); ?>
            </div>
            <div class="x1-now-playing" id="x1-now-playing" hidden>
                <p class="eyebrow"><?php esc_html_e('Now Playing', 'stardust-broadcast'); ?></p>
                <h2 id="x1-now-playing-title"></h2>
                <p id="x1-now-playing-meta"></p>
                <audio id="x1-shared-player" controls preload="metadata"></audio>
            </div>
        </section>

        <div class="x1-episode-list" id="x1-episode-list">
            <?php foreach ($episodes as $index => $episode):
                $audio_url = $audio_base_url . rawurlencode((string) $episode['filename']);
            ?>
                <article class="x1-episode-row" data-search="<?php echo esc_attr((string) $episode['search']); ?>">
                    <div class="x1-episode-number">
                        <span><?php esc_html_e('Episode', 'stardust-broadcast'); ?></span>
                        <strong><?php echo esc_html($episode['episode'] !== '' ? (string) $episode['episode'] : (string) ($index + 1)); ?></strong>
                    </div>
                    <div class="x1-episode-details">
                        <h2><?php echo esc_html((string) $episode['title']); ?></h2>
                        <?php if ($episode['date'] !== ''): ?><p><?php echo esc_html((string) $episode['date']); ?></p><?php endif; ?>
                    </div>
                    <div class="episode-actions"><button class="x1-play-button" type="button"
                        data-audio="<?php echo esc_url($audio_url); ?>"
                        data-title="<?php echo esc_attr((string) $episode['title']); ?>"
                        data-meta="<?php echo esc_attr(trim(($episode['episode'] !== '' ? __('Episode ', 'stardust-broadcast') . $episode['episode'] : '') . ($episode['date'] !== '' ? ' · ' . $episode['date'] : ''))); ?>">
                        <span aria-hidden="true">▶</span> <?php esc_html_e('Play', 'stardust-broadcast'); ?>
                    </button><div class="episode-reactions" data-reaction-key="<?php echo esc_attr('x-minus-one|' . (string) $episode['filename']); ?>" role="group" aria-label="Rate this episode"><button class="episode-reaction" type="button" data-reaction="up" aria-label="Thumbs up" aria-pressed="false">👍</button><button class="episode-reaction" type="button" data-reaction="down" aria-label="Thumbs down" aria-pressed="false">👎</button></div></div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="x1-no-results" id="x1-no-results" hidden><?php esc_html_e('No X Minus One episodes match that search.', 'stardust-broadcast'); ?></p>
    <?php endif; ?>
</section>

<?php if ($episodes): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var search = document.getElementById('x1-episode-search');
    var list = document.getElementById('x1-episode-list');
    var status = document.getElementById('x1-library-status');
    var noResults = document.getElementById('x1-no-results');
    var player = document.getElementById('x1-shared-player');
    var nowPlaying = document.getElementById('x1-now-playing');
    var nowTitle = document.getElementById('x1-now-playing-title');
    var nowMeta = document.getElementById('x1-now-playing-meta');
    if (!search || !list || !player) return;

    var rows = Array.prototype.slice.call(list.querySelectorAll('.x1-episode-row'));
    var buttons = Array.prototype.slice.call(list.querySelectorAll('.x1-play-button'));

    search.addEventListener('input', function () {
        var query = search.value.trim().toLowerCase();
        var visible = 0;
        rows.forEach(function (row) {
            var matches = query === '' || (row.getAttribute('data-search') || '').indexOf(query) !== -1;
            row.hidden = !matches;
            if (matches) visible += 1;
        });
        status.textContent = query === ''
            ? rows.length.toLocaleString() + ' episodes ready to browse.'
            : visible.toLocaleString() + (visible === 1 ? ' episode matches your search.' : ' episodes match your search.');
        noResults.hidden = visible !== 0;
    });

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            buttons.forEach(function (candidate) {
                candidate.classList.remove('is-playing');
                candidate.innerHTML = '<span aria-hidden="true">▶</span> Play';
            });
            button.classList.add('is-playing');
            button.innerHTML = '<span aria-hidden="true">❚❚</span> Playing';
            player.src = button.getAttribute('data-audio') || '';
            nowTitle.textContent = button.getAttribute('data-title') || '';
            nowMeta.textContent = button.getAttribute('data-meta') || '';
            nowPlaying.hidden = false;
            player.play().catch(function () {});
            nowPlaying.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    player.addEventListener('ended', function () {
        buttons.forEach(function (button) {
            button.classList.remove('is-playing');
            button.innerHTML = '<span aria-hidden="true">▶</span> Play';
        });
    });
});
</script>
<?php endif; ?>
<?php get_footer(); ?>
