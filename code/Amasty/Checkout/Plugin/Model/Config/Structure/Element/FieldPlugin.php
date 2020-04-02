<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Model\Config\Structure\Element;

use Amasty\Checkout\Plugin\DefaultConfigProvider;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FieldPlugin
 */
class FieldPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param Field $subject
     */
    public function beforeShowInDefault(Field $subject)
    {
        if (array_key_exists($subject->getId(), DefaultConfigProvider::BLOCK_NAMES)
            && $this->storeManager->isSingleStoreMode()
        ) {
            $data = $subject->getData();
            $data['showInDefault'] = true;

            $subject->setData($data, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        }
    }
}
