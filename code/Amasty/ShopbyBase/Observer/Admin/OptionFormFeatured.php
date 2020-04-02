<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Observer\Admin;

use Magento\Framework\Data\Form;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Event\ObserverInterface;

class OptionFormFeatured implements ObserverInterface
{
    /**
     * @var Yesno
     */
    private $yesNoSource;

    /**
     * System event manager
     *
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * OptionFormFeatured constructor.
     * @param Yesno $yesNoSource
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        Yesno $yesNoSource,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->yesNoSource = $yesNoSource;
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getEvent()->getForm();
        $setting = $observer->getEvent()->getSetting();

        $featuredFieldset = $form->addFieldset(
            'featured_fieldset',
            ['legend' => __('Featured Options'), 'class'=>'form-inline']
        );

        $featuredFieldset->addField(
            'is_featured',
            'select',
            [
                'name'      => 'is_featured',
                'label'     => __('Is Featured'),
                'title'     => __('Is Featured'),
                'values'    => $this->yesNoSource->toOptionArray(),
                'note'      => __(
                    'Not featured options will be hidden under ‘show more’ element (if at least one '
                    . 'attribute\'s option is marked as ‘Is Featured’). '
                )
            ]
        );

        $this->eventManager->dispatch(
            'amshopby_option_form_featured_fieldset',
            [
                'form' => $form,
                'fieldset' => $featuredFieldset,
                'setting' => $setting,
                'is_slider' => $observer->getEvent()->getIsSlider()
            ]
        );

        $seoFieldset = $form->addFieldset('seo_fieldset', ['legend' => __('SEO'), 'class'=>'form-inline']);
        $seoFieldset->addField(
            'url_alias',
            'text',
            ['name' => 'url_alias', 'label' => __('URL alias'), 'title' => __('URL alias')]
        );
    }
}
