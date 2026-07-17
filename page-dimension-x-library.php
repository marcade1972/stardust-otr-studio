<?php
/**
 * Template Name: Dimension X Episode Library
 * Template Post Type: page
 *
 * Searchable library generated from MP3 files stored in /dimensionx/.
 *
 * @package Stardust_Broadcast
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Locate the public /dimensionx/ directory on common WordPress hosting layouts.
 *
 * @return string Empty when no readable directory can be found.
 */
function stardust_dimensionx_library_directory(): string {
    $document_root = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : '';
    $candidates = [
        trailingslashit(ABSPATH) . 'dimensionx',
        trailingslashit(dirname(untrailingslashit(ABSPATH))) . 'dimensionx',
    ];

    if ($document_root !== '') {
        $candidates[] = trailingslashit($document_root) . 'dimensionx';
    }

    $upload_dir = wp_get_upload_dir();
    if (!empty($upload_dir['basedir'])) {
        $candidates[] = trailingslashit((string) $upload_dir['basedir']) . 'dimensionx';
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
 * Convert a Dimension X MP3 filename into display information.
 * Expected example: 550714_009_Dr_Grimshaws_Sanitorium.mp3
 *
 * @param string $filename MP3 filename.
 * @return array<string,string|int>
 */
function stardust_dimensionx_parse_filename(string $filename): array {
    $stem = pathinfo($filename, PATHINFO_FILENAME);
    $date = '';
    $date_sort = '99999999';
    $raw_date = '';
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

    // Dimension X source filenames often run every title word together. Use the
    // broadcast date to restore the official readable title while preserving the
    // original MP3 filename and URL.
    $known_titles = [
        '500408' => 'The Outer Limit',
        '500415' => 'With Folded Hands',
        '500422' => 'Report on the Barnhouse Effect',
        '500429' => 'No Contact',
        '500506' => 'Knock',
        '500513' => 'Almost Human',
        '500520' => 'The Lost Race',
        '500527' => 'To the Future',
        '500603' => 'The Embassy',
        '500610' => 'The Green Hills of Earth',
        '500617' => 'There Will Come Soft Rains / Zero Hour',
        '500624' => 'Destination Moon',
        '500701' => 'A Logic Named Joe',
        '500707' => 'Mars Is Heaven',
        '500714' => 'The Man in the Moon',
        '500721' => 'Beyond Infinity',
        '500728' => 'The Potters of Firsk',
        '500804' => "Perigi's Wonderful Dolls",
        '500811' => 'The Castaways',
        '500818' => 'The Martian Chronicles',
        '500825' => 'The Parade',
        '500901' => 'The Roads Must Roll',
        '500908' => 'The Outer Limit',
        '500915' => 'Hello Tomorrow',
        '500922' => "Dr. Grimshaw's Sanatorium",
        '500929' => 'And the Moon Be Still as Bright',
        '501028' => 'No Contact',
        '501105' => 'The Professor Was a Thief',
        '501112' => 'Shanghaied',
        '501119' => 'Competition',
        '501126' => 'Universe',
        '501224' => 'The Green Hills of Earth',
        '510107' => 'Mars Is Heaven',
        '510114' => 'The Martian Death March',
        '510603' => 'The Last Objective',
        '510610' => 'Nightmare',
        '510617' => 'Pebble in the Sky',
        '510624' => "Child's Play",
        '510712' => 'Time and Time Again',
        '510719' => 'Dwellers in Silence',
        '510726' => 'Courtesy',
        '510802' => 'Universe',
        '510809' => 'The Veldt',
        '510816' => 'The Vital Factor',
        '510823' => 'Untitled Story',
        '510830' => 'Marionettes, Inc.',
        '510908' => 'First Contact',
        '510915' => 'The Kaleidoscope',
        '510922' => 'Requiem',
        '510929' => 'Nightfall',
    ];

    if ($raw_date !== '' && isset($known_titles[$raw_date])) {
        $title = $known_titles[$raw_date];
    } else {
        // Fallback for any additional files: preserve separators and split camel case.
        $title = preg_replace('/[_\-]+/', ' ', $title_stem);
        $title = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', (string) $title);
        $title = preg_replace('/\s+/', ' ', (string) $title);
        $title = trim((string) $title);
        $title = ucwords(strtolower($title));
        $title = preg_replace('/\bDr\b/', 'Dr.', $title);
        $title = preg_replace('/\bMr\b/', 'Mr.', $title);
        $title = preg_replace('/\bMrs\b/', 'Mrs.', $title);
    }

    return [
        'filename' => $filename,
        'episode' => $episode,
        'title' => $title !== '' ? $title : $stem,
        'date' => $date,
        'date_sort' => $date_sort,
        'search' => strtolower(trim($episode . ' ' . $title . ' ' . $date . ' ' . $filename)),
    ];
}

$library_directory = stardust_dimensionx_library_directory();
$episodes = [];

if ($library_directory !== '') {
    $files = scandir($library_directory);
    if (is_array($files)) {
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') { continue; }
            $full_path = $library_directory . DIRECTORY_SEPARATOR . $file;
            if (!is_file($full_path) || strtolower((string) pathinfo($file, PATHINFO_EXTENSION)) !== 'mp3') { continue; }
            $episodes[] = stardust_dimensionx_parse_filename($file);
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

$audio_base_url = stardust_archive_url('dimensionx/');
get_header();
?>
<section class="content-wrap wrap x1-library-page dx-library-page series-profile-page series-profile-dx">
    <header class="series-spotlight series-spotlight-scifi">
      <div class="series-spotlight-copy"><p class="eyebrow">Stardust Series Spotlight</p><h1><?php echo esc_html(get_the_title()); ?></h1><blockquote>“Adventures in time and space… told in future tense!”</blockquote><p>Broadcast by NBC from 1950 through 1951, Dimension X helped bring serious science fiction to network radio. Its adaptations drew from writers such as Ray Bradbury, Robert A. Heinlein, Isaac Asimov, Kurt Vonnegut, and Clifford D. Simak, and its legacy directly led to X Minus One.</p></div>
      <div class="series-emblem series-emblem-orbit" aria-hidden="true"><span>◉</span></div>
    </header>
    <section class="series-facts" aria-label="Dimension X series facts"><div><span>Original Run</span><strong>1950–1951</strong></div><div><span>Network</span><strong>NBC</strong></div><div><span>Genre</span><strong>Science Fiction</strong></div><div><span>Archive</span><strong><?php echo esc_html(number_format_i18n(count($episodes))); ?> Episodes</strong></div></section>
    <section class="series-starters"><div><p class="eyebrow">A Great Place to Start</p><h2>Enter another dimension</h2></div><ul><li>The Outer Limit</li><li>With Folded Hands</li><li>Report on the Barnhouse Effect</li><li>No Contact</li><li>Mars Is Heaven</li></ul></section>
    <section class="series-related"><div class="series-related-heading"><p class="eyebrow">Continue Exploring</p><h2>More Like This Series</h2><p>Classic radio journeys into science, suspense, and worlds beyond imagination.</p></div><div class="series-related-grid">
    <?php foreach ([['X Minus One','x-minus-one','NBC’s celebrated successor to Dimension X.'],['Escape','escape','High adventure and speculative tales from around the world.'],['Quiet, Please','quiet-please','Atmospheric fantasy, horror, and science fiction.'],['CBS Radio Mystery Theater','cbs-radio-mystery-theater','A later anthology spanning mystery, horror, and science fiction.']] as $item): $rp=get_page_by_path($item[1]); $ru=$rp?get_permalink($rp):home_url('/'.$item[1].'/'); ?><a class="series-related-card" href="<?php echo esc_url($ru); ?>"><span>Recommended Series</span><strong><?php echo esc_html($item[0]); ?></strong><p><?php echo esc_html($item[2]); ?></p><b>Explore Series →</b></a><?php endforeach; ?>
    </div></section>

    <?php if ($library_directory === ''): ?>
        <section class="x1-library-notice dx-library-notice" role="alert">
            <h2><?php esc_html_e('The Dimension X folder could not be located.', 'stardust-broadcast'); ?></h2>
            <p><?php esc_html_e('The page template is working, but the server could not find a readable /dimensionx/ directory. Confirm that the MP3 folder is located beside the WordPress folder or in the website document root.', 'stardust-broadcast'); ?></p>
            <?php if (current_user_can('manage_options')): ?>
                <p><strong><?php esc_html_e('Administrator note:', 'stardust-broadcast'); ?></strong> <?php esc_html_e('Expected common paths include public_html/dimensionx or the folder directly beside the WordPress installation.', 'stardust-broadcast'); ?></p>
            <?php endif; ?>
        </section>
    <?php elseif (!$episodes): ?>
        <section class="x1-library-notice dx-library-notice" role="status">
            <h2><?php esc_html_e('No MP3 files were found.', 'stardust-broadcast'); ?></h2>
            <p><?php esc_html_e('The /dimensionx/ folder was found, but it currently contains no files ending in .mp3.', 'stardust-broadcast'); ?></p>
        </section>
    <?php else: ?>
        <section class="x1-library-console dx-library-console" aria-label="<?php esc_attr_e('Dimension X episode controls', 'stardust-broadcast'); ?>">
            <div class="x1-search-group dx-search-group">
                <label for="dx-episode-search"><?php esc_html_e('Search this series', 'stardust-broadcast'); ?></label>
                <input id="dx-episode-search" type="search" placeholder="<?php esc_attr_e('Try “Mars,” “009,” or “1955”…', 'stardust-broadcast'); ?>" autocomplete="off">
            </div>
            <div class="x1-library-status dx-library-status" id="dx-library-status" aria-live="polite">
                <?php printf(esc_html__('%s episodes ready to browse.', 'stardust-broadcast'), esc_html(number_format_i18n(count($episodes)))); ?>
            </div>
            <div class="x1-now-playing dx-now-playing" id="dx-now-playing" hidden>
                <p class="eyebrow"><?php esc_html_e('Now Playing', 'stardust-broadcast'); ?></p>
                <h2 id="dx-now-playing-title"></h2>
                <p id="dx-now-playing-meta"></p>
                <audio id="dx-shared-player" controls preload="metadata"></audio>
            </div>
        </section>

        <div class="x1-episode-list dx-episode-list" id="dx-episode-list">
            <?php foreach ($episodes as $index => $episode):
                $audio_url = $audio_base_url . rawurlencode((string) $episode['filename']);
            ?>
                <article class="x1-episode-row dx-episode-row" data-search="<?php echo esc_attr((string) $episode['search']); ?>">
                    <div class="x1-episode-number dx-episode-number">
                        <span><?php esc_html_e('Episode', 'stardust-broadcast'); ?></span>
                        <strong><?php echo esc_html($episode['episode'] !== '' ? (string) $episode['episode'] : (string) ($index + 1)); ?></strong>
                    </div>
                    <div class="x1-episode-details dx-episode-details">
                        <h2><?php echo esc_html((string) $episode['title']); ?></h2>
                        <?php if ($episode['date'] !== ''): ?><p><?php echo esc_html((string) $episode['date']); ?></p><?php endif; ?>
                    </div>
                    <div class="episode-actions"><button class="x1-play-button dx-play-button" type="button"
                        data-audio="<?php echo esc_url($audio_url); ?>"
                        data-title="<?php echo esc_attr((string) $episode['title']); ?>"
                        data-meta="<?php echo esc_attr(trim(($episode['episode'] !== '' ? __('Episode ', 'stardust-broadcast') . $episode['episode'] : '') . ($episode['date'] !== '' ? ' · ' . $episode['date'] : ''))); ?>">
                        <span aria-hidden="true">▶</span> <?php esc_html_e('Play', 'stardust-broadcast'); ?>
                    </button><div class="episode-reactions" data-reaction-key="<?php echo esc_attr('dimension-x|' . (string) $episode['filename']); ?>" role="group" aria-label="Rate this episode"><button class="episode-reaction" type="button" data-reaction="up" aria-label="Thumbs up" aria-pressed="false">👍</button><button class="episode-reaction" type="button" data-reaction="down" aria-label="Thumbs down" aria-pressed="false">👎</button></div></div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="x1-no-results dx-no-results" id="dx-no-results" hidden><?php esc_html_e('No Dimension X episodes match that search.', 'stardust-broadcast'); ?></p>
    <?php endif; ?>
</section>

<?php if ($episodes): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var search = document.getElementById('dx-episode-search');
    var list = document.getElementById('dx-episode-list');
    var status = document.getElementById('dx-library-status');
    var noResults = document.getElementById('dx-no-results');
    var player = document.getElementById('dx-shared-player');
    var nowPlaying = document.getElementById('dx-now-playing');
    var nowTitle = document.getElementById('dx-now-playing-title');
    var nowMeta = document.getElementById('dx-now-playing-meta');
    if (!search || !list || !player) return;

    var rows = Array.prototype.slice.call(list.querySelectorAll('.dx-episode-row'));
    var buttons = Array.prototype.slice.call(list.querySelectorAll('.dx-play-button'));

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
