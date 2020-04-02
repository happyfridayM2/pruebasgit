<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab;

use Amasty\ShopbyPage\Controller\RegistryConstants;
use Amasty\ShopbyPage\Model\Config\Source\Position as SourcePosition;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Category\Attribute\Source\Page as CategoryAttributeSourcePage;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

class Text extends Generic implements TabInterface
{
    /**
     * @var CategoryAttributeSourcePage
     */
    private $categoryAttributeSourcePage;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var  SourcePosition
     */
    private $sourcePosition;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $wysiwygConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CategoryAttributeSourcePage $categoryAttributeSourcePage,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        SourcePosition $sourcePosition,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->categoryAttributeSourcePage = $categoryAttributeSourcePage;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->sourcePosition = $sourcePosition;
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Page Text');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('amasty_shopbypage_');

        /** @var \Amasty\ShopbyPage\Api\Data\PageInterface $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::PAGE);

        $fieldset = $form->addFieldset(
            'page_fieldset',
            ['legend' => __('Page Text'), 'class' => 'fieldset-wide']
        );

        if ($model->getPageId()) {
            $fieldset->addField('page_id', 'hidden', ['name' => 'page_id']);
        }

        $fieldset->addField(
            'position',
            'select',
            [
                'name'    => 'position',
                'label'   => __('Add Title & Description'),
                'title'   => __('Add Title & Description'),
                'options' => $this->sourcePosition->toArray()
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name'  => 'title',
                'label' => __('Title'),
                'title' => __('Title')
            ]
        );

        $fieldset->addField(
            'description',
            'editor',
            [
                'name'  => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'wysiwyg' => true,
                'config' => $this->wysiwygConfig->getConfig(['add_variables' => false])
            ]
        );

        $categoryImage = '';
        if ($model->getImageUrl()) {
            $categoryImage = '
            <div>
            <br>
            <input type="checkbox" id="image_delete" name="image_delete" value="1" />
            <label for="image_delete">' . __('Delete Image') . '</label>
            <br>
            <br><img src="' . $model->getImageUrl() . '" /></div>';
        }

        $fieldset->addField(
            'image',
            'file',
            ['name' => 'image', 'label' => __('Image'), 'title' => __('Image'), 'after_element_html' => $categoryImage]
        );

        $fieldset->addField(
            'top_block_id',
            'select',
            [
                'name'   => 'top_block_id',
                'label'  => __('Top CMS Block'),
                'values' => $this->categoryAttributeSourcePage->getAllOptions()
            ]
        );

        $fieldset->addField(
            'bottom_block_id',
            'select',
            [
                'name'   => 'bottom_block_id',
                'label'  => __('Bottom CMS Block'),
                'values' => $this->categoryAttributeSourcePage->getAllOptions()
            ]
        );

        $form->setValues(
            $this->extensibleDataObjectConverter->toFlatArray(
                $model,
                [],
                \Amasty\ShopbyPage\Api\Data\PageInterface::class
            )
        );

        $this->setForm($form);
        parent::_prepareForm();

        return $this;
    }
}
