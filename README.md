# Magento 2 Advanced Elastisuite Catalog Module
Magento 2 module enhancing ElasticSuite Catalog features for Hyva Theme. 
Adds smart ajax filtering, dynamic loading of products with infinite scroll or with dynamic pagination.

## Key Features
- Multiple filtering without page reloads based on Alpine.js
- Multiselect Ajax Swatches
- Dynamic update sliders of any type
- Expandable and configurable Infinite Scroll
- Or default pagination with alpine.js
- Loads products from  [previous pages](https://github.com/shellpea/advanced-elasticsuite-catalog/blob/30fb841b89939e9619971136d2b55c6c77e070e1/view/frontend/templates/product/list.phtml#L67)
- Also works on catalog search


## Installation

### Hyva Theme version <=1.1.23
```
composer require "shellpea/magento-advanced-elasticsuite-catalog":"^1.0.0"
```
### Hyva Theme version >=1.2.x || 1.3.x
```
composer require "shellpea/magento-advanced-elasticsuite-catalog":"^1.2.7"
```

## Admin Configurations
### General
- Active: Yes/No
### Infinite Scroll
- Active: Yes/No
- Button Label: String for infinite scroll button on Catalog Page
![Infinite Scroll button](https://user-images.githubusercontent.com/55882198/236670329-ba761d13-45a4-41a5-8723-f130d88926dd.png)
### Slider Configuration
Direct Mode Active: Yes/No
![Admin Configurations](https://user-images.githubusercontent.com/55882198/236669994-8ddb17f9-8f37-445d-9bf0-6e039c6ddd67.png)

>[!TIP]
> If yo have Varnish Cache issue - update sub vcl_hash in your varnish VCL by adding code below
```
 # To make sure XMLHttp calls have their own cache
    if (req.http.X-Requested-With) {
        hash_data(req.http.X-Requested-With);
    }
```
