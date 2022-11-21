<?php

namespace Shellpea\AdvancedElasticsuiteCatalog\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Catalog\Model\CategoryFactory;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        ScopeConfigInterface $scopeConfig,
        AttributeRepositoryInterface $attributeRepository,
        Registry $registry,
        CategoryFactory $categoryFactory,
        \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->attributeRepository = $attributeRepository;
        $this->registry = $registry;
        $this->categoryFactory = $categoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        return $keyInfo;
    }

    public function getProductsFromPrevPages()
    {
        $curPageNumber = $this->getRequest()->getParam('p');
        if (!$this->getRequest()->isAjax() && $curPageNumber > 1) {
            $categoryId = $this->registry->registry('current_category')->getId();
            $category_product_collection = $this->categoryFactory->create()->load($categoryId);
            /** @var \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $prevProductCollection */
            $prevProductCollection = $this->productCollectionFactory->create();
            $prevProductCollection->addAttributeToSelect('*');
            $prevProductCollection->addCategoryFilter($category_product_collection);

            foreach ($this->getRequest()->getParams() as $attributeCode => $optionLabel) {
                if (is_array($optionLabel)) {
                    foreach ($optionLabel as $optionLabel) {
                        if ($optionId = $this->getOptionIdByLabel($attributeCode, $optionLabel)) {
                            $prevProductCollection->addFieldToFilter($attributeCode, $optionId);
                        }
                    }
                } else {
                    if ($optionId = $this->getOptionIdByLabel($attributeCode, $optionLabel)) {
                        $prevProductCollection->addFieldToFilter($attributeCode, $optionId);
                    }
                }
            }

            $prevProductCollection->addOrder('entity_id', 'desc');
            $prevProductCollection->setPageSize($this->getPageSize() * ($curPageNumber - 1));

            return $prevProductCollection;
        }
    }

    public function getPageSize()
    {
        return $this->scopeConfig->getValue(
            'catalog/frontend/grid_per_page',
            ScopeInterface::SCOPE_STORES
        );
    }

    public function getPageSeparationLabel($curPage)
    {
        $label = $this->scopeConfig->getValue(
            'smile_elasticsuite_ajax_settings/infinite/page_separation_label',
            ScopeInterface::SCOPE_STORES
        );

        return str_replace('%number', (int) $curPage, (string) $label);
    }

    public function isInfinityActive()
    {
        return $this->scopeConfig->getValue(
            'smile_elasticsuite_ajax_settings/infinite/active',
            ScopeInterface::SCOPE_STORES
        );
    }

    public function getOptionIdByLabel($attributeCode, $optionLabel)
    {
        try {
            return $this->attributeRepository->get(Product::ENTITY, $attributeCode)->getSource()->getOptionId($optionLabel);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    public function getProductsFromPrevPagesSearch()
    {
        $curPageNumber = $this->getRequest()->getParam('p');
        if (!$this->getRequest()->isAjax() && $curPageNumber > 1) {
            $prevProductCollection = $this->getSearchCollection();
            $prevProductCollection->setCurPage(null);
            $prevProductCollection->setPageSize($this->getPageSize() * ($curPageNumber - 1));

            return $prevProductCollection;
        }
    }

    public function getSearchCollection()
    {
        /** @var \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $searchCollection */
        $searchCollection = $this->productCollectionFactory->create();
        $searchCollection->addAttributeToSelect('*');
        $searchCollection->addSearchFilter($this->getRequest()->getParam('q'));

        foreach ($this->getRequest()->getParams() as $attributeCode => $optionLabel) {
            if (is_array($optionLabel)) {
                foreach ($optionLabel as $optionLabel) {
                    if ($optionId = $this->getOptionIdByLabel($attributeCode, $optionLabel)) {
                        $searchCollection->addFieldToFilter($attributeCode, $optionId);
                    }
                }
            } else {
                if ($optionId = $this->getOptionIdByLabel($attributeCode, $optionLabel)) {
                    $searchCollection->addFieldToFilter($attributeCode, $optionId);
                }
            }
        }

        $searchCollection->setPageSize($this->getPageSize());
        $searchCollection->setCurPage($this->getRequest()->getParam('p'));
        $searchCollection->addOrder('entity_id', 'desc');

        return $searchCollection;
    }
}
