<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedOptimizer\Controller\Adminhtml\AbstractClearFolders;
use Amasty\PageSpeedOptimizer\Model\OptionSource\Resolutions;

class ClearTabletFolder extends AbstractClearFolders
{
    public function execute()
    {
        $this->clearFolder(Resolutions::RESOLUTIONS[Resolutions::TABLET]['dir']);
        $this->messageManager->addSuccessMessage(__('Tablet Images Folder was successful cleaned.'));

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
