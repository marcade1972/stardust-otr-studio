<?php
/**
 * Template Name: Broadcast Library
 * Template Post Type: page
 *
 * Searchable, genre-organized directory for Stardust OTR series libraries.
 *
 * @package Stardust_Broadcast
 */
if (!defined('ABSPATH')) { exit; }

$series = apply_filters('stardust_broadcast_library_series', [
    [
        'title' => 'The Shadow', 'slug' => 'the-shadow', 'template' => 'page-the-shadow-library.php',
        'section' => 'Mystery & Suspense', 'genres' => ['Mystery', 'Crime', 'Suspense'], 'years' => '1937–1954', 'network' => 'Mutual',
        'description' => 'Enter the dark, atmospheric world of Lamont Cranston, Margot Lane, and radio’s most legendary crime fighter.',
        'fallback' => 'mystery.jpg', 'accent' => 'shadow',
    ],
    [
        'title' => 'Suspense', 'slug' => 'suspense', 'template' => 'page-suspense-library.php',
        'section' => 'Mystery & Suspense', 'genres' => ['Mystery', 'Suspense', 'Anthology'], 'years' => '1942–1962', 'network' => 'CBS',
        'description' => 'Radio’s theater of thrills, featuring unforgettable scripts, Hollywood stars, and some of the medium’s finest dramatic performances.',
        'fallback' => 'thriller.jpg', 'accent' => 'suspense',
    ],
    [
        'title' => 'The Whistler', 'slug' => 'the-whistler', 'template' => 'page-the-whistler-library.php',
        'section' => 'Mystery & Suspense', 'genres' => ['Mystery', 'Crime', 'Noir'], 'years' => '1942–1955', 'network' => 'CBS',
        'description' => 'An unseen narrator guides tales of hidden guilt, human weakness, and ironic justice through the shadows of the night.',
        'fallback' => 'crime.jpg', 'accent' => 'whistler',
    ],
    [
        'title' => 'Inner Sanctum', 'slug' => 'inner-sanctum', 'template' => 'page-inner-sanctum-library.php',
        'section' => 'Mystery & Suspense', 'genres' => ['Horror', 'Mystery', 'Supernatural'], 'years' => '1941–1952', 'network' => 'NBC / Blue',
        'description' => 'Enter through the famous creaking door for macabre mysteries, sinister humor, ghosts, murder, and unforgettable radio horror.',
        'fallback' => 'horror.jpg', 'accent' => 'inner-sanctum',
    ],
    [
        'title' => 'The Mysterious Traveler', 'slug' => 'the-mysterious-traveler', 'template' => 'page-the-mysterious-traveler-library.php',
        'section' => 'Mystery & Suspense', 'genres' => ['Mystery', 'Horror', 'Fantasy'], 'years' => '1943–1952', 'network' => 'Mutual',
        'description' => 'Board a midnight train into crime, fantasy, science fiction, and supernatural terror with radio’s enigmatic traveler as your guide.',
        'fallback' => 'horror.jpg', 'accent' => 'mysterious-traveler',
    ],
    [
        'title' => 'Hall of Fantasy', 'slug' => 'hall-of-fantasy', 'template' => 'page-hall-of-fantasy-library.php',
        'section' => 'Mystery & Suspense', 'genres' => ['Horror', 'Supernatural', 'Fantasy'], 'years' => '1946–1953', 'network' => 'Mutual',
        'description' => 'Open a forbidden volume of haunted places, ancient curses, supernatural legends, and stark psychological terror.',
        'fallback' => 'horror.jpg', 'accent' => 'hall-of-fantasy',
    ],
    [
        'title' => 'Lights Out', 'slug' => 'lights-out', 'template' => 'page-lights-out-library.php',
        'section' => 'Mystery & Suspense', 'genres' => ['Horror', 'Supernatural', 'Anthology'], 'years' => '1934–1947', 'network' => 'NBC',
        'description' => 'Turn down the lights for groundbreaking radio nightmares, surreal terror, and unforgettable sound-driven horror from Wyllis Cooper and Arch Oboler.',
        'fallback' => 'horror.jpg', 'accent' => 'lights-out',
    ],
    [
        'title' => 'CBS Radio Mystery Theater', 'slug' => 'cbs-radio-mystery-theater', 'template' => 'page-cbs-radio-mystery-theater-library.php',
        'section' => 'Mystery & Suspense', 'genres' => ['Mystery', 'Horror', 'Suspense'], 'years' => '1974–1982', 'network' => 'CBS',
        'description' => 'E. G. Marshall opens the creaking door to a vast modern revival of mystery, suspense, horror, and imaginative drama.',
        'fallback' => 'horror.jpg', 'accent' => 'cbsrmt',
    ],
    [
        'title' => 'Dragnet', 'slug' => 'dragnet', 'template' => 'page-dragnet-library.php',
        'section' => 'Crime & Detective', 'genres' => ['Crime', 'Police Procedural', 'Detective'], 'years' => '1949–1957', 'network' => 'NBC',
        'description' => 'Sergeant Joe Friday follows the facts through precise, documentary-style investigations drawn from the files of the Los Angeles police.',
        'fallback' => 'crime.jpg', 'accent' => 'dragnet',
    ],
    [
        'title' => 'Yours Truly, Johnny Dollar', 'slug' => 'yours-truly-johnny-dollar', 'template' => 'page-yours-truly-johnny-dollar-library.php',
        'section' => 'Crime & Detective', 'genres' => ['Detective', 'Crime', 'Insurance Investigator'], 'years' => '1949–1962', 'network' => 'CBS',
        'description' => 'Follow America’s fabulous freelance insurance investigator through suspicious claims, dangerous assignments, and meticulously itemized expense accounts.',
        'fallback' => 'crime.jpg', 'accent' => 'johnny-dollar',
    ],
    [
        'title' => 'Gunsmoke', 'slug' => 'gunsmoke', 'template' => 'page-gunsmoke-library.php',
        'section' => 'Western', 'genres' => ['Western', 'Drama', 'Adult Western'], 'years' => '1952–1961', 'network' => 'CBS',
        'description' => 'Ride into Dodge City with Marshal Matt Dillon in radio’s landmark adult Western, rich with realism, atmosphere, and moral complexity.',
        'fallback' => 'western.jpg', 'accent' => 'gunsmoke',
    ],
    [
        'title' => 'The Six Shooter', 'slug' => 'the-six-shooter', 'template' => 'page-the-six-shooter-library.php',
        'section' => 'Western', 'genres' => ['Western', 'Drama', 'Wandering Hero'], 'years' => '1953–1954', 'network' => 'NBC',
        'description' => 'Ride the trail with James Stewart as Britt Ponset, a quiet, thoughtful drifter armed with dry humor, compassion, and a pearl-handled six-shooter.',
        'fallback' => 'western.jpg', 'accent' => 'six-shooter',
    ],
    [
        'title' => 'The Lone Ranger', 'slug' => 'the-lone-ranger', 'template' => 'page-the-lone-ranger-library.php',
        'section' => 'Western', 'genres' => ['Western', 'Adventure', 'Masked Hero'], 'years' => '1933–1956', 'network' => 'WXYZ / Mutual',
        'description' => 'Ride with the masked champion of justice and his trusted companion Tonto through one of radio’s most celebrated frontier adventures.',
        'fallback' => 'western.jpg', 'accent' => 'lone-ranger',
    ],
    [
        'title' => 'Burns and Allen', 'slug' => 'burns-and-allen', 'template' => 'page-burns-and-allen-library.php',
        'section' => 'Comedy', 'genres' => ['Comedy', 'Situation Comedy', 'Variety'], 'years' => '1932–1950', 'network' => 'CBS / NBC',
        'description' => 'Enjoy George Burns trying to keep up as Gracie Allen’s wonderfully sideways logic turns everyday life into effortless comic confusion.',
        'fallback' => 'comedy.jpg', 'accent' => 'burns-allen',
    ],
    [
        'title' => 'The Jack Benny Program', 'slug' => 'jack-benny-program', 'template' => 'page-jack-benny-program-library.php',
        'section' => 'Comedy', 'genres' => ['Comedy', 'Character Comedy', 'Variety'], 'years' => '1932–1955', 'network' => 'NBC / CBS',
        'description' => 'Join Jack Benny and his legendary ensemble for impeccable timing, beloved running gags, and one of radio’s funniest comic personas.',
        'fallback' => 'comedy.jpg', 'accent' => 'jack-benny-program',
    ],
    [
        'title' => 'The Life of Riley', 'slug' => 'life-of-riley', 'template' => 'page-life-of-riley-library.php',
        'section' => 'Comedy', 'genres' => ['Comedy', 'Family Comedy', 'Situation Comedy'], 'years' => '1944–1951', 'network' => 'NBC',
        'description' => 'Join Chester A. Riley as his good intentions, family worries, and grand schemes turn everyday working-class life into comic chaos.',
        'fallback' => 'comedy.jpg', 'accent' => 'life-of-riley',
    ],
    [
        'title' => 'Our Miss Brooks', 'slug' => 'our-miss-brooks', 'template' => 'page-our-miss-brooks-library.php',
        'section' => 'Comedy', 'genres' => ['Comedy', 'School Comedy', 'Situation Comedy'], 'years' => '1948–1957', 'network' => 'CBS',
        'description' => 'Join Connie Brooks at Madison High, where romance, faculty politics, and student schemes meet Eve Arden’s unmatched dry wit.',
        'fallback' => 'comedy.jpg', 'accent' => 'our-miss-brooks',
    ],
    [
        'title' => 'Escape', 'slug' => 'escape', 'template' => 'page-escape-library.php',
        'section' => 'Adventure', 'genres' => ['Adventure', 'Thriller', 'Anthology'], 'years' => '1947–1954', 'network' => 'CBS',
        'description' => 'Leave the everyday world behind for vivid tales of survival, danger, exotic places, and unforgettable high adventure.',
        'fallback' => 'adventure.jpg', 'accent' => 'escape',
    ],
    [
        'title' => 'X Minus One', 'slug' => 'x-minus-one', 'template' => 'page-x-minus-one-library.php',
        'section' => 'Science Fiction', 'genres' => ['Science Fiction', 'Anthology', 'Adventure'], 'years' => '1955–1958', 'network' => 'NBC',
        'description' => 'Intelligent science fiction adapted from leading writers and introduced with one of radio’s most memorable countdowns.',
        'fallback' => 'sci-fi.jpg', 'accent' => 'xminusone',
    ],
    [
        'title' => 'Dimension X', 'slug' => 'dimension-x', 'template' => 'page-dimension-x-library.php',
        'section' => 'Science Fiction', 'genres' => ['Science Fiction', 'Anthology', 'Adventure'], 'years' => '1950–1951', 'network' => 'NBC',
        'description' => 'Adventures in time and space from the pioneering anthology that helped bring serious science fiction to network radio.',
        'fallback' => 'sci-fi.jpg', 'accent' => 'dimensionx',
    ],
]);

