<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tabs as MagentoAttributeEditTabs;

/**
 * Class Tabs
 * @package Amasty\Shopby\Plugin\Catalog\Block\Adminhtml\Product\Attribute\Edit
 */
class Tabs
{
    /**
     * @param MagentoAttributeEditTabs $subject
     * @return array
     */
    public function beforeToHtml(MagentoAttributeEditTabs $subject)
    {
        $content = $subject->getRequest()->getParam('attribute_id') ? $subject->getChildHtml('amshopby') : null;
        /*disable for new products because wrong loading dispay mode */
        $subject->addTabAfter(
            'amasty_shopby',
            [
                'label' => __('Improved Layered Navigation'),
                'title' => __('Improved Layered Navigation'),
                'content' => $content,
            ],
            'front'
        );

        return [];
    }
}
