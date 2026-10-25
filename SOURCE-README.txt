# Preloader Studio: human-readable source and reproduction

The PHP, CSS and JavaScript shipped in this WordPress.org distribution are
human-readable source files. No external build service, npm packages, bundled
transpiler, hidden repositories, or generated-only binary assets are required.

## JavaScript sources reviewed by the Plugins Team

| Source (editable) | Runtime script (installed and enqueued) |
| --- | --- |
| `assets/js/src/auth-custom.js` | `assets/js/auth-custom.js` |
| `assets/js/src/auth-suite-admin.js` | `assets/js/auth-suite-admin.js` |
| `assets/js/src/product-admin.js` | `assets/js/product-admin.js` |

The source and runtime scripts are deliberately identical. They are not
minified and use standard browser JavaScript without build-time transforms.
The source path is bundled for easy discovery by reviewers and contributors.

### Rebuild instructions

From the plugin's root directory, copy each desired source to its runtime path:

```
cp assets/js/src/auth-custom.js assets/js/auth-custom.js
cp assets/js/src/auth-suite-admin.js assets/js/auth-suite-admin.js
cp assets/js/src/product-admin.js assets/js/product-admin.js
```

To check JavaScript syntax with Node.js (optional):

```
node --check assets/js/auth-custom.js
node --check assets/js/auth-suite-admin.js
node --check assets/js/product-admin.js
```

PHP and CSS files are edited directly. The local Font Awesome Free Solid icon
vectors under `assets/icons/` are third-party derivatives. Their license,
original source and transformation are documented in `THIRD-PARTY-LICENSES.txt`.

## Backward compatibility

The namespace of internal PHP classes/constants and the plugin's account-form
actions was updated for the v2.6.5 review. Existing persisted option keys
(`ps_studio_*`) and customer custom-field user meta (`psx_field_*`) are
intentionally preserved to prevent settings and customer data loss. Keep these
legacy keys stable unless introducing an explicit data migration.
