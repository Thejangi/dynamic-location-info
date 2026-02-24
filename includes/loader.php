<?php
// File: includes/loader.php

// 1) Start PHP session early
add_action('init', 'dli_start_session');
function dli_start_session()
{
    if (!session_id()) {
        session_start();
    }
}

// 2) Add rewrite rules for contact page and service pages
add_action('init', 'dli_add_rewrite_rules');
function dli_add_rewrite_rules()
{
    add_rewrite_rule(
        '^([^/]+)/contact-us/?$',
        'index.php?pagename=contact-us&location_slug=$matches[1]',
        'top'
    );

    $services = [
        'arborist-consultation',
        'tree-removal',
        'tree-shrub-trimming-pruning',
        'emergency-tree-services',
        'commercial-tree-services',
        'crane-assisted-tree-removal',
        'stump-grinding-removal',
    ];

    foreach ($services as $service_slug) {
        add_rewrite_rule(
            '^([^/]+)/' . $service_slug . '/?$',
            'index.php?pagename=' . $service_slug . '&location_slug=$matches[1]',
            'top'
        );
    }
}

// 3) Register the query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'location_slug';
    return $vars;
});

// 4) Set session for dynamic content using location_slug
add_action('template_redirect', 'dli_detect_location_slug');
function dli_detect_location_slug()
{
    $slug = get_query_var('location_slug');

    // Fallback: if rewrite/query_var didn't set location_slug (some permalink/caching cases),
    // infer it from the request path for localized service/contact URLs: /{location}/{service}/
    if (! $slug && (is_page('contact-us') || is_page([
        'arborist-consultation',
        'tree-removal',
        'tree-shrub-trimming-pruning',
        'emergency-tree-services',
        'commercial-tree-services',
        'crane-assisted-tree-removal',
        'stump-grinding-removal',
    ]))) {
        $path = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        $path = (string) parse_url($path, PHP_URL_PATH);
        $path = trim($path, '/');

        if ($path !== '') {
            $parts = explode('/', $path);
            // Expected: [0] location-slug, [1] page-slug
            if (count($parts) >= 2) {
                $candidate = sanitize_title($parts[0]);
                if ($candidate && $candidate !== 'service-location') {
                    $maybe = get_page_by_path($candidate, OBJECT, 'service-location');
                    if ($maybe && isset($maybe->ID)) {
                        $slug = $candidate;
                    }
                }
            }
        }
    }


    // Location-based session set
    if ($slug && (is_page('contact-us') || is_page([
        'arborist-consultation',
        'tree-removal',
        'tree-shrub-trimming-pruning',
        'emergency-tree-services',
        'commercial-tree-services',
        'crane-assisted-tree-removal',
        'stump-grinding-removal',
    ]))) {
        $loc = get_page_by_path($slug, OBJECT, 'service-location');
        if ($loc) {
            if (!session_id()) session_start();

            $_SESSION['dli_location_id'] = $loc->ID;

            if (is_page('contact-us')) {
                $embed = get_field('ghl_form_embed', $loc->ID);
                if ($embed) {
                    $_SESSION['dli_ghl_embed'] = $embed;
                }
            }
        }
    }

    // Static contact page visit (no slug): clear session
    elseif (is_page('contact-us') && !$slug) {
        if (!session_id()) session_start();
        unset($_SESSION['dli_location_id']);
        unset($_SESSION['dli_ghl_embed']);
    }


    // Viewing a single location page: set session location context
    elseif (is_singular('service-location')) {
        if (!session_id()) session_start();

        $loc_id = get_queried_object_id();
        if ($loc_id) {
            $_SESSION['dli_location_id'] = $loc_id;

            // Keep the embed in sync with the current location
            $embed = function_exists('get_field') ? get_field('ghl_form_embed', $loc_id) : '';
            if ($embed) {
                $_SESSION['dli_ghl_embed'] = $embed;
            } else {
                unset($_SESSION['dli_ghl_embed']);
            }
        }
    }
}

