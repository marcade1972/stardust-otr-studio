<?php
/**
 * Template Name: CBS Radio Mystery Theater Episode Library
 * Template Post Type: page
 *
 * Searchable library generated recursively from MP3 files stored in
 * /cbsrmt/<year>/ folders.
 *
 * @package Stardust_Broadcast
 */

if (!defined('ABSPATH')) { exit; }

function stardust_cbsrmt_library_directory(): string {
    $document_root = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : '';
    $candidates = [
        trailingslashit(ABSPATH) . 'cbsrmt',
        trailingslashit(dirname(untrailingslashit(ABSPATH))) . 'cbsrmt',
    ];
    if ($document_root !== '') {
        $candidates[] = trailingslashit($document_root) . 'cbsrmt';
    }
    $upload_dir = wp_get_upload_dir();
    if (!empty($upload_dir['basedir'])) {
        $candidates[] = trailingslashit((string) $upload_dir['basedir']) . 'cbsrmt';
    }
    $candidates = array_values(array_unique(array_map('wp_normalize_path', $candidates)));
    foreach ($candidates as $candidate) {
        if (is_dir($candidate) && is_readable($candidate)) {
            return untrailingslashit($candidate);
        }
    }
    return '';
}

function stardust_cbsrmt_pretty_title(string $value): string {
    $value = preg_replace('/\.(mp3)$/i', '', $value);
    $value = preg_replace('/^(cbs[ _-]*radio[ _-]*mystery[ _-]*theater|cbsrmt)[ _-]*/i', '', (string) $value);
    $value = str_replace(['_', '.'], ' ', (string) $value);
    $value = preg_replace('/\s*-\s*/', ' ', (string) $value);
    $value = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', (string) $value);
    $value = preg_replace('/\s+/', ' ', (string) $value);
    $value = trim((string) $value, " \t\n\r\0\x0B-");
    if ($value === '') { return ''; }
    $title = ucwords(strtolower($value));
    $small_words = ['A','An','And','As','At','But','By','For','From','In','Into','Nor','Of','On','Or','Over','The','To','Up','With'];
    foreach ($small_words as $word) {
        $title = preg_replace('/\b' . preg_quote($word, '/') . '\b/', strtolower($word), $title);
    }
    $title = ucfirst((string) $title);
    $title = preg_replace('/\bCbs\b/', 'CBS', (string) $title);
    $title = preg_replace('/\bDr\b/', 'Dr.', (string) $title);
    $title = preg_replace('/\bMr\b/', 'Mr.', (string) $title);
    $title = preg_replace('/\bMrs\b/', 'Mrs.', (string) $title);
    return trim((string) $title);
}

function stardust_cbsrmt_parse_date(string $raw, string $fallback_year = ''): array {
    $digits = preg_replace('/\D+/', '', $raw);
    $year = 0; $month = 0; $day = 0;
    if (strlen($digits) === 8) {
        $year = (int) substr($digits, 0, 4);
        $month = (int) substr($digits, 4, 2);
        $day = (int) substr($digits, 6, 2);
    } elseif (strlen($digits) === 6) {
        $yy = (int) substr($digits, 0, 2);
        $year = $yy >= 30 ? 1900 + $yy : 2000 + $yy;
        $month = (int) substr($digits, 2, 2);
        $day = (int) substr($digits, 4, 2);
    } elseif ($fallback_year !== '' && preg_match('/^(19|20)\d{2}$/', $fallback_year)) {
        $year = (int) $fallback_year;
    }
    if ($year && $month && $day && checkdate($month, $day, $year)) {
        $timestamp = mktime(12, 0, 0, $month, $day, $year);
        return [wp_date(get_option('date_format'), $timestamp), sprintf('%04d%02d%02d', $year, $month, $day), (string) $year];
    }
    return ['', $year ? sprintf('%04d9999', $year) : '99999999', $year ? (string) $year : $fallback_year];
}

