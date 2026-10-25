=== Preloader Studio - Animated Loading Screens ===
Contributors: codelith
Tags: preloader, woocommerce, mobile menu, product layout, login
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.6.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create animated preloaders, responsive bottom menus, WooCommerce product layouts, and custom account forms with optional theme overrides.

== Description ==

Preloader Studio offers independent design tools for loading screens, mobile navigation, WooCommerce product layouts, and customer account experiences. The plugin can be used without WooCommerce; shopping and account-specific features require WooCommerce to be active.

**Core loading experience**

* Choose from 36 local CSS preloader animations with live previews.
* Use the site's logo, an uploaded image or animated GIF, and custom text.
* Customize animations, duration, background, exit effects, branding and responsive behavior.
* Configure page/device rules, safety timeout and theme-loader compatibility options.
* Save reusable preloader design presets and export/import settings.

**Mobile and tablet navigation**

* 24 navigation styles with local icons and per-device sizing options.
* Configure up to six menu destinations: Home, Search, published pages or custom links.
* Active-item styling, optional scroll-to-hide behavior, and mobile/tablet previews.
* Opt-in compatibility modes for known theme mobile toolbars. Theme settings are not overwritten.

**WooCommerce product design tools**

* 32 product card styles with real product images and responsive preview.
* Configure grid columns, display elements, image ratios and category rules.
* Optional replacement of supported classic WooCommerce product-loop templates.
* Optional matching single-product layout, with WooCommerce product hooks retained.
* Product builders using independent markup may require the plugin shortcode or additional integration; this is not a universal override for every page builder.

**Login, registration and account designs**

* Six independent Login/Register/Password Recovery design templates.
* Optional takeover of the WooCommerce guest My Account screen.
* Optional premium logged-in account dashboard; WooCommerce account endpoints remain available.
* Add up to 12 extra registration fields of supported types, stored in user metadata.
* Login and registration use WordPress/WooCommerce authentication and registration processing.
* Manual Default Restore switches designs off without deleting your saved appearance choices.

All overrides are separately configurable. Some third-party themes and page builders replace WooCommerce markup outside the standard PHP template hooks. Test each enabled integration on a staging site before publishing it.

No account, authentication data, site content or usage analytics are sent to a Preloader Studio server. All icons and default product demo pictures used in the plugin are bundled locally.

== Installation ==

1. Upload the plugin ZIP through **Plugins > Add New Plugin > Upload Plugin**, then activate it.
2. Open **Preloader Studio** in the WordPress administration menu.
3. Select a design in the feature-specific submenu. Preview, adjust and save it.
4. Bottom Navigation, Product Layout Override and independent Login/Register are opt-in; leave them off until configured.
5. For WooCommerce features, install and activate WooCommerce, then verify cart, checkout, variable products, and account endpoints.
6. If a theme displays its own loader or fixed bottom toolbar, try the theme's own disable setting first.
7. Purge page caches after changes and verify the frontend on real devices.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =
No. Loading screens and the mobile bottom navigation work without it. WooCommerce product and account integrations require WooCommerce.

= Does it change my theme files? =
No. Optional frontend overrides use WordPress and WooCommerce hooks/templates. Disable an override to restore the theme's normal rendering. Third-party builders may require separate integration.

= Will it replace every Elementor or WoodMart widget automatically? =
No. Builders with custom markup are not guaranteed to use the WooCommerce loop hooks. The included product shortcode can be used as an alternative product grid where appropriate.

= Can I choose my own logo or animated GIF? =
Yes. Choose an existing site logo or upload an image through the WordPress Media Library.

= Are Login, Register and Reset Password independent templates? =
Yes, the optional independent account experience renders its own forms while retaining the core WordPress/WooCommerce account and security systems. Test CAPTCHA, two-factor login, mail delivery and password recovery with your installed security plugins before turning on the feature for visitors.

= Can I restore the original login, account and product pages? =
Yes. Each design override can be disabled without deleting theme files or customer accounts.

= Is a preloader's loading percentage actual network transfer progress? =
No. The optional displayed percentage is a visual estimate, not measured bytes downloaded.