// 5) Rewrite Contact Us links on service/location/service pages
add_action('template_redirect', 'dli_buffer_contact_links');
function dli_buffer_contact_links()
{
    if (is_singular('service-location') || is_page([
        'contact-us',
        'arborist-consultation',
        'tree-removal',
        'tree-shrub-trimming-pruning',
        'emergency-tree-services',
        'commercial-tree-services',
        'crane-assisted-tree-removal',
        'stump-grinding-removal',
    ])) {
        ob_start('dli_replace_contact_us_links');
    }
}

function dli_replace_contact_us_links($html)
{
    $slug = '';

    if (is_singular('service-location')) {
        $slug = get_post_field('post_name', get_queried_object_id());
    } elseif (isset($_SESSION['dli_location_id'])) {
        $loc_post = get_post($_SESSION['dli_location_id']);
        if ($loc_post) {
            $slug = $loc_post->post_name;
        }
    }

    if (!$slug) return $html;

    $old = home_url('/contact-us/');
    $new = home_url("/{$slug}/contact-us/");
    return str_replace('href="' . $old . '"', 'href="' . $new . '"', $html);
}

// 6) Rewrite service links on service/location/contact pages
add_action('template_redirect', 'dli_buffer_service_links');
function dli_buffer_service_links()
{
    if (is_singular('service-location') || is_page([
        'contact-us',
        'arborist-consultation',
        'tree-removal',
        'tree-shrub-trimming-pruning',
        'emergency-tree-services',
        'commercial-tree-services',
        'crane-assisted-tree-removal',
        'stump-grinding-removal',
    ])) {
        ob_start('dli_replace_service_links');
    }
}

function dli_replace_service_links($html)
{
    $slug = '';

    if (is_singular('service-location')) {
        $slug = get_post_field('post_name', get_queried_object_id());
    } elseif (isset($_SESSION['dli_location_id'])) {
        $loc_post = get_post($_SESSION['dli_location_id']);
        if ($loc_post) {
            $slug = $loc_post->post_name;
        }
    }

    if (!$slug) return $html;

    $services = [
        'arborist-consultation',
        'tree-removal',
        'tree-shrub-trimming-pruning',
        'emergency-tree-services',
        'commercial-tree-services',
        'crane-assisted-tree-removal',
        'stump-grinding-removal',
    ];

    foreach ($services as $service_slug) {
        $original = home_url("/$service_slug/");
        $localized = home_url("/{$slug}/$service_slug/");
        $html = str_replace('href="' . $original . '"', 'href="' . $localized . '"', $html);
    }

    return $html;
}

// 7) Clear session on specific static pages
add_action('wp', function () {
    $pages_to_clear = ['home', 'why-us'];
    if (is_page($pages_to_clear)) {
        unset($_SESSION['dli_location_id']);
        unset($_SESSION['dli_ghl_embed']);
    }
});

// 8) Optional: GHL embed capture from location page content (fallback)
add_filter('the_content', 'dli_capture_ghl_iframe');
function dli_capture_ghl_iframe($content)
{
    if (!is_singular('service-location')) return $content;

    if (preg_match('/(<iframe[^>]*data-form-id=.*?<\/iframe>\s*<script[^>]*src=["\']?[^>]*\/form_embed\.js[^>]*><\/script>)/is', $content, $matches)) {
        $_SESSION['dli_ghl_embed'] = $matches[1];
    }

    return $content;
}

// 9) Helper: Get dynamic contact field
function get_dynamic_location_info($field)
{
    if (isset($_SESSION['dli_location_id'])) {
        return esc_html(get_post_meta($_SESSION['dli_location_id'], $field, true));
    }
    return '';
}