function stardust_cbsrmt_parse_file(string $filename, string $relative_path, string $folder_year): array {
    $stem = pathinfo($filename, PATHINFO_FILENAME);
    $work = preg_replace('/^(cbs[ _-]*radio[ _-]*mystery[ _-]*theater|cbsrmt)[ _-]*/i', '', $stem);
    $episode = '';
    $raw_date = '';
    $title_part = (string) $work;

    $patterns = [
        '/^(\d{8}|\d{6})[_ -]+(?:ep(?:isode)?[_ -]*)?(\d{1,4})[_ -]+(.+)$/i' => 'date_episode',
        '/^(?:ep(?:isode)?[_ -]*)?(\d{1,4})[_ -]+(\d{8}|\d{6})[_ -]+(.+)$/i' => 'episode_date',
        '/^(?:ep(?:isode)?[_ -]*)?(\d{1,4})[_ -]+(.+)$/i' => 'episode',
    ];
    foreach ($patterns as $pattern => $kind) {
        if (preg_match($pattern, (string) $work, $m)) {
            if ($kind === 'date_episode') { $raw_date = $m[1]; $episode = $m[2]; $title_part = $m[3]; }
            elseif ($kind === 'episode_date') { $episode = $m[1]; $raw_date = $m[2]; $title_part = $m[3]; }
            else { $episode = $m[1]; $title_part = $m[2]; }
            break;
        }
    }

    // Remove a date left at either edge of the title when filenames use an unusual order.
    if ($raw_date === '' && preg_match('/(?:^|[_ -])(\d{8}|\d{6})(?:[_ -]|$)/', $title_part, $dm)) {
        $raw_date = $dm[1];
        $title_part = trim((string) preg_replace('/(?:^|[_ -])' . preg_quote($dm[1], '/') . '(?:[_ -]|$)/', ' ', $title_part, 1));
    }

    $episode = $episode !== '' ? str_pad((string) ((int) $episode), 4, '0', STR_PAD_LEFT) : '';
    list($date, $date_sort, $year) = stardust_cbsrmt_parse_date($raw_date, $folder_year);
    $title = stardust_cbsrmt_pretty_title($title_part);
    if ($title === '') { $title = stardust_cbsrmt_pretty_title($stem); }

    return [
        'filename' => $filename,
        'relative_path' => $relative_path,
        'episode' => $episode,
        'title' => $title !== '' ? $title : $stem,
        'date' => $date,
        'date_sort' => $date_sort,
        'year' => $year,
        'search' => strtolower(trim($episode . ' ' . $title . ' ' . $date . ' ' . $year . ' ' . $relative_path)),
    ];
}

function stardust_cbsrmt_encode_relative_url(string $relative_path): string {
    $parts = preg_split('~[\\\\/]+~', $relative_path);
    $parts = array_map('rawurlencode', array_filter((array) $parts, 'strlen'));
    return implode('/', $parts);
}

function stardust_cbsrmt_scan_library(string $library_directory): array {
    if ($library_directory === '') { return []; }
    $cache_key = 'stardust_cbsrmt_library_' . md5($library_directory);
    if (current_user_can('manage_options') && isset($_GET['refresh-cbsrmt'])) {
        delete_transient($cache_key);
    }
    $cached = get_transient($cache_key);
    if (is_array($cached)) { return $cached; }

    $episodes = [];
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($library_directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file_info) {
            if (!$file_info->isFile() || strtolower($file_info->getExtension()) !== 'mp3') { continue; }
            $full_path = wp_normalize_path($file_info->getPathname());
            $base_path = trailingslashit(wp_normalize_path($library_directory));
            if (strpos($full_path, $base_path) !== 0) { continue; }
            $relative = ltrim(substr($full_path, strlen($base_path)), '/');
            $relative_dir = wp_normalize_path(dirname($relative));
            $folder_year = '';
            if (preg_match('/(?:^|\/)((?:19|20)\d{2})(?:\/|$)/', '/' . $relative_dir . '/', $ym)) {
                $folder_year = $ym[1];
            }
            $episodes[] = stardust_cbsrmt_parse_file($file_info->getFilename(), $relative, $folder_year);
        }
    } catch (UnexpectedValueException $e) {
        return [];
    }

    usort($episodes, static function (array $a, array $b): int {
        if ($a['episode'] !== '' && $b['episode'] !== '' && $a['episode'] !== $b['episode']) {
            return strnatcasecmp((string) $a['episode'], (string) $b['episode']);
        }
        if ($a['date_sort'] !== $b['date_sort']) { return strcmp((string) $a['date_sort'], (string) $b['date_sort']); }
        return strnatcasecmp((string) $a['title'], (string) $b['title']);
    });
    set_transient($cache_key, $episodes, 30 * MINUTE_IN_SECONDS);
    return $episodes;
}

