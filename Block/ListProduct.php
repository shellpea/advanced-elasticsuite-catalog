<?php

namespace Shellpea\AdvancedElasticsuiteCatalog\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Shellpea\AdvancedElasticsuiteCatalog\Provider\Config;
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
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;


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
        CollectionFactory $productCollectionFactory,
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

    public function getCacheKeyInfo(): array
    {
        return parent::getCacheKeyInfo();
    }

    public function getProductsFromPrevPages(): \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection|string
    {
        $curPageNumber = $this->getRequest()->getParam('p');
        if (!$this->getRequest()->isAjax() && $curPageNumber > 1) {
            $category = $this->_catalogLayer->getCurrentCategory();

            $prevProductCollection = $this->productCollectionFactory->create();
            $prevProductCollection->addAttributeToSelect('*');
            $prevProductCollection->addCategoryFilter($category);

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
        return '';
    }

    public function getPageSize(): int
    {
        return $this->scopeConfig->getValue(
            'catalog/frontend/grid_per_page',
            ScopeInterface::SCOPE_STORES
        );
    }

    public function isInfinityActive(): bool
    {
        return $this->scopeConfig->getValue(
            Config::INFINITE_ACTIVE,
            ScopeInterface::SCOPE_STORES
        );
    }

    public function getOptionIdByLabel(string $attributeCode, string $optionLabel): bool|string|null
    {
        try {
            return $this->attributeRepository->get(Product::ENTITY, $attributeCode)->getSource()->getOptionId($optionLabel);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    public function getProductsFromPrevPagesSearch(): \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection|string
    {
        $curPageNumber = $this->getRequest()->getParam('p');
        if (!$this->getRequest()->isAjax() && $curPageNumber > 1) {
            $prevProductCollection = $this->getSearchCollection();
            $prevProductCollection->setCurPage($curPageNumber - 1);
            $prevProductCollection->setPageSize($this->getPageSize() * ($curPageNumber - 1));

            return $prevProductCollection;
        }
        return '';
    }

    public function getSearchCollection(): \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection
    {
        /** @var \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $searchCollection */
        $searchCollection = $this->productCollectionFactory->create();
        $searchCollection->addAttributeToSelect('*');
        $searchCollection->setSearchQuery($this->getRequest()->getParam('q'));

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
