<?php
// File: includes/shortcode.php

// 1) Dynamic Address
function dli_address_shortcode()
{
    if (! session_id()) {
        session_start();
    }
    $post_id = $_SESSION['dli_location_id'] ?? null;
    $addr    = $post_id
        ? get_post_meta($post_id, 'address', true)
        : '';
    return $addr ?: '';
}
add_shortcode('dynamic_address', 'dli_address_shortcode');

// 2) Dynamic Email
function dli_email_shortcode()
{
    if (! session_id()) {
        session_start();
    }
    $post_id = $_SESSION['dli_location_id'] ?? null;
    $email   = $post_id
        ? get_post_meta($post_id, 'location_email', true)
        : '';
    $email = $email ?: 'sales@kdtreeservices.com';
    return sprintf('<a href="mailto:%1$s">%1$s</a>', esc_attr($email));
}
add_shortcode('dynamic_email', 'dli_email_shortcode');

// 3) Dynamic Phone - Updated with optional brand text
function dli_phone_shortcode($atts = array())
{
    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'show_brand' => false, // Default to no branding
    ), $atts);

    // Convert show_brand to boolean
    $show_brand = filter_var($atts['show_brand'], FILTER_VALIDATE_BOOLEAN);

    if (! session_id()) {
        session_start();
    }
    $post_id = $_SESSION['dli_location_id'] ?? null;
    $phone   = $post_id
        ? get_post_meta($post_id, 'phone_number', true)
        : '';

    // If no phone number found, return default
    if (! $phone) {
        if ($show_brand) {
            return '<a href="tel:+18334087333"> (833) 408-7333 (TREE)</a>';
        } else {
            return '<a href="tel:+18334087333">(833) 408-7333</a>';
        }
    }

    $digits = preg_replace('/\D+/', '', $phone);
    if (strlen($digits) < 10) {
        return esc_html($phone);
    }

    $area   = substr($digits, 0, 3);
    $prefix = substr($digits, 3, 3);
    $line   = substr($digits, 6, 4);

    // Start with standard format
    $display_text = sprintf('(%s) %s-%s', $area, $prefix, $line);

    // Add brand text only if show_brand is true
    if ($show_brand) {
        if ($line === '8733') {
            $display_text = sprintf('(%s) %s-%s (TREE)', $area, $prefix, $line);
        }
        // You can add more brand mappings here as needed
        // elseif ($line === '1234') {
        //     $display_text = sprintf('(%s) %s-%s (BRAND)', $area, $prefix, $line);
        // }
    }

    return sprintf(
        '<a href="tel:+1%s">%s</a>',
        esc_attr($digits),
        esc_html($display_text)
    );
}
add_shortcode('dynamic_phone', 'dli_phone_shortcode');

// 3.5) Dynamic Footer Paragraph (allows basic HTML)
function dli_footer_paragraph_shortcode()
{
    $atts = shortcode_atts(
        array(
            // Default footer paragraph when a location-specific value is not available.
            'fallback' => 'Holistic tree care service with a personalized approach that focuses on every aspect of your treestrees, ensuring health, beauty, and longevity.',
        ),
        array()
    );

    if (! session_id()) {
        session_start();
    }

    $post_id = $_SESSION['dli_location_id'] ?? null;

    // Fallback: if session not set, but currently viewing a single location page
    if (! $post_id && is_singular('service-location')) {
        $post_id = get_queried_object_id();
    }

    // If location context is not available, return the default (same behavior as other dynamic fields).
    if (! $post_id) {
        return wp_kses_post(do_shortcode($atts['fallback']));
    }

    $raw = function_exists('get_field')
        ? get_field('location_footer_paragraph', $post_id)
        : get_post_meta($post_id, 'location_footer_paragraph', true);

    if (! $raw) {
        return wp_kses_post(do_shortcode($atts['fallback']));
    }

    // Allow shortcodes if present, then sanitize to basic safe HTML
    $raw = do_shortcode($raw);
    return wp_kses_post($raw);
}
add_shortcode('dynamic_footer_paragraph', 'dli_footer_paragraph_shortcode');

// 4) GHL Form Shortcode – ACF based
function dli_ghl_form_shortcode()
{
    if (! session_id()) {
        session_start();
    }

    if (! empty($_SESSION['dli_ghl_embed'])) {
        return $_SESSION['dli_ghl_embed'];
    }

    $slug = get_query_var('location_slug');
    if ($slug) {
        $loc = get_page_by_path($slug, OBJECT, 'service-location');
        if ($loc && isset($loc->ID)) {
            $embed = get_field('ghl_form_embed', $loc->ID);
            if ($embed) {
                $_SESSION['dli_ghl_embed'] = $embed;
                return $embed;
            }
        }
    }

    return do_shortcode('[contact-form-7 id="326" title="Contact Form"]');
}
add_shortcode('dynamic_ghl_form', 'dli_ghl_form_shortcode');

