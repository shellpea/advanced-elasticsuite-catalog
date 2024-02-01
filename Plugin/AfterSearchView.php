<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Magento\CatalogSearch\Controller\Result\Index;
use Shellpea\AdvancedElasticsuiteCatalog\Model\AjaxResponse;

class AfterSearchView
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
     * @param Index $view
     *
     * @return ?\Magento\Framework\Controller\Result\Json
     */
    public function afterExecute(Index $view): ?\Magento\Framework\Controller\Result\Json
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        if (!$isAjax) {
            return null;
        }

        return $this->ajaxResponse
            ->setProductListBlock('search_result_list')
            ->setLeftNavBlock('catalogsearch.leftnav')
            ->execute();
    }
}
