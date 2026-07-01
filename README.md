# Magento 2 Advanced ElasticSuite Catalog

Magento 2 module enhancing ElasticSuite Catalog for Hyvä Theme.
Adds AJAX-based layered navigation, infinite scroll, and dynamic pagination — all without page reloads.

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^7.4 \| ^8.0` |
| `smile/elasticsuite` | `~2.10.9 \| ~2.11.0` |
| `hyva-themes/magento2-smile-elasticsuite` | `^1.2` |
| Hyvä Theme | `>=1.2.x` |

## Features

- AJAX layered navigation — filters apply without page reloads (Alpine.js)
- Multiselect attribute filters with fulltext search and show more/less
- Multiselect swatch filters (color, image, text) with tooltip support
- Range slider filters for price and custom attributes
- Infinite scroll with configurable button label
- Standard pagination with AJAX page loading
- Loads products from previous pages on direct URL access (`?p=N`)
- Works on both category pages and catalog search results
- Hyvä-compatible star rating templates on category pages
- Varnish Cache compatible

## Installation

```bash
composer require "shellpea/magento-advanced-elasticsuite-catalog":"^1.2.10"
bin/magento module:enable Shellpea_AdvancedElasticsuiteCatalog
bin/magento setup:upgrade
bin/magento setup:di:compile
```

> For Hyvä Theme `<=1.1.23` use `^1.0.0` instead.

## Admin Configuration

**Stores → Configuration → Smile ElasticSuite → Advanced ElasticSuite Catalog**

### General
| Field | Description |
|---|---|
| Active | Enable/disable the module |

### Infinite Scroll
| Field | Description |
|---|---|
| Active | Enable infinite scroll. When disabled, standard pagination is used |
| Button Label | Text on the "load more" button |

![Infinite Scroll button](https://user-images.githubusercontent.com/55882198/236670329-ba761d13-45a4-41a5-8723-f130d88926dd.png)

### Slider Configuration
| Field | Description |
|---|---|
| Direct Mode Active | Apply slider range immediately on change without clicking OK |

![Admin Configurations](https://user-images.githubusercontent.com/55882198/236669994-8ddb17f9-8f37-445d-9bf0-6e039c6ddd67.png)

## Varnish Cache

If AJAX requests return cached full-page responses, add the following to `sub vcl_hash` in your VCL:

```vcl
# Separate cache entries for XMLHttpRequest
if (req.http.X-Requested-With) {
    hash_data(req.http.X-Requested-With);
}
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).
