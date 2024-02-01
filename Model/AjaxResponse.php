<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Model;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Layout;
use Smile\ElasticsuiteCatalog\Block\Navigation;
use Smile\ElasticsuiteCatalog\Model\Layer\Filter\Attribute;
use Smile\ElasticsuiteSwatches\Helper\Swatches;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Swatches\Helper\Media;

class AjaxResponse
{
    /**
     * Product list block
     *
     * @var string $productListBlock
     */
    protected ?string $productListBlock = null;
    /**
     * Left nav block
     *
     * @var string $leftNavBlock
     */
    protected ?string $leftNavBlock = null;
    /**
     * Result json factory
     *
     * @var JsonFactory $resultJsonFactory
     */
    protected $resultJsonFactory;
    /**
     * Context
     *
     * @var Context $context
     */
    protected $context;
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
     * AjaxResponse constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param Layout      $layout
     * @param Context     $context
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Layout $layout,
        Swatches $swatchHelper,
        Media $mediaHelper,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->context = $context;
        $this->layout = $layout;
        $this->swatchHelper = $swatchHelper;
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * Execute
     *
     * @return Json
     */
    public function execute(): Json
    {
        $this->context->setValue('ajax', true, false);

        /** @var ListProduct $productList */
        $productList = $this->layout->getBlock($this->getProductListBlock());
        //Need to calculate page size
        $htmlContent = $productList->toHtml();
        $activeFilters = $this->layout->getBlock('catalog.navigation.state') ?
            $this->layout->getBlock('catalog.navigation.state')->toHtml() :
            $this->layout->getBlock('catalogsearch.navigation.state')->toHtml();
        $productCollection = $productList->getLoadedProductCollection();
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'productList' => $htmlContent,
            'listFilterOptions' => $this->layout->getBlock($this->getLeftNavBlock())->toHtml(),
            'filterItems' => $this->getFilterItems($this->getLeftNavBlock()),
            'activeFilter' => $activeFilters,
            'pageSize' => $productCollection->getPageSize(),
            'size' => $productCollection->getSize(),
            'curPage' => $productCollection->getCurPage(),
        ]);

        return $resultJson;
    }

    /**
     * Check if option should be visible
     *
     * @param FilterItem $filterItem
     *
     * @return bool
     */
    protected function isOptionVisible(FilterItem $filterItem, $attribute): bool
    {
        return !($this->isOptionDisabled($filterItem) && $this->isShowEmptyResults($attribute));
    }

    /**
     * Check if attribute values should be visible with no results
     *
     * @return bool
     */
    protected function isShowEmptyResults($attribute): bool
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
    protected function isOptionDisabled(FilterItem $filterItem): bool
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
    protected function getUnusedOption(FilterItem $filterItem): array
    {
        return [
            'label' => $filterItem->getLabel(),
            'link' => 'javascript:void();',
            'custom_style' => 'disabled'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getOptionViewData(FilterItem $filterItem): array
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
     * Get filter items
     *
     * @return string[]
     */
    public function getFilterItems(string $navBlock): array
    {
        $leftNavBlock = $this->layout->getBlock($navBlock);
        $items = [];

        $filters = $leftNavBlock->getFilters();

        foreach ($filters as $filter) {
            $items += $this->processFilter($filter);
        }

        return $items;
    }

    private function processFilter($filter): array
    {
        $items = [];
        $datascope = $filter->getRequestVar() . 'Filter';

        if ($filter instanceof Attribute) {
            $items[$datascope] = $this->processAttributeFilter($filter);
        } else {
            $items[$datascope] = $this->processNonAttributeFilter($filter);
        }

        return $items;
    }

    private function processAttributeFilter($filter): array
    {
        $items = [];
        $attribute = $filter->getAttributeModel();

        if ($this->swatchHelper->isSwatchAttribute($attribute)) {
            $items = $this->processSwatchAttributeFilter($filter, $attribute);
        } else {
            $items = $this->processNonSwatchAttributeFilter($filter);
        }

        return $items;
    }
    private function processSwatchAttributeFilter($filter, $attribute): array
    {
        $items = [];

        foreach ($filter->getItems() as $item) {
            $resultOption = $this->getResultOption($item, $attribute);
            $attributeOptionId = $this->swatchHelper->getOptionIds($attribute, $item['label']);
            $swatchData = $this->swatchHelper->getSwatchesByOptionsId($attributeOptionId);
            $swatchThumbPath = $this->mediaHelper->getSwatchAttributeImage('swatch_thumb', $swatchData[$attributeOptionId[0]]['value']);
            $swatchImagePath = $this->mediaHelper->getSwatchAttributeImage('swatch_image', $swatchData[$attributeOptionId[0]]['value']);
            $items[] = [
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
        }

        return $items;
    }

    private function processNonSwatchAttributeFilter($filter): array
    {
        $items = [];

        foreach ($filter->getItems() as $item) {
            $items[] = $item->toArray(['label', 'count', 'url', 'is_selected']);
        }

        return $items;
    }

    private function processNonAttributeFilter($filter): array
    {
        $items = [];

        foreach ($filter->getItems() as $item) {
            $items[] = [
                'label' => $item->getLabel(),
                'count' => $item->getCount(),
                'url' => $item->getUrl(),
            ];
        }

        return $items;
    }

    private function getResultOption($item, $attribute): array|bool
    {
        if ($this->isShowEmptyResults($attribute)) {
            return $this->getUnusedOption($item);
        } elseif ($item && $this->isOptionVisible($item, $attribute)) {
            return $this->getOptionViewData($item);
        }

        return false;
    }


    /**
     * Set product list block
     *
     * @param string $productListBlock
     *
     * @return AjaxResponse
     */
    public function setProductListBlock(string $productListBlock): AjaxResponse
    {
        $this->productListBlock = $productListBlock;

        return $this;
    }

    /**
     * Set product list block
     *
     * @param string $leftNavBlock
     *
     * @return AjaxResponse
     */
    public function setLeftNavBlock(string $leftNavBlock): AjaxResponse
    {
        $this->leftNavBlock = $leftNavBlock;

        return $this;
    }

    /**
     * Get product list block
     *
     * @return string
     */
    public function getProductListBlock(): string
    {
        return $this->productListBlock;
    }

    /**
     * Get left nav block
     *
     * @return string
     */
    public function getLeftNavBlock(): string
    {
        return $this->leftNavBlock;
    }
}
