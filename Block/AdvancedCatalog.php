<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Block;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Smile\ElasticsuiteCatalog\Block\Navigation;
use Smile\ElasticsuiteCatalog\Model\Layer\Filter\Attribute;
use Shellpea\AdvancedElasticsuiteCatalog\Provider\Config;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Smile\ElasticsuiteSwatches\Helper\Swatches;
use Magento\Swatches\Helper\Media;

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
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
        $this->json = $json;
        $this->layout = $layout;
        $this->swatchHelper = $swatchHelper;
        $this->mediaHelper = $mediaHelper;
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
            $productList;
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
     * {@inheritDoc}
     */
    protected function getOptionViewData(FilterItem $filterItem)
    {
        $customStyle = '';

        $linkToOption = $filterItem->getUrl();

        if ($this->isOptionDisabled($filterItem)) {
            $customStyle = 'disabled';
            $linkToOption = 'javascript:void();';
        }

        if ($filterItem->getIsSelected()) {
            $customStyle = ' border-container-lighter ring ring-primary ring-opacity-50';
        }

        return [
            'label' => $filterItem->getLabel(),
            'link' => $linkToOption,
            'custom_style' => $customStyle,
        ];
    }


    /**
     * Check if option should be visible
     *
     * @param FilterItem $filterItem
     *
     * @return bool
     */
    protected function isOptionVisible(FilterItem $filterItem, $attribute)
    {
        return !($this->isOptionDisabled($filterItem) && $this->isShowEmptyResults($attribute));
    }

    /**
     * Check if attribute values should be visible with no results
     *
     * @return bool
     */
    protected function isShowEmptyResults($attribute)
    {
        return $attribute->getIsFilterable() != '1';
    }

    /**
     * Check if option should be disabled
     *
     * @param FilterItem $filterItem
     *
     * @return bool
     */
    protected function isOptionDisabled(FilterItem $filterItem)
    {
        return !$filterItem->getCount();
    }

    /**
     * Get view data for option with no results
     *
     * @param FilterItem $filterItem
     *
     * @return array
     */
    protected function getUnusedOption(FilterItem $filterItem)
    {
        return [
            'label' => $filterItem->getLabel(),
            'link' => 'javascript:void();',
            'custom_style' => 'disabled'
        ];
    }

    /**
     * Get filter items
     *
     * @return array
     */
    protected function getFilterItems(): array
    {
        $navBlock = $this->getNavBlock();
        $items = [];
        $swatchData = [];
        /** @var mixed[] $filters */
        $filters = $navBlock->getFilters();

        foreach ($filters as $filter) {
            $datascope = $filter->getRequestVar() . 'Filter';

            if (is_a($filter, Attribute::class)) {
                $items[$datascope] = [];
                $attribute = $filter->getAttributeModel();
                foreach ($filter->getItems() as $item) {
                    if ($this->swatchHelper->isSwatchAttribute($attribute)) {
                        $resultOption = false;
                        if ($this->isShowEmptyResults($attribute)) {
                            $resultOption = $this->getUnusedOption($item);
                        } elseif ($item && $this->isOptionVisible($item, $attribute)) {
                            $resultOption = $this->getOptionViewData($item);
                        }
                        $attributeOptionId = $this->swatchHelper->getOptionIds($attribute, $item['label']);
                        $swatchData = $this->swatchHelper->getSwatchesByOptionsId($attributeOptionId);
                        $swatchThumbPath = $this->mediaHelper->getSwatchAttributeImage('swatch_thumb', $swatchData[$attributeOptionId[0]]['value']);
                        $swatchImagePath = $this->mediaHelper->getSwatchAttributeImage('swatch_image', $swatchData[$attributeOptionId[0]]['value']);
                        $items[$datascope][] = [
                            'label' => $item->getLabel(),
                            'count' => $item->getCount(),
                            'url' => $item->getUrl(),
                            'is_selected' => $item->getIsSelected(),
                            'option_id' => $attributeOptionId[0],
                            'option' => $resultOption,
                            'swatch' => $swatchData,
                            'swatch_thumb' => $swatchThumbPath,
                            'swatch_image' => $swatchImagePath
                        ];
                    } else {
                        $items[$datascope][] = $item->toArray(['label', 'count', 'url', 'is_selected']);
                    }
                }
            } else {
                $items[$datascope] = [];
                foreach ($filter->getItems() as $item) {
                    $items[$datascope][] = [
                        'label' => $item->getLabel(),
                        'count' => $item->getCount(),
                        'url' => $item->getUrl(),
                    ];
                }
            }

        }

        return $items;
    }

    /**
     * Get active filters
     *
     * @return string[]
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
     * @return \Magento\Framework\View\Element\BlockInterface | bool
     */
    protected function getNavBlock(): \Magento\Framework\View\Element\BlockInterface
    {
        if ($this->isSearch()) {
            $navBlock = $this->getLayout()->getBlock('catalogsearch.leftnav');

            return $navBlock;
        }

        $navBlock = $this->getLayout()->getBlock('catalog.leftnav');

        return $navBlock;
    }

    /**
     * Is the current request a search.
     *
     * @return boolean
     */
    private function isSearch()
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
