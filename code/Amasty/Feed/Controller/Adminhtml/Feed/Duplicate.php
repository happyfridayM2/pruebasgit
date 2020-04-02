<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

/**
 * Class Duplicate
 *
 * @package Amasty\Feed
 */
class Duplicate extends AbstractMassAction
{
    /**
     * {@inheritdoc}
     */
    public function massAction($collection)
    {
        foreach ($collection as $model) {
            $this->feedCopier->copy($model);
            $this->messageManager->addSuccessMessage(__('Feed %1 was duplicated', $model->getName()));
        }
    }
}
