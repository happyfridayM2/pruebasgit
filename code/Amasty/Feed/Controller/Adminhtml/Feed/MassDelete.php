<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

/**
 * Class MassDelete
 *
 * @package Amasty\Feed
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     */
    public function massAction($collection)
    {
        $count = $collection->getSize();
        $collection->walk('delete');
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $count));
    }
}
