<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Framework\Search;

use Magento\Framework\Search\Request;

class RequestPlugin
{
    const DEFAULT_FIELD_NAME = 'entity_id';

    /**
     * @param Request $request
     * @param array $result
     * @return array
     */
    public function afterGetSort(Request $request, $result)
    {
        foreach ($result as $key => $sort) {
            if (array_key_exists('field', $sort) && $sort['field'] === null) {
                $result[$key]['field'] = self::DEFAULT_FIELD_NAME;
            }
        }

        return $result;
    }
}