// 5) Individual Social Profile Shortcodes
function location_social_facebook_shortcode()
{
    if (! session_id()) {
        session_start();
    }
    if (! empty($_SESSION['dli_location_id'])) {
        $url = get_post_meta($_SESSION['dli_location_id'], 'facebook', true);
        return $url ? esc_url($url) : '';
    }
    return esc_url('https://www.facebook.com/kdtreeservicealbany/');
}
add_shortcode('location_facebook', 'location_social_facebook_shortcode');

function location_social_instagram_shortcode()
{
    if (! session_id()) {
        session_start();
    }
    if (! empty($_SESSION['dli_location_id'])) {
        $url = get_post_meta($_SESSION['dli_location_id'], 'instagram', true);
        return $url ? esc_url($url) : '';
    }
    return esc_url('https://www.instagram.com/kdtreeservice/');
}
add_shortcode('location_instagram', 'location_social_instagram_shortcode');

function location_social_pinterest_shortcode()
{
    if (! session_id()) {
        session_start();
    }
    if (! empty($_SESSION['dli_location_id'])) {
        $url = get_post_meta($_SESSION['dli_location_id'], 'pinterest', true);
        return $url ? esc_url($url) : '';
    }
    return esc_url('https://www.pinterest.com/kdtreeservicesbuffalo/');
}
add_shortcode('location_pinterest', 'location_social_pinterest_shortcode');

function location_social_x_shortcode()
{
    if (! session_id()) {
        session_start();
    }
    if (! empty($_SESSION['dli_location_id'])) {
        $url = get_post_meta($_SESSION['dli_location_id'], 'x', true);
        if ($url) {
            return esc_url($url);
        }
    }
    return '';
}
add_shortcode('location_x', 'location_social_x_shortcode');

function location_social_tiktok_shortcode()
{
    if (! session_id()) {
        session_start();
    }
    if (! empty($_SESSION['dli_location_id'])) {
        $url = get_post_meta($_SESSION['dli_location_id'], 'tik_tok', true);
        return $url ? esc_url($url) : '';
    }
    return esc_url('https://www.tiktok.com/@kdtreealbany');
}
add_shortcode('location_tik_tok', 'location_social_tiktok_shortcode');

// 6) One Shortcode to Render All Social Icons Conditionally
function location_social_icons_shortcode()
{
    if (! session_id()) {
        session_start();
    }

    if (! empty($_SESSION['dli_location_id'])) {
        $loc       = $_SESSION['dli_location_id'];
        $facebook  = get_post_meta($loc, 'facebook', true);
        $instagram = get_post_meta($loc, 'instagram', true);
        $pinterest = get_post_meta($loc, 'pinterest', true);
        $x         = get_post_meta($loc, 'x', true);
        $tiktok    = get_post_meta($loc, 'tik_tok', true);
    } else {
        // global defaults
        $facebook  = 'https://www.facebook.com/kdtreeservicealbany';
        $instagram = 'https://www.instagram.com/kdtreeservicealbany/';
        $pinterest = 'https://de.pinterest.com/kd_treealbany/';
        $x         = 'https://x.com/kd_treealbany';
        $tiktok    = 'https://www.tiktok.com/@kdtreealbany';
    }

    ob_start();
    echo '<div class="location-social-icons">';

    if ($facebook) {
        echo '<a href="' . esc_url($facebook) . '" target="_blank" aria-label="Facebook">';
        echo '<i class="fab fa-facebook-f"></i>';
        echo '</a>';
    }

    if ($instagram) {
        echo '<a href="' . esc_url($instagram) . '" target="_blank" aria-label="Instagram">';
        echo '<i class="fab fa-instagram"></i>';
        echo '</a>';
    }

    if ($pinterest) {
        echo '<a href="' . esc_url($pinterest) . '" target="_blank" aria-label="Pinterest">';
        echo '<i class="fab fa-pinterest-p"></i>';
        echo '</a>';
    }

    if ($x) {
        echo '<a href="' . esc_url($x) . '" target="_blank" aria-label="X (formerly Twitter)">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-hidden="true" focusable="false" style="width:1em; height:1em; vertical-align:-0.125em;">';
        echo '<path fill="currentColor" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>';
        echo '</svg>';
        echo '</a>';
    }

    if ($tiktok) {
        echo '<a href="' . esc_url($tiktok) . '" target="_blank" aria-label="TikTok">';
        echo '<i class="fab fa-tiktok"></i>';
        echo '</a>';
    }

    echo '</div>';
    return ob_get_clean();
}
add_shortcode('location_social_icons', 'location_social_icons_shortcode');

