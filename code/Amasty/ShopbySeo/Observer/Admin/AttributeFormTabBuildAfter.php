<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Observer\Admin;

use Amasty\Shopby\Helper\Category;
use Amasty\ShopbySeo\Model\Source\IndexMode;
use Amasty\ShopbySeo\Model\Source\RelNofollow;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Amasty\ShopbyBase\Helper\Data as BaseHelper;

class AttributeFormTabBuildAfter implements ObserverInterface
{
    /**
     * @var  Yesno
     */
    protected $yesNoSource;

    /**
     * @var  IndexMode
     */
    protected $indexMode;

    /**
     * @var  Attribute
     */
    protected $attribute;

    /**
     * @var  FieldFactory
     */
    protected $dependencyFieldFactory;

    /**
     * @var RelNofollow
     */
    protected $relNofollow;

    /**
     * @var BaseHelper
     */
    private $baseHelper;

    public function __construct(
        Yesno $yesNoSource,
        BaseHelper $baseHelper,
        IndexMode $indexMode,
        RelNofollow $relNofollow,
        Registry $registry,
        FieldFactory $fieldFactory
    ) {
        $this->yesNoSource = $yesNoSource;
        $this->indexMode = $indexMode;
        $this->relNofollow = $relNofollow;
        $this->dependencyFieldFactory = $fieldFactory;
        $this->attribute = $registry->registry('entity_attribute');
        $this->baseHelper = $baseHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getData('form');

        $fieldset = $form->addFieldset(
            'shopby_fieldset_seo',
            ['legend' => __('SEO')]
        );

        if ($this->attribute->getAttributeCode() != Category::ATTRIBUTE_CODE
            && $this->attribute->getFrontendInput() != 'price'
        ) {
            $note = '';
            if ($this->baseHelper->getBrandAttributeCode() == $this->attribute->getAttributeCode()) {
                $note = __('SEO URL is always generated for the brand.');
            }

            $fieldset->addField(
                'is_seo_significant',
                'select',
                [
                    'name'   => 'is_seo_significant',
                    'label'  => __('Generate SEO URL'),
                    'title'  => __('Generate SEO URL'),
                    'note'  => $note,
                    'values' => $this->yesNoSource->toOptionArray(),
                ]
            );
        }

        $fieldset->addField(
            'index_mode',
            'select',
            [
                'name'     => 'index_mode',
                'label'    => __('Allow Google to INDEX the Category Page with the Filter Applied'),
                'title'    => __('Allow Google to INDEX the Category Page with the Filter Applied'),
                'values'   => $this->indexMode->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'follow_mode',
            'select',
            [
                'name'     => 'follow_mode',
                'label'    => __('Allow Google to FOLLOW Links on the Category Page with the Filter Applied'),
                'title'    => __('Allow Google to FOLLOW Links on the Category Page with the Filter Applied'),
                'values'   => $this->indexMode->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'rel_nofollow',
            'select',
            [
                'name'     => 'rel_nofollow',
                'label'    => __('Add rel=\'nofollow\' to Filter Links'),
                'title'    => __('Add rel=\'nofollow\' to filter links'),
                'values'   => $this->relNofollow->toOptionArray(),
            ]
        );

        $dependence = $observer->getData('dependence');

        if ($this->attribute->getAttributeCode() == Category::ATTRIBUTE_CODE) {
            $dependence->addFieldsets(
                $fieldset->getHtmlId(),
                'is_multiselect',
                ['value' => '0', 'negative' => false]
            );
        }
    }
}
