# Changelog

## [1.2.10] - 2026-07-01

### Fixed

- `AfterSearchView`: added missing `$result` parameter to `afterExecute` after-plugin method.
  Without it, Magento substituted `null` as the controller result for all non-AJAX search requests,
  causing a blank page on `/catalogsearch/result/`.
- `AfterCategoryView`, `AfterSearchView`, `AddAjaxToCache`: replaced direct `$_SERVER['HTTP_X_REQUESTED_WITH']`
  access with `Magento\Framework\App\Request\Http::isXmlHttpRequest()` via proper DI injection.
- `catalog/layer/filter/default.phtml`: added `@click.prevent` Alpine.js handler to category filter links.
  Clicks previously triggered full page reloads instead of AJAX updates.
- `AjaxResponse::processSwatchAttributeFilter`: added empty-array guard before accessing `$attributeOptionId[0]`.
  A swatch attribute with no matching option ID caused an undefined index error.
- `ListProduct::getProductsFromPrevPagesSearch`: added `isAjax()` guard, consistent with
  `getProductsFromPrevPages()`. The method was executing an unnecessary ES query on every AJAX filter request.
- `configuration.phtml`: removed two leftover `console.log()` debug statements.

## [1.2.9] - 2026-01-26

### Fixed

- `AfterCategoryView`: extended `afterExecute` return type from `Page` to `Page|Forward`.
  Categories configured as a redirect (display mode "redirect") return a `Forward` result,
  which caused a PHP type error.

## [1.2.8] - 2024-07-05

### Fixed

- `ListProduct`: fixed typo in variable name inside `getProductsFromPrevPages()` — the inner loop
  variable `$optionLabel` shadowed the outer one, causing filters to be applied incorrectly
  when multiple values were selected for the same attribute.

## [1.2.7] - 2024-06-21

### Added

- `configuration.phtml`: added `getSliderFilterValues()` to distinguish slider filters from
  attribute filters during DOM updates.
- `configuration.phtml`: added `replaceSliderFilters()` — slider filter DOM nodes are now
  individually replaced after an AJAX response instead of being lost in a full filter-list replacement.
- `configuration.phtml`: added `loadPageWithPager()` and `afterPageLoadPager()` — pagination
  links now trigger AJAX page loads that replace the product grid and pagination block without
  touching the sidebar filters.

### Fixed

- `configuration.phtml`: pagination block is now updated after filter changes (`afterUpdateLayer`
  previously replaced only the product grid).
- `configuration.phtml`: product count sentence extracted to `generateSentence()` and made
  i18n-ready (`__('Items')`, `__('of')`). Sentence format now differs depending on whether
  Infinite Scroll is active.

## [1.2.6] - 2024-03-22

### Fixed

- `ListProduct::getSearchCollection`: sort order handling rewritten. `relevance` and `price`
  are now mapped to their correct ES fields; other values are passed through as-is. Previously
  all sort modes defaulted to `entity_id DESC`.

## [1.2.5] - 2024-02-01

### Changed

- `ListProduct`: added PHP return type declarations to all public methods (`getCacheKeyInfo`,
  `getProductsFromPrevPages`, `getProductsFromPrevPagesSearch`, `getPageSize`,
  `isInfinityActive`, `getOptionIdByLabel`, `getSearchCollection`).

### Fixed

- `ListProduct::getProductsFromPrevPages`: added missing `return ''` for the non-paginated
  branch, preventing a void-return PHP error.
- `ListProduct::getProductsFromPrevPagesSearch`: same missing `return ''` fix.

## [1.2.4] - 2023-11-28

### Added

- `Block/Product/ReviewRenderer`: custom review renderer block that points rating templates
  to module-local `helper/summary.phtml` and `helper/summary_short.phtml`, enabling
  Hyvä-compatible star ratings on category pages.
- `view/frontend/templates/helper/summary.phtml` and `summary_short.phtml`: Hyvä-styled
  rating summary templates (full and short variants).
- PHP `7.4` added to supported versions alongside `8.x`.

### Changed

- `product/list.phtml`: refactored to load products from previous pages on direct URL access
  with `p=N` (supports both category and search contexts).

## [1.2.3] - 2023-08-22

### Changed

- `configuration.phtml`: refactored Alpine.js store — property declarations moved before
  computed getters, extracted `generateSentence()` for product count display logic,
  replaced string interpolation templates with proper `Math.min` bounds check.
- `configuration.phtml`: use `window.location.href` instead of `window.location` for
  consistent URL string coercion across browsers.

## [1.2.2] - 2023-08-19

### Fixed

- `catalog/layer/filter/attribute.phtml`: filter selection state was not being reset after
  click — added `expanded = false` on filter link click so the expanded list collapses after
  a value is selected.
- `catalog/layer/filter/attribute.phtml`: fixed malformed `x-data` attribute (stray trailing
  quote character caused Alpine.js initialization failure in some browsers).

## [1.2.1] - 2023-06-25

### Added

- `Model/AjaxResponse`: extracted AJAX response assembly into a dedicated model class.
  Handles product list HTML, active filters HTML, filter items JSON, pagination metadata.
- `Block/ListProduct`: custom block extending `Magento\Catalog\Block\Product\ListProduct`
  with methods for loading products from previous pages (category and search contexts),
  enabling correct product display when navigating directly to page `p=N`.
- `Block/AdvancedCatalog`: injected `AjaxResponse` model; filter item processing
  delegated to the model.

### Changed

- `Block/AdvancedCatalog`: `getFilterItems()` now delegates to `AjaxResponse::getFilterItems()`
  instead of duplicating the logic inline.

## [1.2.0] - 2023-05-07

### Added

- Full support for Hyvä Theme `>=1.2.x` and Alpine.js v3.

### Changed

- Dependency updated: `hyva-themes/magento2-smile-elasticsuite` constraint changed
  from `~1.1.2` to `^1.2`.
- Dependency updated: `smile/elasticsuite` extended to `~2.10.9|~2.11.0`.

### Removed

- Spruce.js bundled library and related template removed; Alpine.js v3 `$store` API
  is used directly.

## [1.0.0] - 2022-12-10

### Added

- Initial release.
- AJAX-based layered navigation filtering without page reloads (Alpine.js + Hyvä Theme 1.1.x).
- Infinite Scroll with configurable button label.
- Standard pagination with Alpine.js-driven AJAX page loading.
- Range slider filters (price and custom attributes) with direct mode option.
- Multiselect attribute filters with fulltext search, show more/less.
- Multiselect swatch filters with tooltip support.
- Admin configuration: enable/disable module, infinite scroll, slider direct mode.
- Varnish cache compatibility via `X-Requested-With` header segregation.
