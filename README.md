# Easy Search & Filter

Universal hierarchical search and filter system for WordPress. Create dynamic taxonomies, cascading dropdowns of N levels, and custom meta field filters — no code required.

## Features

- **Dynamic Hierarchical Taxonomies** — Create unlimited taxonomies with any depth from the admin panel. No code, no ACF, no metabox plugins needed.
- **N-Level Cascading Dropdowns** — When a user selects a parent term, children load via AJAX. This works for any number of levels.
- **Custom Meta Field Filters** — Add filters for any post meta field: dropdown select, number input, text input, range (min/max), or checkbox. Supports all comparison operators.
- **Auto-Detect Options** — Scan existing content to automatically populate select field options.
- **Any Post Type** — Works with posts, pages, WooCommerce products, or any custom post type.
- **Full AJAX** — Cascading dropdowns and search results load without page reload.
- **Shortcodes & Gutenberg Blocks** — Embed forms and results anywhere.
- **Template Overrides** — Copy templates to your theme's `/esp/` directory to customize the output.
- **WooCommerce Compatible** — Works seamlessly when WooCommerce is active, but does not require it.

## Quick Start

### 1. Create Taxonomies

Go to **Easy Search & Filter > Taxonomies** and click **Add New**.

Each taxonomy is a hierarchical classification system (e.g., Location, Category, Brand, Department). Set:
- **Name** — Display name (e.g., "Location")
- **Slug** — URL-friendly identifier (e.g., "location")
- **Max Hierarchy Depth** — Maximum nesting levels (0 = unlimited)
- **Attach to Post Types** — Which post types this taxonomy applies to

### 2. Add Terms

After creating a taxonomy, click **Manage Terms** to add terms. Set parent-child relationships to build your hierarchy.

### 3. Create a Filter Group

Go to **Easy Search & Filter > Filter Groups** and click **Add New**.

A Filter Group combines taxonomies and meta field filters into a search form:

- **General tab** — Name and target post type
- **Taxonomies tab** — Select which taxonomies to include as filters. Order determines the cascading hierarchy.
- **Meta Fields tab** — Add custom meta field filters. For each field, configure:
  - Meta key (the field name in the database)
  - Display label
  - Field type (select, number, text, range, checkbox)
  - Comparison operator
  - Options (for select type — add manually or use auto-detect)
- **Display tab** — Results layout: grid/list, columns, items per page, ordering

### 4. Add to Your Site

Use the shortcodes on any page or post:

```
[esp_form id="1"]       // Search form
[esp_results id="1"]    // Results display
[esp_search id="1"]     // Both combined
```

Or use the **Easy Search & Filter** blocks in the Gutenberg editor.

## Templates

Override the default templates by copying them to your theme:

```
your-theme/esp/search-form.php
your-theme/esp/search-results.php
```

## AJAX Search

By default, the form submits via page reload. To enable full AJAX search (no page reload), add the attribute `data-esp-ajax="1"` to the `.esp-search-form` div, or enable it in the Pro version.

## Requirements

- WordPress 5.8+
- PHP 7.4+
- WooCommerce 5.0+ (optional)

## Changelog

### 1.0.0
- Initial release
- Dynamic hierarchical taxonomy management
- N-level cascading dropdowns with AJAX
- Custom meta field filters with auto-detect
- Gutenberg blocks
- Template override system
- WooCommerce compatibility