foreach ($series as &$item) {
    $page = get_page_by_path($item['slug']);
    if (!$page && !empty($item['template'])) {
        $matches = get_posts([
            'post_type' => 'page', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template',
            'meta_value' => $item['template'], 'post_status' => 'publish', 'orderby' => 'menu_order title', 'order' => 'ASC',
        ]);
        if ($matches) { $page = $matches[0]; }
    }
    $item['url'] = $page ? get_permalink($page) : home_url('/' . $item['slug'] . '/');
    $featured = $page ? get_the_post_thumbnail_url($page->ID, 'large') : '';
    $item['image'] = $featured ?: get_template_directory_uri() . '/assets/images/genres/' . $item['fallback'];
    $item['search'] = strtolower(implode(' ', array_merge(
        [$item['title'], $item['section'], $item['years'], $item['network'], $item['description']], $item['genres']
    )));
}
unset($item);

$sections = [];
foreach ($series as $item) { $sections[$item['section']][] = $item; }
$section_order = ['Mystery & Suspense', 'Crime & Detective', 'Science Fiction', 'Western', 'Comedy', 'Adventure', 'Family', 'Horror & Supernatural'];
uksort($sections, static function($a, $b) use ($section_order) {
    $ai = array_search($a, $section_order, true); $bi = array_search($b, $section_order, true);
    $ai = $ai === false ? 999 : $ai; $bi = $bi === false ? 999 : $bi;
    return $ai === $bi ? strcasecmp($a, $b) : $ai <=> $bi;
});

