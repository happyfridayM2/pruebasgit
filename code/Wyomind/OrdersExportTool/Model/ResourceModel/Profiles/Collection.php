<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Model\ResourceModel\Profiles;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wyomind\OrdersExportTool\Model\Profiles', 'Wyomind\OrdersExportTool\Model\ResourceModel\Profiles');
    }
    
    public function searchProfiles($ids)
    {
        $this->getSelect()->where("id IN (?)", $ids);

        return $this;
    }
    
    public function getList($profilesIds)
    {
        if (!empty($profilesIds)) {
            $this->getSelect()->where("id IN (" . implode(',', $profilesIds) . ")");
        }

        return $this;
    }
}