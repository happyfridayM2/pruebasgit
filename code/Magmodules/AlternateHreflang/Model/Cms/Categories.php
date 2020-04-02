<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Model\Cms;

use Magento\Framework\Option\ArrayInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

/**
 * Class Categories
 *
 * @package Magmodules\AlternateHreflang\Model\Cms
 */
class Categories implements ArrayInterface
{

    /**
     * @var CollectionFactory
     */
    private $pageCollectionFactory;
    /**
     * @var array
     */
    private $groups = [];

    /**
     * Categories constructor.
     *
     * @param CollectionFactory $pageCollectionFactory
     */
    public function __construct(
        CollectionFactory $pageCollectionFactory
    ) {
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->groups) {
            $pages = $this->pageCollectionFactory->create()
                ->addFieldToFilter('alternate_category', ['notnull' => true]);
            $pages->getSelect()->group('alternate_category');

            foreach ($pages as $page) {
                if (!empty($page['alternate_category'])) {
                    $this->groups[] = [
                        'value' => $page['alternate_category'],
                        'label' => ucfirst($page['alternate_category'])
                    ];
                }
            }

            $this->groups[] = [
                'value' => __('alternate_category_new'),
                'label' => __('-- Add new')
            ];
        }

        return $this->groups;
    }
}
