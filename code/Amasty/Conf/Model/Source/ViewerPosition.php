<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class ViewerPosition implements \Magento\Framework\Option\ArrayInterface
{
    const MAX_POSITION_VALUE = 11;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        for ($i = 1; $i <= self::MAX_POSITION_VALUE; $i++) {
            $options[] = [
                'value' => $i,
                'label' => $i
            ];
        }

        return $options;
    }
}
