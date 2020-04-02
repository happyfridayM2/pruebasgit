<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles;

/**
 * return the preview data
 */
class Preview extends \Magento\Backend\Block\Template
{
    /**
     * @var \Wyomind\OrdersExportTool\Model\Profiles|null
     */
    protected $_model=null;

    /**
     * @var \Wyomind\Core\Helper\Data|null
     */
    protected $_helper=null;

    /**
     * @var \Wyomind\OrdersExportTool\Helper\Storage|null
     */
    protected $_storageHelper=null;


    /**
     * Preview constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Wyomind\Core\Helper\Data $helper
     * @param \Wyomind\OrdersExportTool\Helper\Storage $storageHelper
     * @param \Wyomind\OrdersExportTool\Model\Profiles $model
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Wyomind\Core\Helper\Data $helper,
        \Wyomind\OrdersExportTool\Helper\Storage $storageHelper,
        \Wyomind\OrdersExportTool\Model\Profiles $model,
        array $data=[]
    )
    {
        $this->_model=$model;
        $this->_helper=$helper;
        $this->_storageHelper=$storageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get the content of the preview
     * @return string
     * @throws \Exception
     */
    public function getContent()
    {
        $request=$this->getRequest();
        $id=$request->getParam('id');

        $model=$this->_model;
        $model->limit=$this->_helper->getStoreConfig('ordersexporttool/system/preview');
        $model->setDisplay(true);

        if ($model->load($id)) {
            try {

                $content=$model->generate($request, true);
                return $content;
            } catch (\Exception $e) {
                return __('Unable to generate the profile : ' . nl2br($e->getMessage()));
            }
        }
    }

    public function needsCodeMirror()
    {
        $model=$this->_model;
        return ($model->getType() == 1 || $model->getType() > 1 && $model->getFormat() == 2);
    }
}