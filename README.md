# Dynamic Location Info

Dynamically change contact details, footer paragraph, SEO meta, and Elementor section blocks per **Service Location + Service Page** using **ACF**, **Elementor**, and **Rank Math**.

This plugin is designed for sites that use **location-scoped URLs** like:

- `/{location-slug}/contact-us/`
- `/{location-slug}/{service-slug}/`

…and want content + SEO to change based on the active location.

---

## Features

### Location context (Session)
- Stores the active location in a session (`dli_location_id`) while browsing location-wise pages.
- Automatically updates location context when viewing a `service-location` single page.

### Dynamic shortcodes (Location fields)
Use these in Elementor or any editor:
- `[dynamic_address]`
- `[dynamic_email]`
- `[dynamic_phone]`
- `[dynamic_social_icons]`
- `[dynamic_ghl_form]`
- `[dynamic_footer_paragraph]`  
  - Optional: `[dynamic_footer_paragraph fallback="Your default footer text"]`

### Phase 1: Rank Math SEO overrides (per Location + Service)
On location-scoped service URLs only, the plugin can override:
- Meta title (ACF: `meta_title`)
- Meta description (ACF: `meta_description`)
- JSON-LD schema injection (ACF: `schema_jsonld` — raw JSON only)

### Phase 1: Elementor text overrides (with default fallback)
Use shortcodes in Elementor widgets:
- `[dli_text key="custom_h1" fallback="Default H1"]`
- `[dli_text key="tree_care_h2" fallback="Default H2"]`
- `[dli_text key="intro_h2" fallback="Default H2"]`

If no override value exists, the `fallback` text is displayed.

### Phase 2: Slot-based Elementor Template rendering (ACF Free friendly)
Render Elementor templates into named slots:
- `[dli_section slot="when_call_arborist"]`
- `[dli_section slot="why_choose_arborist" default_template="123"]`

Supports:
- Multiple templates per slot (ordered by priority)
- Default template fallback via `default_template=...`
- No ACF Pro repeater required (uses a dedicated CPT instead)

---

## Requirements

- WordPress 6.0+
- PHP 7.4+
- **ACF** (required)
- **Elementor** (required for template/slot rendering)
- **Rank Math** (optional — only needed for SEO overrides)

---

## Installation

1. Upload the plugin folder to `wp-content/plugins/` (or install ZIP from WP Admin).
2. Activate the plugin.
3. Go to **Settings → Permalinks → Save Changes** (flush rewrite rules once).

---

## Setup

### A) Location CPT fields (Service Location)
Your location CPT should be `service-location` (or equivalent).

Add these ACF fields to the location post type:
- `location_address`
- `location_email`
- `location_phone_number`
- `location_ghl_form` (optional)
- `location_footer_paragraph`

### B) Location + Service SEO overrides (CPT: `location-service-seo`)
Create/keep a CPT where each post represents one Location + Service pair.

**Required mapping fields** (ACF Post Object fields, return ID):
- `location` → `service-location`
- `service_page` → WordPress Page

**SEO fields**:
- `meta_title`
- `meta_description`
- `schema_jsonld` (raw JSON only)

**Optional text override fields** (examples):
- `custom_h1`
- `tree_care_h2`
- `intro_h2`

Use them in Elementor via `[dli_text key="FIELD_NAME" fallback="..."]`.

### C) Phase 2 blocks (CPT key: `loc-svc-block`)
The plugin registers a CPT key: `loc-svc-block`

Create an ACF Field Group for post type `loc-svc-block` with:

- `location` (Post Object → `service-location`, return ID)
- `service_page` (Post Object → Page, return ID)
- `slot` (Text)
- `template` (Post Object → `elementor_library`, return ID)
- `priority` (Number: 10, 20, 30...)

**Elementor usage**
Place slots in your service page where content may vary:
- `[dli_section slot="when_call_arborist"]`
- `[dli_section slot="why_choose_arborist" default_template="123"]`

To override for a location, create `loc-svc-block` posts that target the location + service page + slot.

---

## Slot naming guide (important)
Use **generic** slot names (not location-specific), e.g.:
- `when_call_arborist`
- `why_choose_arborist`

Avoid:
- `when_call_binghamton_arborist`  
Slot names should be reusable across all locations.

---

## Development notes

- After changing service slugs used in localized URLs, flush permalinks again.
- If page cache is active (WP cache / Cloudflare), purge cache when testing dynamic location changes.

---

## License

This project is licensed under the **GNU General Public License v2.0 or later (GPL-2.0-or-later)**.
See `LICENSE` for details.