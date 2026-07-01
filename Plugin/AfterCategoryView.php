<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Magento\Catalog\Controller\Category\View;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\View\Result\Page;
use Shellpea\AdvancedElasticsuiteCatalog\Model\AjaxResponse;

class AfterCategoryView
{
    /**
     * @var AjaxResponse
     */
    protected $ajaxResponse;

    /**
     * @var HttpRequest
     */
    protected HttpRequest $request;

    /**
     * @param AjaxResponse $ajaxResponse
     * @param HttpRequest  $request
     */
    public function __construct(
        AjaxResponse $ajaxResponse,
        HttpRequest $request
    ) {
        $this->ajaxResponse = $ajaxResponse;
        $this->request = $request;
    }

    /**
     * @param View         $view
     * @param Page|Forward $page
     *
     * @return mixed
     */
    public function afterExecute(View $view, Page|Forward $page)
    {
        if (!$this->request->isXmlHttpRequest()) {
            return $page;
        }

        return $this->ajaxResponse
            ->setProductListBlock('category.products.list')
            ->setLeftNavBlock('catalog.leftnav')
            ->execute();
    }
}
