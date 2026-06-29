=== Easy Search & Filter ===
Contributors: jaredquiero
Tags: search, filter, taxonomy, hierarchical, ajax, woocommerce, facets, cascading
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Universal hierarchical search and filter system for WordPress. Create dynamic taxonomies, N-level cascading dropdowns, and custom meta field filters — no code required.

== Description ==

Easy Search & Filter lets you build advanced search forms with hierarchical dropdowns that cascade dynamically. No coding, no ACF, no page builder required.

= Key Features =

* **N-Level Cascading Dropdowns** — Select a parent term and its children load via AJAX. Works for any number of levels.
* **Dynamic Taxonomy Manager** — Create hierarchical taxonomies from the WordPress admin panel with a custom tree UI.
* **Custom Meta Field Filters** — Add filters for any post meta: dropdown selects, number inputs, text inputs, range sliders, and checkboxes.
* **Auto-Detect Options** — Scan existing content to automatically populate select field options.
* **Any Post Type** — Works with posts, pages, WooCommerce products, and any custom post type.
* **Full AJAX Search** — Filter results without page reloads (Pro version).
* **Live Result Counts** — Show real-time counts next to each filter option via the built-in indexer (Pro version).
* **Sorting** — Sort results by date, title, or custom meta fields (Pro version).
* **Text Search** — Full-text search that combines with all other filters.
* **WooCommerce Compatible** — Works seamlessly when WooCommerce is active, but does not require it.
* **Gutenberg Blocks** — Insert filter forms and results using Gutenberg blocks (Pro version).
* **Template Overrides** — Copy templates to your theme's `/esp/` directory to customize the output.

= Use Cases =

* **Real Estate** — Filter properties by location hierarchy (country > state > city > neighborhood), price range, property type, and bedrooms.
* **E-Commerce** — Filter products by category hierarchy, brand, color, size, and price range.
* **Job Boards** — Filter jobs by industry > role > seniority, salary range, and location.
* **Directories** — Any business with hierarchical categorization and custom fields.

== Installation ==

1. Upload the `easy-search-filter` folder to the `/wp-content/plugins/` directory, or install directly from WordPress plugin repository.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Easy Search & Filter → Taxonomies** to create your first taxonomy.
4. Add terms to your taxonomy with parent-child relationships.
5. Go to **Easy Search & Filter → Filter Groups** to create a filter group combining taxonomies and meta fields.
6. Use the shortcode `[esp_search id="1"]` on any page or post to display the search form and results.

== Frequently Asked Questions ==

= Does it work with WooCommerce? =

Yes. Easy Search & Filter fully supports WooCommerce products as a post type target. The Pro version includes price sorting and range filtering compatible with WooCommerce's `_price` meta field. WooCommerce is optional — the plugin works with any post type.

= Can I use existing taxonomies? =

Currently, the plugin lets you create new taxonomies from the admin. You select which taxonomies to use in each filter group. Support for attaching to existing WordPress taxonomies (like WooCommerce product categories) is planned.

= Does it work with page builders like Elementor? =

Shortcodes work in any page builder that supports WordPress shortcodes. Gutenberg blocks are included in the Pro version.

= Is it multilingual? =

The plugin is fully translatable via `.pot` files and compatible with WPML and Polylang for multilingual sites.

= Can I customize the output? =

Yes. Copy the template files from `/templates/` to your theme's `/esp/` directory. The plugin will use your theme's versions.

= Does it work with caching plugins? =

Yes. AJAX-based filtering bypasses page cache. The indexer uses a custom database table for fast counting, compatible with most caching setups.

== Screenshots ==

1. Taxonomy manager — create hierarchical taxonomies with the custom admin UI.
2. Term tree — manage terms with the collapsible tree view, showing parent-child relationships.
3. Filter group editor — combine taxonomies and meta fields into a search form.
4. Frontend form — cascading dropdowns, range slider, and search input.
5. Results grid — filtered results with sorting and pagination.

== Changelog ==

= 1.0.0 =
* Initial release
* Dynamic hierarchical taxonomy management with custom admin UI
* N-level AJAX cascading dropdowns
* Custom meta field filters (select, number, text, range, checkbox)
* Auto-detect meta values from existing content
* Filter Group editor (combine taxonomies + meta fields)
* Template override system (theme/esp/)
* WooCommerce compatibility
* Gutenberg blocks (Pro)
* AJAX search with skeleton loading (Pro)
* Indexer with custom database table and post hooks (Pro)
* Dynamic sorting by date, title, and meta values (Pro)
* Active tags with individual filter removal
* Range slider (noUiSlider)
* Text search with debounced AJAX
* hide_zero_count with DOM-order-aware logic
* Responsive mobile layout
