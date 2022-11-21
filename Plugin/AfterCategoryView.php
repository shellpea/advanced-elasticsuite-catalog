<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Controller\Category\View;
use Magento\CatalogSearch\Controller\Result\Index;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Smile\ElasticsuiteCatalog\Block\Navigation;
use Smile\ElasticsuiteCatalog\Model\Layer\Filter\Attribute;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Shellpea\AdvancedElasticsuiteCatalog\Model\AjaxResponse;

class AfterCategoryView
{
    /**
     * @var AjaxResponse
     */
    protected $ajaxResponse;

    /**
     * @param AjaxResponse $ajaxResponse
     */
    public function __construct(
        AjaxResponse $ajaxResponse
    ) {
        $this->ajaxResponse = $ajaxResponse;
    }

    /**
     * @param View $view
     * @param      $page
     *
     * @return mixed
     */
    public function afterExecute(View $view, $page)
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        if (!$isAjax) {
            return $page;
        }

        return $this->ajaxResponse
            ->setProductListBlock('category.products.list')
            ->setLeftNavBlock('catalog.leftnav')
            ->execute();
    }
}
