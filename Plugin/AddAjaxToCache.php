<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Serialize\Serializer\Json;

class AddAjaxToCache
{
    /**
     * @var HttpRequest $request
     */
    protected HttpRequest $request;
    /**
     * @var Context $context
     */
    protected Context $context;
    /**
     * @var Json $json
     */
    protected Json $json;

    /**
     * @param HttpRequest $request
     * @param Context     $context
     * @param Json        $json
     */
    public function __construct(
        HttpRequest $request,
        Context $context,
        Json $json
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->json = $json;
    }

    /**
     * @param Identifier $_subject
     * @param string     $_result
     *
     * @return string
     */
    public function afterGetValue(Identifier $_subject, string $_result): string
    {
        $data = [
            $this->request->isXmlHttpRequest(),
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(Http::COOKIE_VARY_STRING)
            ?: $this->context->getVaryString()
        ];

        return sha1($this->json->serialize($data));
    }
}
