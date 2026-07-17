<?php
/**
 * Template Name: Yours Truly, Johnny Dollar Episode Library
 * Template Post Type: page
 *
 * Searchable library generated recursively from MP3 files in the Johnny Dollar archive.
 *
 * @package Stardust_Broadcast
 */
if (!defined('ABSPATH')) { exit; }

function stardust_johnny_dollar_library_directory(): string {
    $root = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : '';
    $folder_names = ['yours-truly-johnny-dollar', 'yours-truly-johnny-dollar-radio', 'johnny-dollar', 'johnnydollar', 'ytjd'];
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

function stardust_johnny_dollar_pretty_title(string $value): string {
    $value = preg_replace('/\.mp3$/i', '', $value);
    $value = preg_replace('/^(yours[ _-]*truly[ _-]*johnny[ _-]*dollar|johnny[ _-]*dollar|ytjd)[ _-]*/i', '', (string) $value);
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

function stardust_johnny_dollar_parse_date(string $raw, string $fallback_year = ''): array {
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

function stardust_johnny_dollar_parse_file(string $filename, string $relative, string $folder_year): array {
    $stem = pathinfo($filename, PATHINFO_FILENAME);
    $work = preg_replace('/^(yours[ _-]*truly[ _-]*johnny[ _-]*dollar|johnny[ _-]*dollar|ytjd)[ _-]*/i', '', $stem);
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
    list($date, $sort, $year) = stardust_johnny_dollar_parse_date($date_raw, $folder_year);
    $title = stardust_johnny_dollar_pretty_title($title_part);
    return [
        'filename'=>$filename, 'relative'=>$relative,
        'episode'=>$episode !== '' ? str_pad((string)((int)$episode), 3, '0', STR_PAD_LEFT) : '',
        'title'=>$title !== '' ? $title : $stem, 'date'=>$date, 'date_sort'=>$sort, 'year'=>$year,
        'search'=>strtolower(trim($episode . ' ' . $title . ' ' . $date . ' ' . $year . ' ' . $relative)),
    ];
}

function stardust_johnny_dollar_scan(string $directory): array {
    if ($directory === '') { return []; }
    $cache_key = 'stardust_johnny_dollar_library_' . md5($directory);
    if (current_user_can('manage_options') && isset($_GET['refresh-johnny-dollar'])) { delete_transient($cache_key); }
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
            $episodes[] = stardust_johnny_dollar_parse_file($file->getFilename(), $relative, $folder_year);
        }
    } catch (UnexpectedValueException $e) { return []; }
    usort($episodes, static function(array $a, array $b): int {
        if ($a['date_sort'] !== $b['date_sort']) { return strcmp((string)$a['date_sort'], (string)$b['date_sort']); }
        if ($a['episode'] !== $b['episode']) { return strnatcasecmp((string)$a['episode'], (string)$b['episode']); }
        return strnatcasecmp((string)$a['title'], (string)$b['title']);
    });
    set_transient($cache_key, $episodes, 30 * MINUTE_IN_SECONDS); return $episodes;
}

function stardust_johnny_dollar_url_path(string $relative): string {
    return implode('/', array_map('rawurlencode', array_filter((array) preg_split('~[\\\\/]+~', $relative), 'strlen')));
}


/**
 * The public archive is served from the detected Johnny Dollar folder at the domain root.
 * The server folder may use one of several supported names; the detected folder name is also used for the public URL.
 */
function stardust_johnny_dollar_audio_base_url(string $directory): string {
    $folder = basename(wp_normalize_path($directory));
    if ($folder === '' || $folder === '.' || $folder === DIRECTORY_SEPARATOR) { $folder = 'yours-truly-johnny-dollar'; }
    return stardust_archive_url($folder . '/');
}

$directory = stardust_johnny_dollar_library_directory();
$episodes = stardust_johnny_dollar_scan($directory); $year_counts = [];
foreach ($episodes as $ep) { if ($ep['year'] !== '') { $year_counts[$ep['year']] = isset($year_counts[$ep['year']]) ? $year_counts[$ep['year']] + 1 : 1; } }
$years = array_keys($year_counts); sort($years, SORT_NUMERIC); $audio_base = stardust_johnny_dollar_audio_base_url($directory);
$related = [
    ['Dragnet','dragnet','A landmark police procedural built on facts, evidence, and methodical investigation.'],
    ['The Whistler','the-whistler','Crime, hidden motives, and ironic justice told from the shadows.'],
    ['Suspense','suspense','Radio’s celebrated theater of thrills and psychological tension.'],
    ['The Shadow','the-shadow','A legendary crime fighter confronting danger in the city at night.'],
    ['CBS Radio Mystery Theater','cbs-radio-mystery-theater','A later generation of mystery, crime, and suspense anthology drama.'],
];
get_header();
?>
<section class="content-wrap wrap x1-library-page johnny-dollar-library-page">
  <header class="suspense-spotlight johnny-dollar-spotlight">
    <div class="suspense-spotlight-copy johnny-dollar-spotlight-copy">
      <p class="eyebrow"><?php esc_html_e('Stardust Series Spotlight', 'stardust-broadcast'); ?></p>
      <h1><?php echo esc_html(get_the_title()); ?></h1>
      <blockquote><?php esc_html_e('“Expense account submitted by special investigator Johnny Dollar…”', 'stardust-broadcast'); ?></blockquote>
      <p><?php esc_html_e('Yours Truly, Johnny Dollar followed “America’s fabulous freelance insurance investigator” from one suspicious claim to the next. Johnny’s investigations carried him across the country and around the world, where danger, deception, and dry humor were carefully recorded on his itemized expense account. The celebrated five-part serials of the mid-1950s gave the series unusual room for character, atmosphere, and steadily tightening suspense.', 'stardust-broadcast'); ?></p>
    </div>
    <div class="suspense-microphone johnny-dollar-ledger" aria-hidden="true"><span></span></div>
  </header>

  <section class="suspense-facts johnny-dollar-facts" aria-label="Yours Truly, Johnny Dollar series facts">
    <div><span>Radio Run</span><strong>1949–1962</strong></div>
    <div><span>Network</span><strong>CBS</strong></div>
    <div><span>Format</span><strong>Detective Drama</strong></div>
    <div><span>Archive</span><strong><?php echo esc_html(number_format_i18n(count($episodes))); ?> Episodes</strong></div>
  </section>

  <section class="suspense-starters johnny-dollar-starters">
    <div><p class="eyebrow">A Great Place to Start</p><h2>Begin with five memorable expense-account adventures</h2></div>
    <ul><li>The Plantagenet Matter</li><li>The Valentine Matter</li><li>The McCormack Matter</li><li>The Shady Lane Matter</li><li>The Todd Matter</li></ul>
  </section>

  <section class="suspense-related johnny-dollar-related" aria-labelledby="johnny-dollar-related-title">
    <div class="suspense-related-heading johnny-dollar-related-heading"><p class="eyebrow">Continue Exploring</p><h2 id="johnny-dollar-related-title">More Like This Series</h2><p>More classic crime, detective, mystery, and suspense radio waiting in the Stardust archive.</p></div>
    <div class="suspense-related-grid johnny-dollar-related-grid">
      <?php foreach ($related as $item): $page = get_page_by_path($item[1]); $url = $page ? get_permalink($page) : home_url('/' . $item[1] . '/'); ?>
      <a class="suspense-related-card johnny-dollar-related-card" href="<?php echo esc_url($url); ?>"><span>Recommended Series</span><strong><?php echo esc_html($item[0]); ?></strong><p><?php echo esc_html($item[2]); ?></p><b>Explore Series →</b></a>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($directory === ''): ?>
  <section class="x1-library-notice" role="alert"><h2>The Yours Truly, Johnny Dollar folder could not be located.</h2><p>The template could not find a readable <code>/yours-truly-johnny-dollar/</code> directory.</p></section>
  <?php elseif (!$episodes): ?>
  <section class="x1-library-notice"><h2>No MP3 files were found.</h2><p>The <code>/yours-truly-johnny-dollar/</code> folder exists but contains no MP3 files.</p></section>
  <?php else: ?>
  <section class="x1-library-console johnny-dollar-library-console">
    <div class="x1-search-group"><label for="johnny-dollar-search">Search this series</label><input id="johnny-dollar-search" type="search" placeholder="Try a title, episode number, date, or year…" autocomplete="off"></div>
    <?php if ($years): ?><div class="suspense-year-jump johnny-dollar-year-jump"><span>Jump to Year</span><?php foreach ($years as $year): ?><button type="button" data-year-target="johnny-dollar-year-<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></button><?php endforeach; ?></div><?php endif; ?>
    <div class="x1-library-status" id="johnny-dollar-status"><?php echo esc_html(number_format_i18n(count($episodes))); ?> episodes ready to browse.</div>
    <div class="x1-now-playing" id="johnny-dollar-now-playing" hidden><p class="eyebrow">Now Playing</p><h2 id="johnny-dollar-now-title"></h2><p id="johnny-dollar-now-meta"></p><audio id="johnny-dollar-player" controls preload="metadata"></audio></div>
  </section>

  <div class="x1-episode-list johnny-dollar-episode-list" id="johnny-dollar-list">
    <?php $last_year=''; foreach ($episodes as $index=>$ep): if ($ep['year'] !== '' && $ep['year'] !== $last_year): $last_year=$ep['year']; ?>
      <div class="suspense-year-divider johnny-dollar-year-divider" id="johnny-dollar-year-<?php echo esc_attr($last_year); ?>" data-year="<?php echo esc_attr($last_year); ?>"><span><?php echo esc_html($last_year); ?></span><small><?php echo esc_html(number_format_i18n($year_counts[$last_year])); ?> broadcasts</small></div>
    <?php endif; $url = $audio_base . stardust_johnny_dollar_url_path($ep['relative']); ?>
    <article class="x1-episode-row johnny-dollar-episode-row" data-search="<?php echo esc_attr($ep['search']); ?>" data-year="<?php echo esc_attr($ep['year']); ?>">
      <div class="x1-episode-number"><span>Episode</span><strong><?php echo esc_html($ep['episode'] !== '' ? $ep['episode'] : (string)($index+1)); ?></strong></div>
      <div class="x1-episode-details"><h2><?php echo esc_html($ep['title']); ?></h2><p><?php echo esc_html(trim($ep['date'] . ($ep['year'] !== '' && $ep['date'] === '' ? $ep['year'] : ''))); ?></p></div>
      <div class="episode-actions"><button class="x1-play-button johnny-dollar-play-button" type="button" data-audio="<?php echo esc_url($url); ?>" data-title="<?php echo esc_attr($ep['title']); ?>" data-meta="<?php echo esc_attr(trim(($ep['episode'] !== '' ? 'Episode ' . $ep['episode'] : '') . ($ep['date'] !== '' ? ' • ' . $ep['date'] : ''))); ?>">Play Episode</button><div class="episode-reactions" data-reaction-key="<?php echo esc_attr('johnny-dollar|' . (string) $ep['relative']); ?>" role="group" aria-label="Rate this episode"><button class="episode-reaction" type="button" data-reaction="up" aria-label="Thumbs up" aria-pressed="false">👍</button><button class="episode-reaction" type="button" data-reaction="down" aria-label="Thumbs down" aria-pressed="false">👎</button></div></div>
    </article>
    <?php endforeach; ?>
  </div>
  <p class="x1-no-results" id="johnny-dollar-no-results" hidden>No episodes match that search.</p>
  <?php endif; ?>
</section>
<script>
(function(){
 const search=document.getElementById('johnny-dollar-search'), rows=[...document.querySelectorAll('.johnny-dollar-episode-row')], status=document.getElementById('johnny-dollar-status'), empty=document.getElementById('johnny-dollar-no-results'), player=document.getElementById('johnny-dollar-player'), now=document.getElementById('johnny-dollar-now-playing'), title=document.getElementById('johnny-dollar-now-title'), meta=document.getElementById('johnny-dollar-now-meta');
 function updateDividers(){document.querySelectorAll('.johnny-dollar-year-divider').forEach(d=>{d.hidden=!rows.some(r=>r.dataset.year===d.dataset.year&&!r.hidden);});}
 if(search){search.addEventListener('input',()=>{const q=search.value.toLowerCase().trim();let n=0;rows.forEach(r=>{r.hidden=q!==''&&!r.dataset.search.includes(q);if(!r.hidden)n++;});status.textContent=n+' episode'+(n===1?'':'s')+' shown.';empty.hidden=n!==0;updateDividers();});}
 document.querySelectorAll('.johnny-dollar-play-button').forEach(b=>b.addEventListener('click',()=>{player.pause();player.src=b.dataset.audio;player.load();title.textContent=b.dataset.title;meta.textContent=b.dataset.meta;now.hidden=false;player.play().catch((error)=>{meta.textContent=(b.dataset.meta?b.dataset.meta+' • ':'')+'Audio could not be loaded from '+b.dataset.audio; if(window.console){console.error('Yours Truly, Johnny Dollar audio playback failed:', b.dataset.audio, error);}});now.scrollIntoView({behavior:'smooth',block:'center'});}));
 document.querySelectorAll('[data-year-target]').forEach(b=>b.addEventListener('click',()=>{const target=document.getElementById(b.dataset.yearTarget);if(target)target.scrollIntoView({behavior:'smooth',block:'start'});}));
})();
</script>
<?php get_footer(); ?>