// --- Phase 1: Generic text override shortcode for Elementor ---
// Usage: [dli_text key="h1" fallback="Default Heading"]
function dli_text_shortcode($atts = [])
{
    $atts = shortcode_atts([
        'key'      => '',
        'fallback' => '',
    ], $atts);

    $key = is_string($atts['key']) ? trim($atts['key']) : '';
    $fallback = is_string($atts['fallback']) ? $atts['fallback'] : '';

    if ($key === '') {
        return wp_kses_post(do_shortcode($fallback));
    }

    // Only apply overrides on scoped service pages (location-wise URLs).
    if (! function_exists('dli_get_current_override_post_id')) {
        return wp_kses_post(do_shortcode($fallback));
    }

    $override_id = dli_get_current_override_post_id();
    if (! $override_id) {
        return wp_kses_post(do_shortcode($fallback));
    }

    // 1) Try a direct field/meta with the same name as the key.
    $value = get_post_meta($override_id, $key, true);
    if (is_string($value)) {
        $value = trim($value);
    } else {
        $value = '';
    }

    // 2) Try ACF get_field if meta returned empty but field exists.
    if ($value === '' && function_exists('get_field')) {
        $acf_val = get_field($key, $override_id);
        if (is_string($acf_val)) {
            $value = trim($acf_val);
        }
    }

    // 3) Optional future-proof: support a repeater field 'text_overrides' with rows { key, value }.
    if ($value === '' && function_exists('get_field')) {
        $rows = get_field('text_overrides', $override_id);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $row_key = isset($row['key']) ? trim((string) $row['key']) : '';
                if ($row_key === $key) {
                    $row_val = isset($row['value']) ? (string) $row['value'] : '';
                    $row_val = trim($row_val);
                    if ($row_val !== '') {
                        $value = $row_val;
                    }
                    break;
                }
            }
        }
    }

    // Fallback to default when empty.
    if ($value === '') {
        return wp_kses_post(do_shortcode($fallback));
    }

    // Allow basic HTML, and allow nested shortcodes.
    return wp_kses_post(do_shortcode($value));
}
add_shortcode('dli_text', 'dli_text_shortcode');

// Convenience alias for H1 (optional):
// Usage: [dli_h1 fallback="Default H1"]
function dli_h1_shortcode($atts = [])
{
    $atts['key'] = 'h1';
    return dli_text_shortcode($atts);
}
add_shortcode('dli_h1', 'dli_h1_shortcode');

// ------------------------------
// Phase 2: Slot-based section rendering (Elementor Templates)
// Usage:
//   [dli_section slot="when_call_arborist" default_template="123"]
// - slot: required (string)
// - default_template: optional Elementor template ID (used when no location-specific override exists)
// Supports multiple templates per slot via CPT: location-service-block (ordered by 'priority').
// ------------------------------
function dli_section_shortcode($atts)
{
    if (! session_id()) {
        session_start();
    }

    $atts = shortcode_atts([
        'slot'             => '',
        'default_template' => '',
    ], $atts, 'dli_section');

    $slot = is_string($atts['slot']) ? trim($atts['slot']) : '';
    // Keep slot flexible: allow a-z, 0-9, underscore, hyphen
    $slot = preg_replace('/[^a-zA-Z0-9_\-]/', '', $slot);

    $default_template = (int) ($atts['default_template'] ?? 0);

    if ($slot === '') {
        return '';
    }

    // Resolve current context
    $location_id = function_exists('dli_get_current_location_id') ? (int) dli_get_current_location_id() : 0;
    $service_id  = function_exists('dli_get_current_service_page_id') ? (int) dli_get_current_service_page_id() : 0;

    $output = '';

    // If we have a location + service context, try location-specific templates first.
    if ($location_id && $service_id && function_exists('dli_get_location_service_block_template_ids')) {
        $template_ids = dli_get_location_service_block_template_ids($location_id, $service_id, $slot);

        if (! empty($template_ids) && is_array($template_ids)) {
            foreach ($template_ids as $tid) {
                if (function_exists('dli_render_elementor_template_by_id')) {
                    $output .= dli_render_elementor_template_by_id((int) $tid);
                }
            }
        }
    }

    // Fallback: default template (renders on normal pages too, when provided)
    if (trim($output) === '' && $default_template > 0 && function_exists('dli_render_elementor_template_by_id')) {
        $output = dli_render_elementor_template_by_id($default_template);
    }

    // Allow nested shortcodes inside template output.
    if (is_string($output) && $output !== '') {
        $output = do_shortcode($output);
    }

    return $output;
}
add_shortcode('dli_section', 'dli_section_shortcode');
