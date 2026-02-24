=== Dynamic Location Info ===
Contributors: Helani Thejangi
Tags: elementor, acf, rank math, local seo, dynamic content, location pages
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.18.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Dynamically change contact details, footer paragraph, SEO meta, and Elementor section blocks per Service Location + Service Page using ACF and Rank Math.

== Description ==

Dynamic Location Info enables location-aware content and SEO for Elementor websites that use Service Location pages and location-scoped URLs.

It supports:
- Location-wise dynamic contact details (address, email, phone, social links)
- Location-wise Contact page and Service page URLs (e.g. /{location-slug}/contact-us/ and /{location-slug}/{service-slug}/)
- Location-wise footer paragraph via ACF field `location_footer_paragraph`
- Rank Math SEO overrides per Location + Service using ACF fields `meta_title`, `meta_description`, and `schema_jsonld`
- Elementor-friendly text overrides via shortcodes with fallback defaults
- Phase 2 slot-based Elementor template rendering (multiple templates per slot) without ACF Pro repeaters

The plugin uses a session-based "current location" context and rewrite rules to keep links and content consistent across location-scoped pages.

== Features ==

1. Location Context (Session)
- Stores the active location post ID in session while browsing location-wise pages.
- Automatically updates location context when visiting a Service Location single page.

2. Location-scoped URLs
- Supports URLs like:
  - /{location-slug}/contact-us/
  - /{location-slug}/{service-slug}/

3. Dynamic shortcodes (Phase 1)
- [dynamic_address]
- [dynamic_email]
- [dynamic_phone]
- [dynamic_social_icons]
- [dynamic_ghl_form]
- [dynamic_footer_paragraph] (supports fallback default)

4. Rank Math SEO overrides (Phase 1)
For location-scoped service URLs only, the plugin can override:
- Meta title (ACF: meta_title)
- Meta description (ACF: meta_description)
- JSON-LD schema injection (ACF: schema_jsonld)

5. Elementor text overrides (Phase 1)
Use shortcodes in Elementor widgets with fallback:
- [dli_text key="custom_h1" fallback="Default H1"]
- [dli_text key="tree_care_h2" fallback="Default H2"]
- [dli_text key="intro_h2" fallback="Default H2"]

6. Phase 2: Slot-based Elementor Templates (no ACF Pro required)
- Render Elementor templates per location+service into named slots:
  - [dli_section slot="when_call_arborist"]
  - [dli_section slot="why_choose_arborist" default_template="123"]
- Supports multiple templates per slot using a dedicated CPT and a priority field.

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/ or install via ZIP in WP Admin.
2. Activate the plugin.
3. Go to Settings → Permalinks and click "Save Changes" once to flush rewrite rules.

== Setup ==

This plugin requires:
- Advanced Custom Fields (ACF)
- Elementor (for templates/shortcodes usage)
- Rank Math (only if you want SEO overrides)

=== A) Service Locations ===
Create a custom post type for locations (example: "service-location") and create location posts (e.g. binghamton-ny, trenton-nj).

Add ACF fields to the location post type:
- location_address
- location_email
- location_phone_number
- location_ghl_form (optional)
- location_footer_paragraph

=== B) SEO Overrides per Location + Service (CPT: location-service-seo) ===
Create a CPT (managed by your site setup) for records that map a Location + Service Page to SEO/text overrides.

Recommended ACF fields on this CPT:
Required mapping fields:
- location (Post Object → service-location, return ID)
- service_page (Post Object → Page, return ID)

SEO fields:
- meta_title
- meta_description
- schema_jsonld (paste raw JSON only, no <script> tag)

Optional text override fields:
- custom_h1
- tree_care_h2
- intro_h2
(You can add any additional text fields and reference them with [dli_text].)

=== C) Phase 2 Blocks (CPT: loc-svc-block) ===
The plugin registers a CPT: loc-svc-block (Location Service Blocks).

Create an ACF field group for post type: loc-svc-block with fields:
- location (Post Object → service-location, return ID)
- service_page (Post Object → Page, return ID)
- slot (Text)
- template (Post Object → elementor_library, return ID)
- priority (Number, e.g. 10, 20, 30...)

Then place [dli_section slot="your_slot"] in Elementor where the slot should render.

== Shortcodes ==

Dynamic location fields:
- [dynamic_address]
- [dynamic_email]
- [dynamic_phone]
- [dynamic_social_icons]
- [dynamic_ghl_form]
- [dynamic_footer_paragraph]
  - Optional: [dynamic_footer_paragraph fallback="Your default text"]

Elementor text override:
- [dli_text key="FIELD_NAME" fallback="Default text"]

Elementor section slot (Phase 2):
- [dli_section slot="slot_name"]
- [dli_section slot="slot_name" default_template="123"]

== Frequently Asked Questions ==

= Why do I need to flush permalinks? =
Rewrite rules are used for location-scoped URLs. Save permalinks once after activation or slug changes.

= Will this create real WordPress pages for each location-service URL? =
No. The plugin uses rewrite rules to serve location-scoped URLs from existing pages while changing content dynamically.

= Does Rank Math analyze each location-scoped URL inside the WordPress editor? =
Rank Math's on-page analyzer is tied to real WP posts/pages. However, crawler tools can analyze the location-scoped URL because it is a real public URL.

= Can I use basic HTML in ACF fields? =
Yes. Footer paragraph and dli_text outputs allow safe basic HTML.

= I use ACF Free. Can I still support multiple templates per slot? =
Yes. Phase 2 uses a dedicated CPT (loc-svc-block) instead of repeaters.

== Screenshots ==

1. Location-wise service URL rendering with dynamic contact details.
2. ACF location footer paragraph field changing per location.
3. Rank Math title/description overrides on a location-scoped service URL.
4. Phase 2 slot rendering using Elementor templates per location-service.

== Changelog ==

= 1.18.2 =
* Fix: PHP syntax error caused by incorrect namespace backslashes in Elementor template render call.
* Phase 2: Stable slot rendering for Elementor templates.

= 1.18.1 =
* Change: Phase 2 CPT key updated to meet 20-character limit requirements.

= 1.18.0 =
* Feature: Phase 2 slot-based Elementor template rendering.
* Feature: Supports multiple templates per slot with priority ordering.
* Feature: default_template support in [dli_section].

= 1.17.2 =
* Fix: [dynamic_footer_paragraph] fallback default behavior aligned with other dynamic shortcodes.

= 1.17.1 =
* Fix: Improved location context switching by inferring location slug from URL when query var is missing.

= 1.17.0 =
* Feature: Phase 1 Rank Math SEO overrides (meta title, meta description, schema JSON-LD).
* Feature: Elementor-friendly text overrides with fallback via [dli_text].

= 1.16.0 =
* Feature: Location-wise footer paragraph via ACF field location_footer_paragraph.
* Improvement: Location context reliability on service-location pages.

== Upgrade Notice ==

= 1.18.2 =
Recommended update. Fixes a PHP syntax error and stabilizes Phase 2 Elementor template rendering.

== License ==

This plugin is licensed under the GPLv2 (or later).