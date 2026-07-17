<?php
/**
 * Template Name: Dragnet Episode Library
 * Template Post Type: page
 *
 * Searchable library generated recursively from MP3 files in /dragnet/.
 *
 * @package Stardust_Broadcast
 */
if (!defined('ABSPATH')) { exit; }

function stardust_dragnet_library_directory(): string {
    $root = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : '';
    $folder_names = ['dragnet', 'dragnet-radio', 'dragnetradio'];
    $candidates = [];
    foreach ($folder_names as $folder) {
        $candidates[] = trailingslashit(ABSPATH) . $folder;
        $candidates[] = trailingslashit(dirname(untrailingslashit(ABSPATH))) . $folder;
        if ($root !== '') { $candidates[] = trailingslashit($root) . $folder; }
    }
    $uploads = wp_get_upload_dir();
    if (!empty($uploads['basedir'])) {
        foreach ($folder_names as $folder) { $candidates[] = trailingslashit((string) $uploads['basedir']) . $folder; }
    }
    foreach (array_unique(array_map('wp_normalize_path', $candidates)) as $candidate) {
        if (is_dir($candidate) && is_readable($candidate)) { return untrailingslashit($candidate); }
    }
    return '';
}

function stardust_dragnet_pretty_title(string $value): string {
    $value = preg_replace('/\.mp3$/i', '', $value);
    $value = preg_replace('/^(dragnet|dragnet-radio)[ _-]*/i', '', (string) $value);
    $value = preg_replace('/[_\.]+/', ' ', (string) $value);
    $value = preg_replace('/\s*-\s*/', ' ', (string) $value);
    $value = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', (string) $value);
    $value = trim((string) preg_replace('/\s+/', ' ', (string) $value));
    if ($value === '') { return ''; }
    $title = ucwords(strtolower($value));
    foreach (['A','An','And','As','At','But','By','For','From','In','Into','Nor','Of','On','Or','The','To','With'] as $word) {
        $title = preg_replace('/\b' . $word . '\b/', strtolower($word), (string) $title);
    }
    return ucfirst((string) $title);
}

function stardust_dragnet_parse_date(string $raw, string $fallback_year = ''): array {
    $digits = preg_replace('/\D+/', '', $raw);
    $year = 0; $month = 0; $day = 0;
    if (strlen($digits) === 8) {
        $year = (int) substr($digits, 0, 4); $month = (int) substr($digits, 4, 2); $day = (int) substr($digits, 6, 2);
    } elseif (strlen($digits) === 6) {
        $yy = (int) substr($digits, 0, 2); $year = $yy >= 30 ? 1900 + $yy : 2000 + $yy;
        $month = (int) substr($digits, 2, 2); $day = (int) substr($digits, 4, 2);
    } elseif (preg_match('/^(19|20)\d{2}$/', $fallback_year)) { $year = (int) $fallback_year; }
    if ($year && $month && $day && checkdate($month, $day, $year)) {
        $ts = mktime(12, 0, 0, $month, $day, $year);
        return [wp_date(get_option('date_format'), $ts), sprintf('%04d%02d%02d', $year, $month, $day), (string) $year];
    }
    return ['', $year ? sprintf('%04d9999', $year) : '99999999', $year ? (string) $year : $fallback_year];
}

