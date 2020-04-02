<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Setup\Operation;

class MoveToOutOfOtock
{
    private $changedSettings = [
        '"amasty_conf/general/out_of_stock"',
    ];

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('core_config_data');

        $select = $setup->getConnection()->select()
            ->from($tableName, ['config_id','path'])
            ->where('path IN (' . implode(',', $this->changedSettings) . ')');

        $settings = $connection->fetchPairs($select);

        foreach ($settings as $key => $value) {
            $value = str_replace('general', 'out_of_stock', $value);
            $connection->update($tableName, ['path' => $value], ['config_id = ?' => $key]);
        }
    }
}
