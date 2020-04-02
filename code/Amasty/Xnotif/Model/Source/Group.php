<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\Source;

/**
 * Class Group
 */
class Group implements \Magento\Framework\Option\ArrayInterface
{
    const NOT_LOGGED_IN_VALUE = 0;
    const ALL_GROUPS = -1;

    /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Group
     */
    private $groupSource;

    public function __construct(\Magento\Customer\Model\Customer\Attribute\Source\Group $groupSource)
    {
        $this->groupSource = $groupSource;
    }

    public function toOptionArray()
    {
        $groups = [
            [
                'value' => self::ALL_GROUPS,
                'label' => __('All Groups')
            ],
            [
                'value' => self::NOT_LOGGED_IN_VALUE,
                'label' => __('Not Logged In')
            ]
        ];

        return array_merge(
            $groups,
            $this->groupSource->getAllOptions()
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $optionArray = $this->toOptionArray();
        $labels =  array_column($optionArray, 'label');
        $values =  array_column($optionArray, 'value');

        return array_combine($values, $labels);
    }
}
