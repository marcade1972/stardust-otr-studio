<?php
/**
 * Template Name: Hall of Fantasy Episode Library
 * Template Post Type: page
 *
 * Searchable library generated recursively from MP3 files in the Hall of Fantasy archive.
 *
 * @package Stardust_Broadcast
 */
if (!defined('ABSPATH')) { exit; }

function stardust_hall_of_fantasy_library_directory(): string {
    $root = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : '';
    $candidates = [
        trailingslashit(ABSPATH) . 'hof',
        trailingslashit(dirname(untrailingslashit(ABSPATH))) . 'hof',
    ];
    if ($root !== '') { $candidates[] = trailingslashit($root) . 'hof'; }
    $uploads = wp_get_upload_dir();
    if (!empty($uploads['basedir'])) { $candidates[] = trailingslashit((string) $uploads['basedir']) . 'hof'; }
    foreach (array_unique(array_map('wp_normalize_path', $candidates)) as $candidate) {
        if (is_dir($candidate) && is_readable($candidate)) { return untrailingslashit($candidate); }
    }
    return '';
}

function stardust_hall_of_fantasy_pretty_title(string $value): string {
    $value = preg_replace('/\.mp3$/i', '', $value);
    $value = preg_replace('/^(the[ _-]*)?hall[s]?[ _-]*of[ _-]*fantasy([ _-]*(program|show|radio))?[ _-]*/i', '', (string) $value);
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

function stardust_hall_of_fantasy_parse_date(string $raw, string $fallback_year = ''): array {
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

function stardust_hall_of_fantasy_parse_file(string $filename, string $relative, string $folder_year): array {
    $stem = pathinfo($filename, PATHINFO_FILENAME);
    $work = preg_replace('/^(the[ _-]*)?hall[s]?[ _-]*of[ _-]*fantasy([ _-]*(program|show|radio))?[ _-]*/i', '', $stem);
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
    list($date, $sort, $year) = stardust_hall_of_fantasy_parse_date($date_raw, $folder_year);
    $title = stardust_hall_of_fantasy_pretty_title($title_part);
    return [
        'filename'=>$filename, 'relative'=>$relative,
        'episode'=>$episode !== '' ? str_pad((string)((int)$episode), 3, '0', STR_PAD_LEFT) : '',
        'title'=>$title !== '' ? $title : $stem, 'date'=>$date, 'date_sort'=>$sort, 'year'=>$year,
        'search'=>strtolower(trim($episode . ' ' . $title . ' ' . $date . ' ' . $year . ' ' . $relative)),
    ];
}

function stardust_hall_of_fantasy_scan(string $directory): array {
    if ($directory === '') { return []; }
    $cache_key = 'stardust_hall_of_fantasy_library_' . md5($directory);
    if (current_user_can('manage_options') && isset($_GET['refresh-hall-of-fantasy'])) { delete_transient($cache_key); }
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
            $episodes[] = stardust_hall_of_fantasy_parse_file($file->getFilename(), $relative, $folder_year);
        }
    } catch (UnexpectedValueException $e) { return []; }
    usort($episodes, static function(array $a, array $b): int {
        if ($a['date_sort'] !== $b['date_sort']) { return strcmp((string)$a['date_sort'], (string)$b['date_sort']); }
        if ($a['episode'] !== $b['episode']) { return strnatcasecmp((string)$a['episode'], (string)$b['episode']); }
        return strnatcasecmp((string)$a['title'], (string)$b['title']);
    });
    set_transient($cache_key, $episodes, 30 * MINUTE_IN_SECONDS); return $episodes;
}

function stardust_hall_of_fantasy_url_path(string $relative): string {
    return implode('/', array_map('rawurlencode', array_filter((array) preg_split('~[\\\\/]+~', $relative), 'strlen')));
}


/**
 * Hall of Fantasy MP3 files are served from the confirmed public domain-root archive.
 */
function stardust_hall_of_fantasy_audio_base_url(string $directory): string {
    return stardust_archive_url('hof/');
}

