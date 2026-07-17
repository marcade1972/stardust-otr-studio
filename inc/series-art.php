<?php
/**
 * Stardust Series Art Library.
 * Stores one reusable image per category/series and resolves sensible fallbacks.
 */
if (!defined('ABSPATH')) { exit; }

function stardust_series_art_add_field(): void {
    ?>
    <div class="form-field stardust-series-art-field">
        <label for="stardust_series_art_id"><?php esc_html_e('Stardust Series Artwork', 'stardust-broadcast'); ?></label>
        <input type="hidden" id="stardust_series_art_id" name="stardust_series_art_id" value="">
        <div class="stardust-series-art-preview"></div>
        <button type="button" class="button stardust-series-art-upload"><?php esc_html_e('Choose Series Artwork', 'stardust-broadcast'); ?></button>
        <button type="button" class="button-link-delete stardust-series-art-remove" hidden><?php esc_html_e('Remove artwork', 'stardust-broadcast'); ?></button>
        <p><?php esc_html_e('Upload one master image for this radio series. Stardust reuses it across episode cards, search results, series pages, and future social graphics.', 'stardust-broadcast'); ?></p>
    </div>
    <?php
}
add_action('category_add_form_fields', 'stardust_series_art_add_field');

function stardust_series_art_edit_field(WP_Term $term): void {
    $image_id = (int) get_term_meta($term->term_id, '_stardust_series_art_id', true);
    $url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>
    <tr class="form-field stardust-series-art-field">
        <th scope="row"><label for="stardust_series_art_id"><?php esc_html_e('Stardust Series Artwork', 'stardust-broadcast'); ?></label></th>
        <td>
            <input type="hidden" id="stardust_series_art_id" name="stardust_series_art_id" value="<?php echo esc_attr($image_id); ?>">
            <div class="stardust-series-art-preview"><?php if ($url): ?><img src="<?php echo esc_url($url); ?>" alt="" style="max-width:240px;height:auto;border-radius:8px;display:block;margin-bottom:12px;"><?php endif; ?></div>
            <button type="button" class="button stardust-series-art-upload"><?php esc_html_e('Choose Series Artwork', 'stardust-broadcast'); ?></button>
            <button type="button" class="button-link-delete stardust-series-art-remove" <?php echo $image_id ? '' : 'hidden'; ?>><?php esc_html_e('Remove artwork', 'stardust-broadcast'); ?></button>
            <p class="description"><?php esc_html_e('This image becomes the reusable visual identity for the series.', 'stardust-broadcast'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('category_edit_form_fields', 'stardust_series_art_edit_field');

function stardust_save_series_art(int $term_id): void {
    if (!current_user_can('manage_categories')) { return; }
    $image_id = isset($_POST['stardust_series_art_id']) ? absint($_POST['stardust_series_art_id']) : 0;
    if ($image_id) {
        update_term_meta($term_id, '_stardust_series_art_id', $image_id);
    } else {
        delete_term_meta($term_id, '_stardust_series_art_id');
    }
}
add_action('created_category', 'stardust_save_series_art');
add_action('edited_category', 'stardust_save_series_art');

function stardust_series_art_admin_assets(string $hook): void {
    $screen = get_current_screen();
    $is_category = $screen && $screen->taxonomy === 'category';
    $is_library = $screen && $screen->id === 'appearance_page_stardust-series-art';
    if (!$is_category && !$is_library) { return; }

    wp_enqueue_media();
    wp_add_inline_style('common', '
        .stardust-series-art-preview img{box-shadow:0 3px 12px rgba(0,0,0,.18)}
        .stardust-art-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:18px;margin-top:20px}
        .stardust-art-card{background:#fff;border:1px solid #ccd0d4;border-radius:8px;overflow:hidden}
        .stardust-art-card img,.stardust-art-placeholder{width:100%;aspect-ratio:16/10;object-fit:cover;display:block;background:#1a1611}
        .stardust-art-placeholder{display:flex;align-items:center;justify-content:center;color:#caa76a;font-size:40px}
        .stardust-art-card-body{padding:14px}.stardust-art-card h2{margin:0 0 8px;font-size:16px}.stardust-art-card p{margin:0 0 12px;color:#646970}
    ');
    $script = <<<'JS'
jQuery(function($){
    $(document).on('click','.stardust-series-art-upload',function(e){
        e.preventDefault();
        var field=$(this).closest('.stardust-series-art-field');
        var frame=wp.media({title:'Choose Series Artwork',button:{text:'Use this artwork'},multiple:false});
        frame.on('select',function(){
            var a=frame.state().get('selection').first().toJSON();
            var imageUrl=(a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url;
            field.find('#stardust_series_art_id').val(a.id);
            field.find('.stardust-series-art-preview').html('<img src="'+imageUrl+'" alt="" style="max-width:240px;height:auto;border-radius:8px;display:block;margin-bottom:12px;">');
            field.find('.stardust-series-art-remove').prop('hidden',false);
        });
        frame.open();
    });
    $(document).on('click','.stardust-series-art-remove',function(e){
        e.preventDefault();
        var field=$(this).closest('.stardust-series-art-field');
        field.find('#stardust_series_art_id').val('');
        field.find('.stardust-series-art-preview').empty();
        $(this).prop('hidden',true);
    });
});
JS;
    wp_add_inline_script('jquery-core', $script);
}
add_action('admin_enqueue_scripts', 'stardust_series_art_admin_assets');

/** Find the category that best represents the episode's series. */
function stardust_get_series_term(int $post_id) {
    $series = trim((string) get_post_meta($post_id, '_stardust_series', true));
    if ($series !== '') {
        $term = get_term_by('name', $series, 'category');
        if (!$term) { $term = get_term_by('slug', sanitize_title($series), 'category'); }
        if ($term && !is_wp_error($term)) { return $term; }
    }

    $categories = get_the_category($post_id);
    if (!$categories || is_wp_error($categories)) { return null; }

    // Prefer any assigned category that already has series artwork.
    foreach ($categories as $category) {
        if ((int) get_term_meta($category->term_id, '_stardust_series_art_id', true)) { return $category; }
    }

    return $categories[0];
}

/** Return a reusable series image URL, with genre-based fallback artwork. */
function stardust_get_series_art_url(int $post_id, string $size = 'medium_large'): string {
    // Academy Award Theater is a drama series. Never reuse its former Whistler artwork.
    $series_name = strtolower(trim((string) get_post_meta($post_id, '_stardust_series', true)));
    $post_title = strtolower((string) get_the_title($post_id));
    if (strpos($series_name, 'academy award theater') !== false || strpos($series_name, 'academy award theatre') !== false || strpos($post_title, 'academy award theater') !== false || strpos($post_title, 'academy award theatre') !== false) {
        return get_template_directory_uri() . '/assets/images/genres/drama.jpg';
    }
    $term = stardust_get_series_term($post_id);
    if ($term && strpos(strtolower((string) $term->name), 'whistler') !== false) {
        return function_exists('stardust_legacy_whistler_replacement_url') ? stardust_legacy_whistler_replacement_url() : get_template_directory_uri() . '/assets/images/genres/thriller.jpg';
    }
    if ($term) {
        $image_id = (int) get_term_meta($term->term_id, '_stardust_series_art_id', true);
        if ($image_id && (!function_exists('stardust_is_retired_whistler_attachment') || !stardust_is_retired_whistler_attachment($image_id))) {
            $url = wp_get_attachment_image_url($image_id, $size);
            if ($url && (!function_exists('stardust_is_retired_whistler_url') || !stardust_is_retired_whistler_url($url))) { return $url; }
        }
    }

    $genre_map = [
        'detective' => 'detective.jpg', 'mystery' => 'mystery.jpg', 'drama' => 'drama.jpg',
        'comedy' => 'comedy.jpg', 'sci-fi' => 'sci-fi.jpg', 'science-fiction' => 'sci-fi.jpg',
        'western' => 'western.jpg', 'adventure' => 'adventure.jpg', 'juvenile' => 'juvenile.jpg',
        'children-youth' => 'children-youth.jpg', 'children-and-youth' => 'children-youth.jpg',
        'thriller' => 'thriller.jpg', 'crime' => 'crime.jpg', 'horror' => 'horror.jpg',
        'romance' => 'romance.jpg',
        'quiz-shows' => 'quiz-shows.jpg', 'quiz-show' => 'quiz-shows.jpg',
    ];

    $categories = get_the_category($post_id);
    if ($categories && !is_wp_error($categories)) {
        foreach ($categories as $category) {
            $slug = sanitize_title($category->slug);
            if (isset($genre_map[$slug])) {
                return get_template_directory_uri() . '/assets/images/genres/' . $genre_map[$slug];
            }
        }
    }

    return get_template_directory_uri() . '/assets/images/featured-fallback.jpg';
}

function stardust_register_series_art_library(): void {
    add_theme_page(
        __('Series Art Library', 'stardust-broadcast'),
        __('Series Art Library', 'stardust-broadcast'),
        'manage_categories',
        'stardust-series-art',
        'stardust_render_series_art_library'
    );
}
add_action('admin_menu', 'stardust_register_series_art_library');

function stardust_render_series_art_library(): void {
    if (!current_user_can('manage_categories')) { return; }
    $categories = get_categories(['hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC']);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Stardust Series Art Library', 'stardust-broadcast'); ?></h1>
        <p><?php esc_html_e('Assign one master image to every radio series. Edit a card to upload or replace its artwork; Stardust will reuse that image everywhere.', 'stardust-broadcast'); ?></p>
        <div class="stardust-art-grid">
            <?php foreach ($categories as $category):
                $image_id = (int) get_term_meta($category->term_id, '_stardust_series_art_id', true);
                $image = $image_id ? wp_get_attachment_image_url($image_id, 'medium_large') : '';
                $edit_url = get_edit_term_link($category->term_id, 'category');
            ?>
                <article class="stardust-art-card">
                    <?php if ($image): ?><img src="<?php echo esc_url($image); ?>" alt="">
                    <?php else: ?><div class="stardust-art-placeholder" aria-hidden="true">📻</div><?php endif; ?>
                    <div class="stardust-art-card-body">
                        <h2><?php echo esc_html($category->name); ?></h2>
                        <p><?php echo $image ? esc_html__('Artwork assigned', 'stardust-broadcast') : esc_html__('Using automatic fallback artwork', 'stardust-broadcast'); ?></p>
                        <a class="button" href="<?php echo esc_url($edit_url); ?>"><?php echo $image ? esc_html__('Replace Artwork', 'stardust-broadcast') : esc_html__('Add Artwork', 'stardust-broadcast'); ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
