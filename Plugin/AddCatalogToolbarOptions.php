<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Framework\Serialize\Serializer\Json;
use Shellpea\AdvancedElasticsuiteCatalog\Provider\Config;

class AddCatalogToolbarOptions
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Json
     */
    protected $json;

    public function __construct(
        Config $config,
        Json $json
    ) {
        $this->config = $config;
        $this->json = $json;
    }

    /**
     * Add catalog toolbar options
     *
     * @param Toolbar $subject
     * @param         $result
     * @param mixed[] $customOptions
     *
     * @return string
     */
    public function afterGetWidgetOptionsJson(
        Toolbar $subject,
        $result,
        array $customOptions = []
    ): string {
        /** @var string[] $jsonData */
        $jsonData = $this->json->unserialize($result);
        if (!isset($jsonData['productListToolbarForm'])) {
            return $result;
        }
        $jsonData['productListToolbarForm']['ajax'] = $this->config->isValue(Config::GENERAL_ACTIVE);

        return $this->json->serialize($jsonData);
    }
}
