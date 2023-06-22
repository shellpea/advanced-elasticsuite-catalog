<?php

declare(strict_types=1);

namespace Shellpea\AdvancedElasticsuiteCatalog\Plugin;

use Shellpea\AdvancedElasticsuiteCatalog\Provider\Config;
use Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Slider;
use Magento\Framework\Serialize\Serializer\Json;

class AddSliderToolbarOptions
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

    public function afterGetJsonConfig(Slider $subject, $result): string
    {
        /** @var string[] $jsonData */
        $jsonData = $this->json->unserialize($result);
        if ($jsonData === null) {
            return $result;
        }
        /** @var \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter */
        $filter = $subject->getFilter();

        $jsonData['requestVar'] = $filter->getRequestVar();
        $jsonData['ajax'] = $this->config->isValue(Config::GENERAL_ACTIVE);

        return $this->json->serialize($jsonData);
    }
}
