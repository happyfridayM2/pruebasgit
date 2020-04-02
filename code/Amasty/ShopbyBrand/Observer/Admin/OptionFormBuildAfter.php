<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Observer\Admin;

use Amasty\ShopbyBase\Model\OptionSetting;
use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Framework\Data\Form;
use Magento\Framework\Event\ObserverInterface;

class OptionFormBuildAfter implements ObserverInterface
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $wysiwygConfig;

    public function __construct(
        Page $page,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Amasty\ShopbyBase\Helper\Data $helper,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
    ) {
        $this->page = $page;
        $this->config = $config;
        $this->helper = $helper;
        $this->wysiwygConfig = $wysiwygConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getData('form');

        /** @var OptionSetting $setting */
        $setting = $observer->getData('setting');

        $this->addMetaDataFieldset($form);
        $this->addProductListFieldset($form, $setting);
        $this->addOtherFieldset($observer);
    }

    /**
     * @param Form $form
     */
    private function addMetaDataFieldset(\Magento\Framework\Data\Form $form)
    {
        $metaDataFieldset = $form->addFieldset(
            'meta_data_fieldset',
            [
                'legend' => __('Meta Data'),
                'class'=>'form-inline'
            ]
        );

        $metaDataFieldset->addField(
            'meta_title',
            'text',
            [
                'name' => 'meta_title',
                'label' => __('Meta Title'),
                'title' => __('Meta Title')
            ]
        );

        $metaDataFieldset->addField(
            'meta_description',
            'textarea',
            ['name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description')
            ]
        );

        $metaDataFieldset->addField(
            'meta_keywords',
            'textarea',
            [
                'name' => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords')
            ]
        );
    }

    /**
     * @param Form $form
     * @param OptionSetting $model
     */
    private function addProductListFieldset(\Magento\Framework\Data\Form $form, OptionSetting $model)
    {
        $productListFieldset = $form->addFieldset(
            'product_list_fieldset',
            [
                'legend' => __('Page Content'),
                'class'=>'form-inline'
            ]
        );

        $productListFieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Page Title'), 'title' => __('Title')]
        );

        $productListFieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'wysiwyg' => true,
                'config' => $this->wysiwygConfig->getConfig(['add_variables' => false]),
            ]
        );

        if ($model->getFilterCode() == 'attr_' . $this->helper->getBrandAttributeCode()) {
            $productListFieldset->addField(
                'short_description',
                'textarea',
                [
                    'name' => 'short_description',
                    'label' => __('Short Description'),
                    'title' => __('Short Description')
                ],
                'description'
            );
        }

        $categoryImage = '';
        $categoryImageUseDefault = $model->getData('image_use_default') && $model->getCurrentStoreId();
        if ($model->getImageUrl()) {
            $categoryImage = '
            <div>
            <br>
            <input type="checkbox" id="image_delete" name="image_delete" value="1" ' .
                ($categoryImageUseDefault ? 'disabled="disabled"' : '' ).
            ' />
            <label for="image_delete">' . __('Delete Image') . '</label>
            <br>
            <br><img src="'.$model->getImageUrl().'" ' .($categoryImageUseDefault ? 'style="display:none"' : '').'/>
            </div>';
        }

        $productListFieldset->addField(
            'image',
            'file',
            [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'after_element_html'=>$categoryImage
            ]
        );

        $listCmsBlocks = $this->page->toOptionArray();

        $productListFieldset->addField(
            'top_cms_block_id',
            'select',
            [
                'name' => 'top_cms_block_id',
                'label' => __('Top CMS Block'),
                'title' => __('Top CMS Block'),
                'values' => $listCmsBlocks
            ]
        );

        $productListFieldset->addField(
            'bottom_cms_block_id',
            'select',
            [
                'name' => 'bottom_cms_block_id',
                'label' => __('Bottom CMS Block'),
                'title' => __('Bottom CMS Block'),
                'values' => $listCmsBlocks,
                'note' => __("Please make sure the attribute is selected in the following setting: STORES -> 
                Configuration -> Improved Layered Navigation -> Category Title and Description -> 
                'Add the title & description of the selected filters'")
            ]
        );
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    private function addOtherFieldset(\Magento\Framework\Event\Observer $observer)
    {
        $model = $observer->getData('setting');

        $img = $model->getSliderImageUrl();
        $strictImg = $model->getSliderImageUrl(true);
        $sliderImage = '';
        $imageUseDefault = $model->getData('slider_image_use_default') && $model->getCurrentStoreId();
        if ($img) {
            $storeId = $observer->getData('store');
            $styles = $this->getStyles($storeId);
            $sliderImage = '
            <div><br>
            <input type="checkbox" id="slider_image_delete" name="slider_image_delete" value="1" ' .
                (($imageUseDefault || !$strictImg) ? 'disabled="disabled"' : '' ).
                ' />
            <label for="slider_image_delete">' . __('Delete Image') . '</label>
            <br><br>            
            <img src="' . $img . '" style="' . $styles
                . ($imageUseDefault ? 'display:none;"' : '"') . '/></div>';
        }

        $note = __('Used in Brands Slider, Product Page Icon & Swatch for Multiselect Attribute');
        if (!$strictImg) {
            $note .=  '<br>';
            $note .= __('Page content image is used.');
        }

        $form = $observer->getData('form');
        $featuredFieldset = $form->addFieldset('other_fieldset', ['legend' => __('Other'), 'class'=>'form-inline']);
        $featuredFieldset->addField(
            'slider_image',
            'file',
            [
                'name' => 'slider_image',
                'label' => __('Small Image'),
                'title' => __('Small Image'),
                'note'  => $note,
                'after_element_html'=>$sliderImage
            ]
        );

        $featuredFieldset->addField(
            'small_image_alt',
            'text',
            [
                'name' => 'small_image_alt',
                'label' => __('Small Image Alt'),
                'title' => __('Small Image Alt')
            ]
        );
    }

    /**
     * Get width and height of slider image
     *
     * @param int $storeId
     * @return string
     */
    private function getStyles($storeId)
    {
        $imageWidth = $this->config->getValue(
            'amshopby_brand/slider/image_width',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $imageHeight = $this->config->getValue(
            'amshopby_brand/slider/image_height',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $width = abs((int)($imageWidth));
        $height = abs((int)($imageHeight));
        $res = 'max-width:' . $width . 'px;';
        if ($height) {
            $res .= 'max-height:' . $height . 'px;';
        }
        return $res;
    }
}
