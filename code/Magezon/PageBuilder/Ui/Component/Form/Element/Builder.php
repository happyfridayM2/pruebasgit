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
 * @package   Magezon_PageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\PageBuilder\Ui\Component\Form\Element;

use Magento\Framework\Data\Form\Element\Editor;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;
use Magento\Framework\App\ObjectManager;

class Builder extends \Magezon\Builder\Ui\Component\Form\Element\Builder
{
    const NAME = 'textarea';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magezon\PageBuilder\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param ContextInterface                      $context       
     * @param FormFactory                           $formFactory   
     * @param ConfigInterface                       $wysiwygConfig 
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory 
     * @param \Magento\Framework\Registry           $registry      
     * @param \Magezon\PageBuilder\Helper\Data      $dataHelper    
     * @param array                                 $data          
     * @param array                                 $config        
     */
    public function __construct(
        ContextInterface $context,
        FormFactory $formFactory,
        ConfigInterface $wysiwygConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        \Magezon\PageBuilder\Helper\Data $dataHelper,
        array $data = [],
        array $config = []
    ) {
        $this->registry   = $registry;
        $this->dataHelper = $dataHelper;
        $components = [];
        $config['ajax_data']['load_builder_url'] = 'mgzpagebuilder/builder/load';
        if (!isset($config['disableMagezonBuilder']) || !$config['disableMagezonBuilder']) {
            $config['disableMagezonBuilder'] = $this->isDisableArea();
        }
        parent::__construct($context, $formFactory, $wysiwygConfig, $layoutFactory, $registry, $components, $data, $config);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return boolean
     */
    public function isDisableArea()
    {
        $isDisableArea = false;
        $type = '';

        if ($this->registry->registry('cms_page')) $type = 'page';
        if ($this->registry->registry('cms_block')) $type = 'block';
        if ($this->registry->registry('current_product')) $type = 'product';
        if ($this->registry->registry('current_category')) $type = 'category';

        switch ($type) {
            case 'page':
                $isDisableArea = !$this->dataHelper->getConfig('general/enable_pages');
                break;

            case 'block':
                $isDisableArea = !$this->dataHelper->getConfig('general/enable_blocks');
                break;

            case 'product':
                $isDisableArea = !$this->dataHelper->getConfig('general/enable_products');
                break;

            case 'category':
                $isDisableArea = !$this->dataHelper->getConfig('general/enable_categories');
                break;
        }

        return $isDisableArea;

    }
}