$directory = stardust_hall_of_fantasy_library_directory();
$episodes = stardust_hall_of_fantasy_scan($directory); $year_counts = [];
foreach ($episodes as $ep) { if ($ep['year'] !== '') { $year_counts[$ep['year']] = isset($year_counts[$ep['year']]) ? $year_counts[$ep['year']] + 1 : 1; } }
$years = array_keys($year_counts); sort($years, SORT_NUMERIC); $audio_base = stardust_hall_of_fantasy_audio_base_url($directory);
$related = [
    ['Inner Sanctum','inner-sanctum','Enter through the creaking door for macabre mysteries, ghosts, murder, and sinister humor.'],
    ['The Mysterious Traveler','the-mysterious-traveler','Board a midnight train into crime, fantasy, science fiction, and supernatural terror.'],
    ['Suspense','suspense','Radio’s theater of thrills brings together unforgettable scripts and celebrated dramatic performances.'],
    ['The Whistler','the-whistler','An unseen narrator guides dark tales of hidden guilt, weakness, and ironic justice.'],
    ['CBS Radio Mystery Theater','cbs-radio-mystery-theater','A later revival of mystery, suspense, horror, and imaginative radio drama.'],
];
get_header();
?>
<section class="content-wrap wrap x1-library-page hall-of-fantasy-library-page">
  <header class="suspense-spotlight hall-of-fantasy-spotlight">
    <div class="suspense-spotlight-copy hall-of-fantasy-spotlight-copy">
      <p class="eyebrow"><?php esc_html_e('Stardust Series Spotlight', 'stardust-broadcast'); ?></p>
      <h1><?php echo esc_html(get_the_title()); ?></h1>
      <blockquote><?php esc_html_e('“There is a world beyond our own—a world of mystery, imagination, and terror.”', 'stardust-broadcast'); ?></blockquote>
      <p><?php esc_html_e('Created and directed by Richard Thorne, Hall of Fantasy invites listeners into a shadowed world of supernatural legends, haunted places, ancient curses, and psychological terror. Its intimate performances and stark soundscapes turn each surviving broadcast into a candlelit journey beyond the ordinary.', 'stardust-broadcast'); ?></p>
    </div>
    <div class="suspense-microphone hall-of-fantasy-tome" aria-hidden="true"><span></span></div>
  </header>

  <section class="suspense-facts hall-of-fantasy-facts" aria-label="Hall of Fantasy series facts">
    <div><span>Radio Runs</span><strong>1946–1947 / 1949–1953</strong></div>
    <div><span>Network</span><strong>Mutual</strong></div>
    <div><span>Format</span><strong>Supernatural Anthology</strong></div>
    <div><span>Archive</span><strong><?php echo esc_html(number_format_i18n(count($episodes))); ?> Episodes</strong></div>
  </section>

  <section class="suspense-starters hall-of-fantasy-starters">
    <div><p class="eyebrow">A Great Place to Start</p><h2>Open the door with five chilling broadcasts</h2></div>
    <ul><li>He Who Follows Me</li><li>The Shadow People</li><li>The Masks of Ashor</li><li>The Silver Flask</li><li>The Cask of Amontillado</li></ul>
  </section>

  <section class="suspense-related hall-of-fantasy-related" aria-labelledby="hall-of-fantasy-related-title">
    <div class="suspense-related-heading hall-of-fantasy-related-heading"><p class="eyebrow">Continue Exploring</p><h2 id="hall-of-fantasy-related-title">More Like This Series</h2><p>More supernatural radio dramas filled with shadows, secrets, curses, and journeys beyond the ordinary.</p></div>
    <div class="suspense-related-grid hall-of-fantasy-related-grid">
      <?php foreach ($related as $item): $page = get_page_by_path($item[1]); $url = $page ? get_permalink($page) : home_url('/' . $item[1] . '/'); ?>
      <a class="suspense-related-card hall-of-fantasy-related-card" href="<?php echo esc_url($url); ?>"><span>Recommended Mystery</span><strong><?php echo esc_html($item[0]); ?></strong><p><?php echo esc_html($item[2]); ?></p><b>Explore Series →</b></a>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($directory === ''): ?>
  <section class="x1-library-notice" role="alert"><h2>The archive folder for Hall of Fantasy could not be located.</h2><p>The template could not find the confirmed readable <code>/hof/</code> directory.</p></section>
  <?php elseif (!$episodes): ?>
  <section class="x1-library-notice"><h2>No MP3 files were found.</h2><p>The detected Hall of Fantasy folder exists but contains no MP3 files.</p></section>
  <?php else: ?>
  <section class="x1-library-console hall-of-fantasy-library-console">
    <div class="x1-search-group"><label for="hall-of-fantasy-search">Search this series</label><input id="hall-of-fantasy-search" type="search" placeholder="Try a title, episode number, date, or year…" autocomplete="off"></div>
    <?php if ($years): ?><div class="suspense-year-jump hall-of-fantasy-year-jump"><span>Jump to Year</span><?php foreach ($years as $year): ?><button type="button" data-year-target="hall-of-fantasy-year-<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></button><?php endforeach; ?></div><?php endif; ?>
    <div class="x1-library-status" id="hall-of-fantasy-status"><?php echo esc_html(number_format_i18n(count($episodes))); ?> episodes ready to browse.</div>
    <div class="x1-now-playing" id="hall-of-fantasy-now-playing" hidden><p class="eyebrow">Now Playing</p><h2 id="hall-of-fantasy-now-title"></h2><p id="hall-of-fantasy-now-meta"></p><audio id="hall-of-fantasy-player" controls preload="metadata"></audio></div>
  </section>

  <div class="x1-episode-list hall-of-fantasy-episode-list" id="hall-of-fantasy-list">
    <?php $last_year=''; foreach ($episodes as $index=>$ep): if ($ep['year'] !== '' && $ep['year'] !== $last_year): $last_year=$ep['year']; ?>
      <div class="suspense-year-divider hall-of-fantasy-year-divider" id="hall-of-fantasy-year-<?php echo esc_attr($last_year); ?>" data-year="<?php echo esc_attr($last_year); ?>"><span><?php echo esc_html($last_year); ?></span><small><?php echo esc_html(number_format_i18n($year_counts[$last_year])); ?> broadcasts</small></div>
    <?php endif; $url = $audio_base . stardust_hall_of_fantasy_url_path($ep['relative']); ?>
    <article class="x1-episode-row hall-of-fantasy-episode-row" data-search="<?php echo esc_attr($ep['search']); ?>" data-year="<?php echo esc_attr($ep['year']); ?>">
      <div class="x1-episode-number"><span>Episode</span><strong><?php echo esc_html($ep['episode'] !== '' ? $ep['episode'] : (string)($index+1)); ?></strong></div>
      <div class="x1-episode-details"><h2><?php echo esc_html($ep['title']); ?></h2><p><?php echo esc_html(trim($ep['date'] . ($ep['year'] !== '' && $ep['date'] === '' ? $ep['year'] : ''))); ?></p></div>
      <div class="episode-actions"><button class="x1-play-button hall-of-fantasy-play-button" type="button" data-audio="<?php echo esc_url($url); ?>" data-title="<?php echo esc_attr($ep['title']); ?>" data-meta="<?php echo esc_attr(trim(($ep['episode'] !== '' ? 'Episode ' . $ep['episode'] : '') . ($ep['date'] !== '' ? ' • ' . $ep['date'] : ''))); ?>">Play Episode</button><div class="episode-reactions" data-reaction-key="<?php echo esc_attr('hall-of-fantasy|' . (string) $ep['relative']); ?>" role="group" aria-label="Rate this episode"><button class="episode-reaction" type="button" data-reaction="up" aria-label="Thumbs up" aria-pressed="false">👍</button><button class="episode-reaction" type="button" data-reaction="down" aria-label="Thumbs down" aria-pressed="false">👎</button></div></div>
    </article>
    <?php endforeach; ?>
  </div>
  <p class="x1-no-results" id="hall-of-fantasy-no-results" hidden>No episodes match that search.</p>
  <?php endif; ?>