== Privacy and Uninstall ==

The plugin saves configuration in WordPress options and optional customer registration field values as user metadata (`psx_field_` prefix). A salted hash derived from IP address may be used as a short-lived rate-limit transient for the plugin's independent authentication endpoints; raw IP values are not sent to an external service. Existing WooCommerce/WordPress account records remain with the website owner.

When the plugin is uninstalled, plugin configuration options are removed. Customer profile metadata is intentionally retained to prevent accidental loss of customer information. Website administrators can handle any requested data deletion using their applicable WordPress privacy workflows. Uploaded logos remain in the Media Library and are not deleted automatically.

== Third-party resources ==

Font Awesome Free Solid icon vector paths are included locally. They are attributed to Fonticons, Inc. under the CC BY 4.0 icons license; see `THIRD-PARTY-LICENSES.txt`. No external scripts, font files or remote assets are loaded by the plugin. Product sample photographs are bundled demo assets, not actual store inventory.

== Source code and development ==

All runtime PHP, CSS, and JavaScript is distributed as human-readable, editable source inside the plugin ZIP.
For the three WordPress.org review-flagged JavaScript files, maintained readable
source copies are included in `assets/js/src/`:

* `assets/js/src/auth-custom.js` corresponds to `assets/js/auth-custom.js`.
* `assets/js/src/auth-suite-admin.js` corresponds to `assets/js/auth-suite-admin.js`.
* `assets/js/src/product-admin.js` corresponds to `assets/js/product-admin.js`.

No compiler, bundler, npm packages, or remote build service is required. JavaScript
sources can be copied to their corresponding `assets/js/` runtime paths; see
`SOURCE-README.txt` for reproduction instructions. The plugin's own PHP and CSS are
editable in place. The Font Awesome Free vector data is a third-party resource;
its source, license and adaptation are documented in `THIRD-PARTY-LICENSES.txt`.

Legacy `ps_studio_*` option names and `psx_field_*` customer metadata are retained
to preserve existing configurations during updates; these identifiers belong to
this plugin and must not be renamed without a migration.

== Changelog ==

= 2.6.5 =
* Review compliance: replace direct style output with accessible JavaScript-optional tabs and WordPress enqueue APIs.
* Remove automatic frontend attribution, use the core login URL helper, and correct WordPress.org contributor metadata.
* Ship readable JavaScript sources and build documentation alongside the runtime files.
* Apply distinctive namespace-style prefixes to PHP declarations and form actions without renaming existing saved user settings.
* Audit administrator capabilities, CSRF protections and independent authentication handlers.

= 2.6.4 =
* Align translation text domain and package directory with the WordPress.org submission slug; keep the Tested up to value only in readme.txt.
* Address Plugin Check escaping and PHP global-variable naming reports.
* Document safe read-only query flags and preserve core WooCommerce product and registration hooks.
* Update WordPress.org compatibility metadata to WordPress 7.1 (full site integration testing remains required).


= 2.6.3 =
* Hardened input/output handling, direct-file access guards and translation metadata for WordPress.org review.
* Updated documentation, privacy notes, third-party license details and uninstall behavior.
* Preserved the standard WordPress registration lifecycle for independent registration.

= 2.6.1 =
* Improved logged-in WooCommerce My Account dashboard rendering with optional force template mode.
* Adjusted authentication notice styling and overlap in supported theme layouts.

= 2.6.0 =
* Added customizable registration fields and premium member account dashboard.

= 2.5.0 =
* Added six plugin-rendered login, registration and password recovery templates.

= 2.3.3 =
* Improved conditional assets and responsive WooCommerce product detail tabs.

= 2.3.0 =
* Added optional single-product layout matching and bottom navigation scroll behavior.

= 2.0.0 =
* Added 32 responsive WooCommerce product card layouts with preview and opt-in theme overrides.

= 1.3.0 =
* Added mobile/tablet bottom navigation studio.

= 1.0.0 =
* Initial animated preloader studio release.

== Upgrade Notice ==

= 2.6.4 =
Back up your site, test WordPress and WooCommerce account flows on staging, and rerun Plugin Check before WordPress.org submission.
