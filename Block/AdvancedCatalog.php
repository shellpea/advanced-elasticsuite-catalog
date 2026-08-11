<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Block;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Smile\ElasticsuiteCatalog\Block\Navigation;
use Shellpea\AdvancedElasticsuiteCatalog\Provider\Config;
use Smile\ElasticsuiteSwatches\Helper\Swatches;
use Magento\Swatches\Helper\Media;
use Shellpea\AdvancedElasticsuiteCatalog\Model\AjaxResponse;

/**
 * Class AdvancedCatalog
 *
 * @package Shellpea\AdvancedElasticsuiteCatalog\Block
 */
class AdvancedCatalog extends Template
{
    /**
     * Product collection
     *
     * @var Collection $productCollection
     */
    protected $productCollection;
    /**
     * Navigation block
     *
     * @var Navigation $navBlock
     */
    protected $navBlock;

    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * Json
     *
     * @var Json $json
     */
    protected $json;
    /**
     * Layout
     *
     * @var Layout $layout
     */
    protected $layout;

    /**
     * @var Swatches
     */
    protected $swatchHelper;

    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * @var AjaxResponse $ajaxResponse
     */
    protected $ajaxResponse;

    /**
     * Ajax constructor.
     *
     * @param Json             $json
     * @param Config           $config
     * @param Template\Context $context
     * @param Layout           $layout
     * @param array            $data
     */
    public function __construct(
        Json $json,
        Config $config,
        Template\Context $context,
        Layout $layout,
        Swatches $swatchHelper,
        Media $mediaHelper,
        AjaxResponse $ajaxResponse,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
        $this->json = $json;
        $this->layout = $layout;
        $this->swatchHelper = $swatchHelper;
        $this->mediaHelper = $mediaHelper;
        $this->ajaxResponse = $ajaxResponse;
    }

    /**
     * Get product list collection
     *
     * @return Collection
     */
    public function getProductList(): Collection
    {
        if ($this->productCollection === null) {
            /** @var ListProduct $productList */
            if ($this->isSearch()) {
                $productList = $this->layout->getBlock('search_result_list');
            } else {
                $productList = $this->layout->getBlock('category.products.list');
            }

            $this->productCollection = $productList->getLoadedProductCollection();
        }

        return $this->productCollection;
    }


    /**
     * Get filter items
     *
     * @return array
     */
    protected function getFilterItems(): array
    {
        return $this->ajaxResponse->getFilterItems($this->getNavBlock());
    }

    /**
     * Get active filters
     *
     * @return string
     */
    protected function getActiveFilters(): string
    {
        if ($this->isSearch()) {
            $activeFilters = $this->layout->getBlock('catalogsearch.navigation.state')->toHtml();

            return $activeFilters;
        }

        $activeFilters = $this->layout->getBlock('catalog.navigation.state')->toHtml();

        return $activeFilters;
    }

    /**
     * Get Nav Block
     *
     * @return string
     */
    protected function getNavBlock(): string
    {
        if ($this->isSearch()) {
            $navBlock = 'catalogsearch.leftnav';

            return $navBlock;
        }

        $navBlock = 'catalog.leftnav';

        return $navBlock;
    }

    /**
     * Is the current request a search.
     *
     * @return boolean
     */
    private function isSearch(): bool
    {
        return (bool) ($this->getRequest()->getParam('q') !== null);
    }

    /**
     * Get js config
     *
     * @return string
     */
    public function getJsonConfig(): string
    {
        /** @var mixed[] $jsonConfig */
        $jsonConfig = [];
        $jsonConfig['items'] = [
            'filterItems' => $this->getFilterItems(),
            'activeFilter' => $this->getActiveFilters(),
            'pageSize' => $this->getPageSize(),
            'size' => $this->getSize(),
            'curPage' => $this->getCurPage(),
        ];
        $jsonConfig['slider'] = [
            'directMode' => $this->config->isValue(Config::SLIDER_DIRECT_MODE_ACTIVE),
        ];
        $jsonConfig['infinite'] = [
            'active' => $this->config->isValue(Config::INFINITE_ACTIVE),
            'buttonLabel' => $this->config->getValue(Config::INFINITE_BUTTON_LABEL),
        ];

        return $this->json->serialize($jsonConfig);
    }
    /**
     * Get size
     *
     * @return int
     */
    protected function getSize(): int
    {
        return (int) $this->getProductList()->getSize();
    }

    /**
     * Get page size
     *
     * @return int
     */
    protected function getPageSize(): int
    {
        return (int) $this->getProductList()->getPageSize();
    }

    /**
     * Get current page
     *
     * @return int
     */
    protected function getCurPage(): int
    {
        return (int) $this->getProductList()->getCurPage();
    }
}