</section>
<script>
(function(){
 const search=document.getElementById('hall-of-fantasy-search'), rows=[...document.querySelectorAll('.hall-of-fantasy-episode-row')], status=document.getElementById('hall-of-fantasy-status'), empty=document.getElementById('hall-of-fantasy-no-results'), player=document.getElementById('hall-of-fantasy-player'), now=document.getElementById('hall-of-fantasy-now-playing'), title=document.getElementById('hall-of-fantasy-now-title'), meta=document.getElementById('hall-of-fantasy-now-meta');
 function updateDividers(){document.querySelectorAll('.hall-of-fantasy-year-divider').forEach(d=>{d.hidden=!rows.some(r=>r.dataset.year===d.dataset.year&&!r.hidden);});}
 if(search){search.addEventListener('input',()=>{const q=search.value.toLowerCase().trim();let n=0;rows.forEach(r=>{r.hidden=q!==''&&!r.dataset.search.includes(q);if(!r.hidden)n++;});status.textContent=n+' episode'+(n===1?'':'s')+' shown.';empty.hidden=n!==0;updateDividers();});}
 document.querySelectorAll('.hall-of-fantasy-play-button').forEach(b=>b.addEventListener('click',()=>{player.pause();player.src=b.dataset.audio;player.load();title.textContent=b.dataset.title;meta.textContent=b.dataset.meta;now.hidden=false;player.play().catch((error)=>{meta.textContent=(b.dataset.meta?b.dataset.meta+' • ':'')+'Audio could not be loaded from '+b.dataset.audio; if(window.console){console.error('Hall of Fantasy audio playback failed:', b.dataset.audio, error);}});now.scrollIntoView({behavior:'smooth',block:'center'});}));
 document.querySelectorAll('[data-year-target]').forEach(b=>b.addEventListener('click',()=>{const target=document.getElementById(b.dataset.yearTarget);if(target)target.scrollIntoView({behavior:'smooth',block:'start'});}));
})();
</script>
<?php get_footer(); ?>