$library_directory = stardust_cbsrmt_library_directory();
$episodes = stardust_cbsrmt_scan_library($library_directory);
$year_counts = [];
foreach ($episodes as $episode) {
    if ($episode['year'] !== '') {
        $year_key = (string) $episode['year'];
        if (!isset($year_counts[$year_key])) { $year_counts[$year_key] = 0; }
        $year_counts[$year_key]++;
    }
}
$years = array_keys($year_counts);
sort($years, SORT_NUMERIC);
$audio_base_url = stardust_archive_url('cbsrmt/');
get_header();
?>
<section class="content-wrap wrap x1-library-page cbsrmt-library-page series-profile-page series-profile-cbsrmt">
    <header class="series-spotlight series-spotlight-mystery">
      <div class="series-spotlight-copy"><p class="eyebrow">Stardust Series Spotlight</p><h1><?php echo esc_html(get_the_title()); ?></h1><blockquote>“Come in… Welcome. I’m E. G. Marshall.”</blockquote><p>Created and produced by Himan Brown, CBS Radio Mystery Theater revived hour-long radio drama for a new generation from 1974 through 1982. Hosted for most of its run by E. G. Marshall, the anthology ranged far beyond mystery into horror, history, science fiction, classics, and the supernatural.</p></div>
      <div class="series-emblem series-emblem-door" aria-hidden="true"><span>⌑</span></div>
    </header>
    <section class="series-facts" aria-label="CBS Radio Mystery Theater series facts"><div><span>Original Run</span><strong>1974–1982</strong></div><div><span>Network</span><strong>CBS Radio</strong></div><div><span>Creator</span><strong>Himan Brown</strong></div><div><span>Archive</span><strong><?php echo esc_html(number_format_i18n(count($episodes))); ?> Episodes</strong></div></section>
    <section class="series-starters"><div><p class="eyebrow">A Great Place to Start</p><h2>The sound of suspense</h2></div><ul><li>The Old Ones Are Hard to Kill</li><li>The House on Chimney Pot Lane</li><li>Zero Hour</li><li>The Edge of the Scalpel</li><li>The Deadly Bride</li></ul></section>
    <section class="series-related"><div class="series-related-heading"><p class="eyebrow">Continue Exploring</p><h2>More Like This Series</h2><p>More mystery, suspense, horror, and supernatural drama from the Stardust archive.</p></div><div class="series-related-grid">
    <?php foreach ([['The Shadow','the-shadow','A legendary crime fighter in tales of mystery and danger.'],['Suspense','suspense','Hollywood stars in gripping tales of terror and suspense.'],['The Whistler','the-whistler','Dark crime stories famous for their ironic endings.'],['Inner Sanctum Mysteries','inner-sanctum-mysteries','Supernatural chills introduced with a creaking door.'],['The Mysterious Traveler','the-mysterious-traveler','Strange journeys into mystery and the unknown.']] as $item): $rp=get_page_by_path($item[1]); $ru=$rp?get_permalink($rp):home_url('/'.$item[1].'/'); ?><a class="series-related-card" href="<?php echo esc_url($ru); ?>"><span>Recommended Series</span><strong><?php echo esc_html($item[0]); ?></strong><p><?php echo esc_html($item[2]); ?></p><b>Explore Series →</b></a><?php endforeach; ?>
    </div></section>

    <?php if ($library_directory === ''): ?>
        <section class="x1-library-notice" role="alert">
            <h2><?php esc_html_e('The CBSRMT folder could not be located.', 'stardust-broadcast'); ?></h2>
            <p><?php esc_html_e('The template is working, but the server could not find a readable /cbsrmt/ directory. The year folders should be inside that directory.', 'stardust-broadcast'); ?></p>
        </section>
    <?php elseif (!$episodes): ?>
        <section class="x1-library-notice" role="status">
            <h2><?php esc_html_e('No MP3 files were found.', 'stardust-broadcast'); ?></h2>
            <p><?php esc_html_e('The /cbsrmt/ folder was found, but no MP3 files were discovered in it or its year folders.', 'stardust-broadcast'); ?></p>
        </section>
    <?php else: ?>
        <section class="x1-library-console cbsrmt-library-console" aria-label="<?php esc_attr_e('CBS Radio Mystery Theater episode controls', 'stardust-broadcast'); ?>">
            <div class="cbsrmt-filter-grid">
                <div class="x1-search-group cbsrmt-search-group">
                    <label for="cbsrmt-episode-search"><?php esc_html_e('Search this series', 'stardust-broadcast'); ?></label>
                    <input id="cbsrmt-episode-search" type="search" placeholder="<?php esc_attr_e('Try a title, episode number, date, or year…', 'stardust-broadcast'); ?>" autocomplete="off">
                </div>
                <div class="cbsrmt-year-group">
                    <label for="cbsrmt-year-filter"><?php esc_html_e('Broadcast year', 'stardust-broadcast'); ?></label>
                    <select id="cbsrmt-year-filter">
                        <option value=""><?php esc_html_e('All years', 'stardust-broadcast'); ?></option>
                        <?php foreach ($years as $year): ?><option value="<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php if ($years): ?>
                <nav class="cbsrmt-year-jump" aria-label="<?php esc_attr_e('Jump to a broadcast year', 'stardust-broadcast'); ?>">
                    <span class="cbsrmt-year-jump-label"><?php esc_html_e('Jump to Year', 'stardust-broadcast'); ?></span>
                    <div class="cbsrmt-year-jump-links">
                        <?php foreach ($years as $year): ?>
                            <a href="#cbsrmt-year-<?php echo esc_attr($year); ?>" data-jump-year="<?php echo esc_attr($year); ?>">
                                <strong><?php echo esc_html($year); ?></strong>
                                <span><?php echo esc_html(number_format_i18n((int) $year_counts[$year])); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </nav>
            <?php endif; ?>
            <div class="x1-library-status" id="cbsrmt-library-status" aria-live="polite">
                <?php printf(esc_html__('%s episodes ready to browse.', 'stardust-broadcast'), esc_html(number_format_i18n(count($episodes)))); ?>
            </div>
            <div class="x1-now-playing" id="cbsrmt-now-playing" hidden>
                <p class="eyebrow"><?php esc_html_e('Now Playing', 'stardust-broadcast'); ?></p>
                <h2 id="cbsrmt-now-playing-title"></h2>
                <p id="cbsrmt-now-playing-meta"></p>
                <audio id="cbsrmt-shared-player" controls preload="metadata"></audio>
            </div>
        </section>

        <div class="x1-episode-list cbsrmt-episode-list" id="cbsrmt-episode-list">
            <?php $current_year = ''; ?>
            <?php foreach ($episodes as $index => $episode):
                $audio_url = $audio_base_url . stardust_cbsrmt_encode_relative_url((string) $episode['relative_path']);
                $episode_year = (string) $episode['year'];
                if ($episode_year !== '' && $episode_year !== $current_year):
                    $current_year = $episode_year;
            ?>
                <header class="cbsrmt-year-divider" id="cbsrmt-year-<?php echo esc_attr($episode_year); ?>" data-year-divider="<?php echo esc_attr($episode_year); ?>">
                    <div>
                        <span><?php esc_html_e('Broadcast Year', 'stardust-broadcast'); ?></span>
                        <h2><?php echo esc_html($episode_year); ?></h2>
                    </div>
                    <p><?php printf(esc_html(_n('%s broadcast this year', '%s broadcasts this year', (int) $year_counts[$episode_year], 'stardust-broadcast')), esc_html(number_format_i18n((int) $year_counts[$episode_year]))); ?></p>
                </header>
            <?php endif; ?>
                <article class="x1-episode-row cbsrmt-episode-row" data-search="<?php echo esc_attr((string) $episode['search']); ?>" data-year="<?php echo esc_attr($episode_year); ?>">
                    <div class="x1-episode-number">
                        <span><?php esc_html_e('Episode', 'stardust-broadcast'); ?></span>
                        <strong><?php echo esc_html($episode['episode'] !== '' ? (string) $episode['episode'] : (string) ($index + 1)); ?></strong>
                    </div>
                    <div class="x1-episode-details">
                        <h2><?php echo esc_html((string) $episode['title']); ?></h2>
                        <p><?php echo esc_html(trim(($episode['date'] !== '' ? (string) $episode['date'] : '') . (($episode['date'] !== '' && $episode['year'] !== '') ? ' · ' : '') . (($episode['date'] === '' && $episode['year'] !== '') ? (string) $episode['year'] : ''))); ?></p>
                    </div>
                    <div class="episode-actions"><button class="x1-play-button cbsrmt-play-button" type="button"
                        data-audio="<?php echo esc_url($audio_url); ?>"
                        data-title="<?php echo esc_attr((string) $episode['title']); ?>"
                        data-meta="<?php echo esc_attr(trim(($episode['episode'] !== '' ? __('Episode ', 'stardust-broadcast') . $episode['episode'] : '') . ($episode['date'] !== '' ? ' · ' . $episode['date'] : ($episode['year'] !== '' ? ' · ' . $episode['year'] : '')))); ?>">
                        <span aria-hidden="true">▶</span> <?php esc_html_e('Play', 'stardust-broadcast'); ?>
                    </button><div class="episode-reactions" data-reaction-key="<?php echo esc_attr('cbsrmt|' . (string) $episode['relative_path']); ?>" role="group" aria-label="Rate this episode"><button class="episode-reaction" type="button" data-reaction="up" aria-label="Thumbs up" aria-pressed="false">👍</button><button class="episode-reaction" type="button" data-reaction="down" aria-label="Thumbs down" aria-pressed="false">👎</button></div></div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="x1-no-results" id="cbsrmt-no-results" hidden><?php esc_html_e('No CBS Radio Mystery Theater episodes match those filters.', 'stardust-broadcast'); ?></p>
    <?php endif; ?>