function stardust_dragnet_parse_file(string $filename, string $relative, string $folder_year): array {
    $stem = pathinfo($filename, PATHINFO_FILENAME);
    $work = preg_replace('/^(dragnet|dragnet-radio)[ _-]*/i', '', $stem);
    $date_raw = ''; $episode = ''; $title_part = (string) $work;
    $patterns = [
        '/^(\d{8}|\d{6})[_ -]+(?:ep(?:isode)?[_ -]*)?(\d{1,4})[_ -]+(.+)$/i' => 'de',
        '/^(?:ep(?:isode)?[_ -]*)?(\d{1,4})[_ -]+(\d{8}|\d{6})[_ -]+(.+)$/i' => 'ed',
        '/^(\d{8}|\d{6})[_ -]+(.+)$/i' => 'd',
        '/^(?:ep(?:isode)?[_ -]*)?(\d{1,4})[_ -]+(.+)$/i' => 'e',
    ];
    foreach ($patterns as $pattern => $kind) {
        if (!preg_match($pattern, (string) $work, $m)) { continue; }
        if ($kind === 'de') { $date_raw=$m[1]; $episode=$m[2]; $title_part=$m[3]; }
        elseif ($kind === 'ed') { $episode=$m[1]; $date_raw=$m[2]; $title_part=$m[3]; }
        elseif ($kind === 'd') { $date_raw=$m[1]; $title_part=$m[2]; }
        else { $episode=$m[1]; $title_part=$m[2]; }
        break;
    }
    list($date, $sort, $year) = stardust_dragnet_parse_date($date_raw, $folder_year);
    $title = stardust_dragnet_pretty_title($title_part);
    return [
        'filename'=>$filename, 'relative'=>$relative,
        'episode'=>$episode !== '' ? str_pad((string)((int)$episode), 3, '0', STR_PAD_LEFT) : '',
        'title'=>$title !== '' ? $title : $stem, 'date'=>$date, 'date_sort'=>$sort, 'year'=>$year,
        'search'=>strtolower(trim($episode . ' ' . $title . ' ' . $date . ' ' . $year . ' ' . $relative)),
    ];
}

function stardust_dragnet_scan(string $directory): array {
    if ($directory === '') { return []; }
    $cache_key = 'stardust_dragnet_library_' . md5($directory);
    if (current_user_can('manage_options') && isset($_GET['refresh-dragnet'])) { delete_transient($cache_key); }
    $cached = get_transient($cache_key); if (is_array($cached)) { return $cached; }
    $episodes = [];
    try {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'mp3') { continue; }
            $full = wp_normalize_path($file->getPathname()); $base = trailingslashit(wp_normalize_path($directory));
            if (strpos($full, $base) !== 0) { continue; }
            $relative = ltrim(substr($full, strlen($base)), '/'); $folder_year = '';
            if (preg_match('/(?:^|\/)((?:19|20)\d{2})(?:\/|$)/', '/' . dirname($relative) . '/', $ym)) { $folder_year = $ym[1]; }
            $episodes[] = stardust_dragnet_parse_file($file->getFilename(), $relative, $folder_year);
        }
    } catch (UnexpectedValueException $e) { return []; }
    usort($episodes, static function(array $a, array $b): int {
        if ($a['date_sort'] !== $b['date_sort']) { return strcmp((string)$a['date_sort'], (string)$b['date_sort']); }
        if ($a['episode'] !== $b['episode']) { return strnatcasecmp((string)$a['episode'], (string)$b['episode']); }
        return strnatcasecmp((string)$a['title'], (string)$b['title']);
    });
    set_transient($cache_key, $episodes, 30 * MINUTE_IN_SECONDS); return $episodes;
}

function stardust_dragnet_url_path(string $relative): string {
    return implode('/', array_map('rawurlencode', array_filter((array) preg_split('~[\\\\/]+~', $relative), 'strlen')));
}


/**
 * The public archive is served from /dragnet/.
 * The server folder may be discovered in more than one filesystem location,
 * but its browser-facing URL is fixed and must not be inferred from the page slug.
 */
function stardust_dragnet_audio_base_url(string $directory): string {
    return stardust_archive_url('dragnet/');
}

