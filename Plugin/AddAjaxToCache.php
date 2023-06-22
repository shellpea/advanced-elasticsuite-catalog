<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Serialize\Serializer\Json;

class AddAjaxToCache
{
    /**
     * @var RequestInterface $request
     */
    protected RequestInterface $request;
    /**
     * @var Context $context
     */
    protected Context $context;
    /**
     * @var Json $json
     */
    protected Json $json;

    /**
     * @param RequestInterface $request
     * @param Context          $context
     * @param Json             $json
     */
    public function __construct(
        RequestInterface $request,
        Context $context,
        Json $json
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->json = $json;
    }

    /**
     * @param Identifier $subject
     * @param string     $result
     *
     * @return string
     */
    public function afterGetValue(Identifier $subject, string $result): string
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        $data = [
            $isAjax,
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(Http::COOKIE_VARY_STRING)
            ?: $this->context->getVaryString()
        ];

        return sha1($this->json->serialize($data));
    }
}
