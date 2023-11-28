<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Shellpea\AdvancedElasticsuiteCatalog\Block\Product;

use Magento\Review\Block\Product\ReviewRenderer as DefaultReviewRenderer;

/**
 * Review renderer
 */
class ReviewRenderer extends DefaultReviewRenderer
{
    /**
     * Array of available template name
     *
     * @var array
     */
    protected $_availableTemplates = [
        self::FULL_VIEW => 'Shellpea_AdvancedElasticsuiteCatalog::helper/summary.phtml',
        self::SHORT_VIEW => 'Shellpea_AdvancedElasticsuiteCatalog::helper/summary_short.phtml',
    ];
}