$directory = stardust_dragnet_library_directory();
$episodes = stardust_dragnet_scan($directory); $year_counts = [];
foreach ($episodes as $ep) { if ($ep['year'] !== '') { $year_counts[$ep['year']] = isset($year_counts[$ep['year']]) ? $year_counts[$ep['year']] + 1 : 1; } }
$years = array_keys($year_counts); sort($years, SORT_NUMERIC); $audio_base = stardust_dragnet_audio_base_url($directory);
$related = [
    ['Yours Truly, Johnny Dollar','yours-truly-johnny-dollar','America’s fabulous freelance insurance investigator follows the facts wherever they lead.'],
    ['The Whistler','the-whistler','Crime, hidden motives, and ironic justice told from the shadows.'],
    ['Suspense','suspense','Radio’s celebrated theater of thrills and psychological tension.'],
    ['The Shadow','the-shadow','A legendary crime fighter confronting danger in the city at night.'],
    ['CBS Radio Mystery Theater','cbs-radio-mystery-theater','A later generation of mystery, crime, and suspense anthology drama.'],
];
get_header();
?>
<section class="content-wrap wrap x1-library-page dragnet-library-page">
  <header class="suspense-spotlight dragnet-spotlight">
    <div class="suspense-spotlight-copy dragnet-spotlight-copy">
      <p class="eyebrow"><?php esc_html_e('Stardust Series Spotlight', 'stardust-broadcast'); ?></p>
      <h1><?php echo esc_html(get_the_title()); ?></h1>
      <blockquote><?php esc_html_e('“The story you are about to hear is true. Only the names have been changed to protect the innocent.”', 'stardust-broadcast'); ?></blockquote>
      <p><?php esc_html_e('Created and produced by Jack Webb, Dragnet brought a spare, documentary style to network radio. Beginning in 1949, Sergeant Joe Friday and his partners followed cases step by step through interviews, evidence, routine police work, and the human consequences behind each crime. Its clipped dialogue, procedural detail, and famous four-note theme helped define the modern police drama.', 'stardust-broadcast'); ?></p>
    </div>
    <div class="suspense-microphone dragnet-badge" aria-hidden="true"><span></span></div>
  </header>

  <section class="suspense-facts dragnet-facts" aria-label="Dragnet series facts">
    <div><span>Radio Run</span><strong>1949–1957</strong></div>
    <div><span>Network</span><strong>NBC</strong></div>
    <div><span>Format</span><strong>Police Procedural</strong></div>
    <div><span>Archive</span><strong><?php echo esc_html(number_format_i18n(count($episodes))); ?> Episodes</strong></div>
  </section>

  <section class="suspense-starters dragnet-starters">
    <div><p class="eyebrow">A Great Place to Start</p><h2>Start with five memorable cases from the files of the Los Angeles police</h2></div>
    <ul><li>The Big Little Jesus</li><li>The Big Thank You</li><li>The Big Cast</li><li>The Big Sorrow</li><li>The Big Saint</li></ul>
  </section>

  <section class="suspense-related dragnet-related" aria-labelledby="dragnet-related-title">
    <div class="suspense-related-heading dragnet-related-heading"><p class="eyebrow">Continue Exploring</p><h2 id="dragnet-related-title">More Like This Series</h2><p>More classic crime, detective, mystery, and suspense radio waiting in the Stardust archive.</p></div>
    <div class="suspense-related-grid dragnet-related-grid">
      <?php foreach ($related as $item): $page = get_page_by_path($item[1]); $url = $page ? get_permalink($page) : home_url('/' . $item[1] . '/'); ?>
      <a class="suspense-related-card dragnet-related-card" href="<?php echo esc_url($url); ?>"><span>Recommended Series</span><strong><?php echo esc_html($item[0]); ?></strong><p><?php echo esc_html($item[2]); ?></p><b>Explore Series →</b></a>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($directory === ''): ?>
  <section class="x1-library-notice" role="alert"><h2>The Dragnet folder could not be located.</h2><p>The template could not find a readable <code>/dragnet/</code> directory.</p></section>
  <?php elseif (!$episodes): ?>
  <section class="x1-library-notice"><h2>No MP3 files were found.</h2><p>The <code>/dragnet/</code> folder exists but contains no MP3 files.</p></section>
  <?php else: ?>
  <section class="x1-library-console dragnet-library-console">
    <div class="x1-search-group"><label for="dragnet-search">Search this series</label><input id="dragnet-search" type="search" placeholder="Try a title, episode number, date, or year…" autocomplete="off"></div>
    <?php if ($years): ?><div class="suspense-year-jump dragnet-year-jump"><span>Jump to Year</span><?php foreach ($years as $year): ?><button type="button" data-year-target="dragnet-year-<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></button><?php endforeach; ?></div><?php endif; ?>
    <div class="x1-library-status" id="dragnet-status"><?php echo esc_html(number_format_i18n(count($episodes))); ?> episodes ready to browse.</div>
    <div class="x1-now-playing" id="dragnet-now-playing" hidden><p class="eyebrow">Now Playing</p><h2 id="dragnet-now-title"></h2><p id="dragnet-now-meta"></p><audio id="dragnet-player" controls preload="metadata"></audio></div>
  </section>

  <div class="x1-episode-list dragnet-episode-list" id="dragnet-list">
    <?php $last_year=''; foreach ($episodes as $index=>$ep): if ($ep['year'] !== '' && $ep['year'] !== $last_year): $last_year=$ep['year']; ?>
      <div class="suspense-year-divider dragnet-year-divider" id="dragnet-year-<?php echo esc_attr($last_year); ?>" data-year="<?php echo esc_attr($last_year); ?>"><span><?php echo esc_html($last_year); ?></span><small><?php echo esc_html(number_format_i18n($year_counts[$last_year])); ?> broadcasts</small></div>
    <?php endif; $url = $audio_base . stardust_dragnet_url_path($ep['relative']); ?>
    <article class="x1-episode-row dragnet-episode-row" data-search="<?php echo esc_attr($ep['search']); ?>" data-year="<?php echo esc_attr($ep['year']); ?>">
      <div class="x1-episode-number"><span>Episode</span><strong><?php echo esc_html($ep['episode'] !== '' ? $ep['episode'] : (string)($index+1)); ?></strong></div>
      <div class="x1-episode-details"><h2><?php echo esc_html($ep['title']); ?></h2><p><?php echo esc_html(trim($ep['date'] . ($ep['year'] !== '' && $ep['date'] === '' ? $ep['year'] : ''))); ?></p></div>
      <div class="episode-actions"><button class="x1-play-button dragnet-play-button" type="button" data-audio="<?php echo esc_url($url); ?>" data-title="<?php echo esc_attr($ep['title']); ?>" data-meta="<?php echo esc_attr(trim(($ep['episode'] !== '' ? 'Episode ' . $ep['episode'] : '') . ($ep['date'] !== '' ? ' • ' . $ep['date'] : ''))); ?>">Play Episode</button><div class="episode-reactions" data-reaction-key="<?php echo esc_attr('dragnet|' . (string) $ep['relative']); ?>" role="group" aria-label="Rate this episode"><button class="episode-reaction" type="button" data-reaction="up" aria-label="Thumbs up" aria-pressed="false">👍</button><button class="episode-reaction" type="button" data-reaction="down" aria-label="Thumbs down" aria-pressed="false">👎</button></div></div>
    </article>
    <?php endforeach; ?>
  </div>
  <p class="x1-no-results" id="dragnet-no-results" hidden>No episodes match that search.</p>
  <?php endif; ?>