</section>

<?php if ($episodes): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var search = document.getElementById('cbsrmt-episode-search');
    var yearFilter = document.getElementById('cbsrmt-year-filter');
    var list = document.getElementById('cbsrmt-episode-list');
    var status = document.getElementById('cbsrmt-library-status');
    var noResults = document.getElementById('cbsrmt-no-results');
    var player = document.getElementById('cbsrmt-shared-player');
    var nowPlaying = document.getElementById('cbsrmt-now-playing');
    var nowTitle = document.getElementById('cbsrmt-now-playing-title');
    var nowMeta = document.getElementById('cbsrmt-now-playing-meta');
    if (!search || !yearFilter || !list || !player) return;
    var rows = Array.prototype.slice.call(list.querySelectorAll('.cbsrmt-episode-row'));
    var dividers = Array.prototype.slice.call(list.querySelectorAll('.cbsrmt-year-divider'));
    var jumpLinks = Array.prototype.slice.call(document.querySelectorAll('[data-jump-year]'));
    var buttons = Array.prototype.slice.call(list.querySelectorAll('.cbsrmt-play-button'));

    function applyFilters() {
        var query = search.value.trim().toLowerCase();
        var year = yearFilter.value;
        var visible = 0;
        rows.forEach(function (row) {
            var textMatch = query === '' || (row.getAttribute('data-search') || '').indexOf(query) !== -1;
            var yearMatch = year === '' || row.getAttribute('data-year') === year;
            var matches = textMatch && yearMatch;
            row.hidden = !matches;
            if (matches) visible += 1;
        });
        dividers.forEach(function (divider) {
            var dividerYear = divider.getAttribute('data-year-divider') || '';
            var hasVisibleRows = rows.some(function (row) {
                return !row.hidden && row.getAttribute('data-year') === dividerYear;
            });
            divider.hidden = !hasVisibleRows;
        });
        jumpLinks.forEach(function (link) {
            var linkYear = link.getAttribute('data-jump-year') || '';
            var hasVisibleRows = rows.some(function (row) {
                return !row.hidden && row.getAttribute('data-year') === linkYear;
            });
            link.classList.toggle('is-unavailable', !hasVisibleRows);
            link.setAttribute('aria-disabled', hasVisibleRows ? 'false' : 'true');
        });
        status.textContent = visible.toLocaleString() + (visible === 1 ? ' episode shown.' : ' episodes shown.');
        noResults.hidden = visible !== 0;
    }
    search.addEventListener('input', applyFilters);
    yearFilter.addEventListener('change', applyFilters);
    jumpLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (link.classList.contains('is-unavailable')) {
                event.preventDefault();
                return;
            }
            var target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', link.getAttribute('href'));
            }
        });
    });

    function resetButtons() {
        buttons.forEach(function (candidate) {
            candidate.classList.remove('is-playing');
            candidate.innerHTML = '<span aria-hidden="true">▶</span> Play';
        });
    }
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            resetButtons();
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
    player.addEventListener('ended', resetButtons);
});
</script>
<?php endif; ?>
<?php get_footer(); ?>