get_header();
?>
<main class="broadcast-library-page">
  <section class="broadcast-library-hero wrap">
    <p class="eyebrow"><?php esc_html_e('The Stardust OTR Collection', 'stardust-broadcast'); ?></p>
    <h1><?php echo esc_html(get_the_title()); ?></h1>
    <p class="broadcast-library-intro"><?php esc_html_e('Explore complete classic-radio collections arranged by genre, each carefully presented with historical context, recommended episodes, related programs, and a searchable listening library.', 'stardust-broadcast'); ?></p>
    <div class="broadcast-library-stats" aria-label="Broadcast Library statistics">
      <div><strong><?php echo esc_html(number_format_i18n(count($series))); ?></strong><span><?php esc_html_e('Series Available', 'stardust-broadcast'); ?></span></div>
      <div><strong><?php echo esc_html(number_format_i18n(count($sections))); ?></strong><span><?php esc_html_e('Genre Collections', 'stardust-broadcast'); ?></span></div>
      <div><strong>24/7</strong><span><?php esc_html_e('Classic Radio', 'stardust-broadcast'); ?></span></div>
    </div>
  </section>

  <section class="broadcast-library-controls wrap" aria-label="Search and filter the Broadcast Library">
    <div class="broadcast-library-search">
      <label for="broadcast-series-search"><?php esc_html_e('Search the Broadcast Library', 'stardust-broadcast'); ?></label>
      <div class="broadcast-library-search-field"><span aria-hidden="true">⌕</span><input id="broadcast-series-search" type="search" placeholder="Search by series, genre, network, or broadcast year…" autocomplete="off"></div>
      <p><?php esc_html_e('This search finds radio series. Each series page has its own separate episode search.', 'stardust-broadcast'); ?></p>
    </div>
    <div class="broadcast-library-filters" role="group" aria-label="Filter series by genre collection">
      <button class="is-active" type="button" data-library-filter="all" aria-pressed="true"><?php esc_html_e('All Collections', 'stardust-broadcast'); ?></button>
      <?php foreach (array_keys($sections) as $section): ?><button type="button" data-library-filter="<?php echo esc_attr(strtolower($section)); ?>" aria-pressed="false"><?php echo esc_html($section); ?></button><?php endforeach; ?>
    </div>
    <div class="broadcast-library-result-count" id="broadcast-library-result-count" aria-live="polite"><?php echo esc_html(count($series)); ?> series ready to explore.</div>
  </section>

  <div class="broadcast-library-sections wrap" id="broadcast-library-grid">
    <?php foreach ($sections as $section_name => $items): ?>
      <section class="broadcast-genre-section" data-section="<?php echo esc_attr(strtolower($section_name)); ?>">
        <header class="broadcast-genre-heading"><div><p class="eyebrow">Browse by Genre</p><h2><?php echo esc_html($section_name); ?></h2></div><span><strong class="genre-visible-count"><?php echo esc_html(count($items)); ?></strong> <?php echo esc_html(_n('Series', 'Series', count($items), 'stardust-broadcast')); ?></span></header>
        <div class="broadcast-library-grid">
          <?php foreach ($items as $item): $genre_tokens = array_map('strtolower', $item['genres']); ?>
          <article class="broadcast-series-card broadcast-series-card--<?php echo esc_attr($item['accent']); ?>" data-search="<?php echo esc_attr($item['search']); ?>" data-section="<?php echo esc_attr(strtolower($item['section'])); ?>" data-genres="<?php echo esc_attr(implode('|', $genre_tokens)); ?>">
            <a class="broadcast-series-card-link" href="<?php echo esc_url($item['url']); ?>" aria-label="<?php echo esc_attr(sprintf(__('Browse the %s library', 'stardust-broadcast'), $item['title'])); ?>">
              <div class="broadcast-series-art"><img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?> series artwork" loading="lazy"><span class="broadcast-series-image-shade" aria-hidden="true"></span></div>
              <div class="broadcast-series-content">
                <div class="broadcast-series-kicker"><span><?php echo esc_html($item['network']); ?></span><span><?php echo esc_html($item['years']); ?></span></div>
                <h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['description']); ?></p>
                <div class="broadcast-series-genres"><?php foreach ($item['genres'] as $genre): ?><span><?php echo esc_html($genre); ?></span><?php endforeach; ?></div>
                <b><?php esc_html_e('Browse Library', 'stardust-broadcast'); ?> <span aria-hidden="true">→</span></b>
              </div>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </div>

  <section class="broadcast-library-empty wrap" id="broadcast-library-empty" hidden><span aria-hidden="true">✦</span><h2><?php esc_html_e('No series matched your search.', 'stardust-broadcast'); ?></h2><p><?php esc_html_e('Try another title, genre, network, or broadcast year.', 'stardust-broadcast'); ?></p></section>
  <section class="broadcast-library-growing wrap"><p class="eyebrow"><?php esc_html_e('A Living Archive', 'stardust-broadcast'); ?></p><h2><?php esc_html_e('The library will keep growing.', 'stardust-broadcast'); ?></h2><p><?php esc_html_e('Every new Stardust series library will be placed into its proper genre collection, giving visitors one elegant starting point for the entire archive.', 'stardust-broadcast'); ?></p></section>