</section>
<script>
(function(){
 const search=document.getElementById('dragnet-search'), rows=[...document.querySelectorAll('.dragnet-episode-row')], status=document.getElementById('dragnet-status'), empty=document.getElementById('dragnet-no-results'), player=document.getElementById('dragnet-player'), now=document.getElementById('dragnet-now-playing'), title=document.getElementById('dragnet-now-title'), meta=document.getElementById('dragnet-now-meta');
 function updateDividers(){document.querySelectorAll('.dragnet-year-divider').forEach(d=>{d.hidden=!rows.some(r=>r.dataset.year===d.dataset.year&&!r.hidden);});}
 if(search){search.addEventListener('input',()=>{const q=search.value.toLowerCase().trim();let n=0;rows.forEach(r=>{r.hidden=q!==''&&!r.dataset.search.includes(q);if(!r.hidden)n++;});status.textContent=n+' episode'+(n===1?'':'s')+' shown.';empty.hidden=n!==0;updateDividers();});}
 document.querySelectorAll('.dragnet-play-button').forEach(b=>b.addEventListener('click',()=>{player.pause();player.src=b.dataset.audio;player.load();title.textContent=b.dataset.title;meta.textContent=b.dataset.meta;now.hidden=false;player.play().catch((error)=>{meta.textContent=(b.dataset.meta?b.dataset.meta+' • ':'')+'Audio could not be loaded from '+b.dataset.audio; if(window.console){console.error('Dragnet audio playback failed:', b.dataset.audio, error);}});now.scrollIntoView({behavior:'smooth',block:'center'});}));
 document.querySelectorAll('[data-year-target]').forEach(b=>b.addEventListener('click',()=>{const target=document.getElementById(b.dataset.yearTarget);if(target)target.scrollIntoView({behavior:'smooth',block:'start'});}));
})();
</script>
<?php get_footer(); ?>
