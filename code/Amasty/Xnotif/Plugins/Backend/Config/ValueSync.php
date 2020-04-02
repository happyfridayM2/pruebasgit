<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\Backend\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class ValueSync
 */
class ValueSync
{
    /**
     * @var WriterInterface
     */
    private $configWriter;

    public function __construct(
        WriterInterface $configWriter
    ) {
        $this->configWriter = $configWriter;
    }

    /**
     * @param Value $subject
     */
    public function beforeSave(Value $subject)
    {
        $syncFields = [
            'amxnotif/admin_notifications/qty_below'
            => 'cataloginventory/item_options/notify_stock_qty',
            'cataloginventory/item_options/notify_stock_qty'
            => 'amxnotif/admin_notifications/qty_below',
            'amxnotif/admin_notifications/stock_alert_email_secondary'
            => 'amxnotif/admin_notifications/stock_alert_email',
            'amxnotif/admin_notifications/stock_alert_email'
            => 'amxnotif/admin_notifications/stock_alert_email_secondary',
            'amxnotif/admin_notifications/sender_email_identity_secondary'
            => 'amxnotif/admin_notifications/sender_email_identity',
            'amxnotif/admin_notifications/sender_email_identity'
            => 'amxnotif/admin_notifications/sender_email_identity_secondary'
        ];

        if (isset($syncFields[$subject->getPath()]) && $subject->getOldValue() != $subject->getValue()) {
            $this->configWriter->save($syncFields[$subject->getPath()], $subject->getValue());
        }
    }
}