// 10) Helper: Determine if we are on a scoped (location-wise) service page URL.
function dli_is_scoped_service_page()
{
    $slug = get_query_var('location_slug');

    // Fallback: if rewrite/query_var didn't set location_slug (some permalink/caching cases),
    // infer it from the request path for localized service/contact URLs: /{location}/{service}/
    if (! $slug && (is_page('contact-us') || is_page([
        'arborist-consultation',
        'tree-removal',
        'tree-shrub-trimming-pruning',
        'emergency-tree-services',
        'commercial-tree-services',
        'crane-assisted-tree-removal',
        'stump-grinding-removal',
    ]))) {
        $path = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        $path = (string) parse_url($path, PHP_URL_PATH);
        $path = trim($path, '/');

        if ($path !== '') {
            $parts = explode('/', $path);
            // Expected: [0] location-slug, [1] page-slug
            if (count($parts) >= 2) {
                $candidate = sanitize_title($parts[0]);
                if ($candidate && $candidate !== 'service-location') {
                    $maybe = get_page_by_path($candidate, OBJECT, 'service-location');
                    if ($maybe && isset($maybe->ID)) {
                        $slug = $candidate;
                    }
                }
            }
        }
    }

    if (! $slug) return false;

    // Only run on the service pages that are localized via rewrite rules.
    $services = [
        'arborist-consultation',
        'tree-removal',
        'tree-shrub-trimming-pruning',
        'emergency-tree-services',
        'commercial-tree-services',
        'crane-assisted-tree-removal',
        'stump-grinding-removal',
    ];

    return is_page($services);
}

// 11) Helper: Get the current Location ID from session (if available).
function dli_get_current_location_id()
{
    if (! session_id()) {
        session_start();
    }
    return isset($_SESSION['dli_location_id']) ? (int) $_SESSION['dli_location_id'] : 0;
}

// 12) Helper: Get the current Service Page ID (only when on a service page).
function dli_get_current_service_page_id()
{
    if (is_page()) {
        return (int) get_queried_object_id();
    }
    return 0;
}

// 13) Helper: Find the override post (CPT: location-service-seo) for (location_id + service_page_id).
function dli_get_location_service_override_post_id($location_id, $service_page_id)
{
    static $cache = [];
    $key = $location_id . ':' . $service_page_id;
    if (isset($cache[$key])) {
        return (int) $cache[$key];
    }

    if (! $location_id || ! $service_page_id) {
        $cache[$key] = 0;
        return 0;
    }

    $q = new WP_Query([
        'post_type'      => 'location-service-seo',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            [
                'key'   => 'location',
                'value' => $location_id,
                'compare' => '='
            ],
            [
                'key'   => 'service_page',
                'value' => $service_page_id,
                'compare' => '='
            ],
        ],
    ]);

    $id = ($q->have_posts()) ? (int) $q->posts[0] : 0;
    $cache[$key] = $id;
    return $id;
}

// 14) Helper: Get current override post ID (only for scoped service pages).
function dli_get_current_override_post_id()
{
    if (! dli_is_scoped_service_page()) return 0;

    $location_id = dli_get_current_location_id();
    $service_id  = dli_get_current_service_page_id();

    return dli_get_location_service_override_post_id($location_id, $service_id);
}

// 15) Rank Math: Override Title + Meta Description for scoped service pages when ACF fields exist.
add_filter('rank_math/frontend/title', function ($title) {
    if (! dli_is_scoped_service_page()) return $title;
    $override_id = dli_get_current_override_post_id();
    if (! $override_id) return $title;

    $custom = get_post_meta($override_id, 'meta_title', true);
    $custom = is_string($custom) ? trim($custom) : '';
    return $custom !== '' ? wp_strip_all_tags($custom) : $title;
}, 20);

add_filter('rank_math/frontend/description', function ($description) {
    if (! dli_is_scoped_service_page()) return $description;
    $override_id = dli_get_current_override_post_id();
    if (! $override_id) return $description;

    $custom = get_post_meta($override_id, 'meta_description', true);
    $custom = is_string($custom) ? trim($custom) : '';
    return $custom !== '' ? wp_strip_all_tags($custom) : $description;
}, 20);

