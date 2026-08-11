# Changelog

## [1.2.12] - 2026-07-14

### Fixed

- **Hyvä 1.5 compatibility.** Dropped the outdated fork of `swatch/product/listing/renderer.phtml`. Both
  `hyva_catalog_category_view.xml` and `hyva_catalogsearch_result_index.xml` now point
  `category.product.type.details.renderers.configurable` at the theme's own
  `Magento_Swatches::product/listing/renderer.phtml`, so the module follows whichever Hyvä version is installed.
  The fork rendered the shared `product.swatch.item` block once inside an Alpine `<template x-for>` and passed it
  only `product_id` / `attribute_id`. As of Hyvä 1.5 that block expands options server-side and throws
  `RuntimeException: product.swatch.item rendered without "option" data` when the fork is used.
  Note the layout override is still required: `Hyva_SmileElasticsuite` 1.2.8 points the same block at its own
  pre-1.5 template, which fails identically on Hyvä 1.5.x. Its only functional addition over the theme template
  was the drag-to-scroll swatch slider.
  Verified on Magento 2.4.8-p3 / Hyvä 1.5.2 / Hyva_SmileElasticsuite 1.2.8: category page, search results page,
  and the module's AJAX filter response (`?color=Purple` with `X-Requested-With`) all render swatches with no
  exception.

### Changed

- **Phase 2 — Architecture & Code Quality.** Internal refactor with no public API or behavioral change.
- `Block/AdvancedCatalog`: removed the duplicated filter-option helpers (`isOptionVisible()`,
  `isShowEmptyResults()`, `isOptionDisabled()`, `getUnusedOption()`, `getOptionViewData()`) that were
  defined verbatim in both `AdvancedCatalog` and `Model/AjaxResponse`. All filter-option logic now lives
  solely in `AjaxResponse`, which `AdvancedCatalog` already delegates to via `getFilterItems()`.
- `Block/AdvancedCatalog::getProductList()`: removed the dead `$productList;` statement (declared,
  never assigned, immediately overwritten).
- `Block/ListProduct`: added `declare(strict_types=1)` for consistency with the rest of the module, and
  added explicit `(int)`/`(bool)` casts on the `getPageSize()` and `isInfinityActive()` return values
  (previously relying on implicit coercion from `ScopeConfigInterface::getValue()`).
- `Model/AjaxResponse`: added parameter type hints to the private filter-processing helpers
  (`processFilter()`, `processAttributeFilter()`, `processSwatchAttributeFilter()`,
  `processNonSwatchAttributeFilter()`, `processNonAttributeFilter()`, `getResultOption()`) to strengthen
  static analysis and clear the outstanding intelephense warnings.

## [1.2.11] - 2026-07-14

### Changed

- Dependency updated: `smile/elasticsuite` constraint extended to `~2.10.9|~2.11.0|~2.12.0`.
  Verified against ElasticSuite 2.12 on a Hyvä project: no code-level incompatibilities,
  `setup:di:compile` passes, and AJAX filtering works on both category and search results pages.

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
