<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Stock;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ExportAlertsXml
 */
class ExportAlertsXml extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    const ADMIN_RESOURCE = 'Amasty_Xnotif::stock';

    /**
     * Export alerts as excel xml file
     *
     * @return \Magento\Framework\App\ResponseInterface|null
     */
    public function execute()
    {
        $fileName = 'alerts.xml';
        $content = $this->_view->getLayout()->createBlock(
            \Amasty\Xnotif\Block\Adminhtml\Stock\Grid::class
        )->getExcelFile($fileName);

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
