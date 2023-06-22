<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Magento\Catalog\Controller\Category\View;
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
