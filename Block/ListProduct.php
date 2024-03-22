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

    /**
     * Get products from previous pages based on current page number.
     *
     * @return \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection|string
     */
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

    /**
     * Retrieves the option ID for a given attribute code and option label.
     *
     * @param string $attributeCode The code of the attribute.
     * @param string $optionLabel The label of the option.
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the option label does not exist.
     * @return bool|string|null The option ID if found, false if not found, or null if an exception is caught.
     */
    public function getOptionIdByLabel(string $attributeCode, string $optionLabel): bool|string|null
    {
        try {
            return $this->attributeRepository->get(Product::ENTITY, $attributeCode)->getSource()->getOptionId($optionLabel);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Retrieves the products from the previous pages of a search.
     *
     * This function checks the current page number and if it is greater than 1, it retrieves the search collection
     * and sets the current page to the previous page number. It also sets the page size based on the current page
     * number and the page size.
     *
     * @return \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection|string
     * The search collection for the previous page or an empty string if the current page is 1.
     */
    public function getProductsFromPrevPagesSearch(): \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection|string
    {
        $currentPage = (int) $this->getRequest()->getParam('p', 1);
        if ($currentPage > 1) {
            $collection = $this->getSearchCollection();
            $collection->setCurPage($currentPage - 1)
                ->setPageSize($this->getPageSize() * ($currentPage - 1));

            return $collection;
        }
        return '';
    }

    /**
     * Get the search collection.
     *
     * @return \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection
     */
    public function getSearchCollection(): \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->setSearchQuery($this->getRequest()->getParam('q'))
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam('p'));

        $sortBy = $this->getRequest()->getParam('product_list_order');
        if ($sortBy === 'relevance') {
            $collection->setOrder('entity_id', 'DESC');
        } elseif ($sortBy === 'price') {
            $collection->setOrder('price_index.price', 'ASC');
        } else {
            $collection->setOrder($sortBy, 'ASC');
        }

        foreach ($this->getRequest()->getParams() as $attributeCode => $optionLabels) {
            if (!is_array($optionLabels)) {
                $optionLabels = [$optionLabels];
            }

            foreach ($optionLabels as $optionLabel) {
                if ($optionId = $this->getOptionIdByLabel($attributeCode, $optionLabel)) {
                    $collection->addFieldToFilter($attributeCode, $optionId);
                }
            }
        }

        return $collection;
    }
}