</main>
<script>
(function(){
 const search=document.getElementById('broadcast-series-search'), cards=[...document.querySelectorAll('.broadcast-series-card')], filters=[...document.querySelectorAll('[data-library-filter]')], count=document.getElementById('broadcast-library-result-count'), empty=document.getElementById('broadcast-library-empty'), sections=[...document.querySelectorAll('.broadcast-genre-section')]; let active='all';
 function apply(){const query=(search?search.value:'').toLowerCase().trim();let shown=0;cards.forEach(card=>{const visible=(query===''||card.dataset.search.includes(query))&&(active==='all'||card.dataset.section===active);card.hidden=!visible;if(visible)shown++;});sections.forEach(section=>{const visibleCards=[...section.querySelectorAll('.broadcast-series-card')].filter(card=>!card.hidden);section.hidden=visibleCards.length===0;const badge=section.querySelector('.genre-visible-count');if(badge)badge.textContent=visibleCards.length;});count.textContent=shown+' series '+(shown===1?'is':'are')+' shown.';empty.hidden=shown!==0;}
 if(search)search.addEventListener('input',apply); filters.forEach(button=>button.addEventListener('click',()=>{active=button.dataset.libraryFilter;filters.forEach(item=>{const selected=item===button;item.classList.toggle('is-active',selected);item.setAttribute('aria-pressed',selected?'true':'false');});apply();}));
})();
</script>
<?php get_footer(); ?>
