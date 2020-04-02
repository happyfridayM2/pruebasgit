<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Builder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Builder\Ui\Component\Form\Element;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class LinkBuilderId extends \Magento\Ui\Component\Form\Field
{
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param StoreManager $storeManager
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Catalog\Ui\Component\Product\Form\Categories\Options $categories,
        \Magezon\Core\Model\Source\Pages $pages,
        array $components = [],
        array $data = []
    ) {
        $data['config']['categories'] = $categories->toOptionArray();
        $data['config']['pages']      = $pages->toOptionArray();
        $data['config']['productUrl'] = $urlBuilder->getUrl('mgzbuilder/ajax/productList');
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
}
