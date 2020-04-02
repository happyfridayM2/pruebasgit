<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Feed\Edit;

/**
 * Class Tabs
 *
 * @package Amasty\Feed
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry= $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct()
    {
        $this->setId('feed_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Feed View'));
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $model = $this->registry->registry('current_amfeed_feed');
        if ($model->getId()) {
            if ($model->isCsv()) {
                $this->addTabAfter(
                    'content',
                    [
                        'label' => __('Content'),
                        'content' => $this->getLayout()->createBlock(
                            \Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Csv::class
                        )->toHtml(),
                    ],
                    'feed_tab_general'
                );
            } elseif ($model->isXml()) {
                $this->addTabAfter(
                    'content',
                    [
                        'label' => __('Content'),
                        'content' => $this->getLayout()->createBlock(
                            \Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Xml::class
                        )->toHtml(),
                    ],
                    'feed_tab_general'
                );
            }
        }
    }
}
