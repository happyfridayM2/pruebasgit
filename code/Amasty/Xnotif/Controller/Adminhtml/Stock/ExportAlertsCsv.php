<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Stock;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ExportAlertsCsv
 */
class ExportAlertsCsv extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    const ADMIN_RESOURCE = 'Amasty_Xnotif::stock';

    /**
     * Export alerts as CSV file
     *
     * @return \Magento\Framework\App\ResponseInterface|null
     */
    public function execute()
    {
        $fileName = 'alerts.csv';
        $content = $this->_view->getLayout()->createBlock(
            \Amasty\Xnotif\Block\Adminhtml\Report\Grid::class
        )->getCSVFile($fileName);

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