// 16) Rank Math: Append Custom JSON-LD schema for scoped service pages when provided.
add_filter('rank_math/json_ld', function ($data, $json) {
    if (! dli_is_scoped_service_page()) return $data;
    $override_id = dli_get_current_override_post_id();
    if (! $override_id) return $data;

    $schema_raw = get_post_meta($override_id, 'schema_jsonld', true);
    $schema_raw = is_string($schema_raw) ? trim($schema_raw) : '';
    if ($schema_raw === '') return $data;

    $decoded = json_decode($schema_raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
        return $data; // Invalid JSON; don't break existing schema.
    }

    // Add under a unique key to avoid collisions.
    $data['dli_custom'] = $decoded;
    return $data;
}, 20, 2);

// 17) Phase 2: Register CPT for slot-based Elementor template overrides (ACF Free friendly).
add_action('init', 'dli_register_location_service_block_cpt', 11);
function dli_register_location_service_block_cpt()
{
    if (post_type_exists('loc-svc-block')) {
        return;
    }

    $labels = [
        'name'               => 'Location Service Blocks',
        'singular_name'      => 'Location Service Block',
        'menu_name'          => 'Location Service Blocks',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Block',
        'edit_item'          => 'Edit Block',
        'new_item'           => 'New Block',
        'view_item'          => 'View Block',
        'search_items'       => 'Search Blocks',
        'not_found'          => 'No blocks found',
        'not_found_in_trash' => 'No blocks found in Trash',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'supports'           => ['title'],
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-layout',
        'has_archive'        => false,
        'rewrite'            => false,
        'query_var'          => false,
        'show_in_rest'       => true,
    ];

    register_post_type('loc-svc-block', $args);
}

// 18) Phase 2: Query template IDs for a given (location + service + slot). Supports multiple templates with priority ordering.
function dli_get_location_service_block_template_ids($location_id, $service_page_id, $slot)
{
    static $cache = [];

    $location_id     = (int) $location_id;
    $service_page_id = (int) $service_page_id;
    $slot            = is_string($slot) ? trim($slot) : '';

    $key = $location_id . ':' . $service_page_id . ':' . $slot;
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    if (! $location_id || ! $service_page_id || $slot === '') {
        $cache[$key] = [];
        return [];
    }

    $q = new WP_Query([
        'post_type'      => 'loc-svc-block',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'orderby'        => [
            'meta_value_num' => 'ASC',
            'ID'             => 'ASC',
        ],
        'meta_key'       => 'priority',
        'meta_query'     => [
            [
                'key'     => 'location',
                'value'   => $location_id,
                'compare' => '=',
            ],
            [
                'key'     => 'service_page',
                'value'   => $service_page_id,
                'compare' => '=',
            ],
            [
                'key'     => 'slot',
                'value'   => $slot,
                'compare' => '=',
            ],
        ],
    ]);

    $template_ids = [];

    if ($q->have_posts()) {
        foreach ($q->posts as $block_id) {
            // ACF Post Object field 'template' should store the template post ID.
            $template_id = get_post_meta((int) $block_id, 'template', true);

            if (is_array($template_id)) {
                $template_id = reset($template_id);
            }

            $template_id = (int) $template_id;
            if ($template_id > 0) {
                $template_ids[] = $template_id;
            }
        }
    }

    $cache[$key] = $template_ids;
    return $template_ids;
}

// 19) Phase 2: Render an Elementor template by ID (fallback to post content if Elementor is unavailable).
function dli_render_elementor_template_by_id($template_id)
{
    $template_id = (int) $template_id;
    if (! $template_id) return '';

    // Elementor render (preferred).
    if (class_exists('\\Elementor\\Plugin')) {
        $content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($template_id, false);
        if (is_string($content) && trim($content) !== '') {
            return $content;
        }
    }

    // Fallback: raw content (filters apply).
    $post = get_post($template_id);
    if (! $post) return '';
    return apply_filters('the_content', $post->post_content);
}
