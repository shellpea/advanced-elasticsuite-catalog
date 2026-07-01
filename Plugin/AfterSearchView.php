<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Magento\CatalogSearch\Controller\Result\Index;
use Magento\Framework\App\Request\Http as HttpRequest;
use Shellpea\AdvancedElasticsuiteCatalog\Model\AjaxResponse;

class AfterSearchView
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
     * @param Index $view
     * @param mixed $result
     *
     * @return mixed
     */
    public function afterExecute(Index $view, $result)
    {
        if (!$this->request->isXmlHttpRequest()) {
            return $result;
        }

        return $this->ajaxResponse
            ->setProductListBlock('search_result_list')
            ->setLeftNavBlock('catalogsearch.leftnav')
            ->execute();
    }
}
