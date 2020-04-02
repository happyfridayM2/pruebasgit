<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Controller\Adminhtml;

abstract class Group extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_Conf::group_attributes';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Amasty\Conf\Model\GroupAttrFactory
     */
    protected $groupAttrFactory;

    /**
     * @var \Magento\Backend\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Amasty\Conf\Model\GroupAttrRepository
     */
    protected $groupAttrRepository;

    /**
     * Group constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Amasty\Conf\Model\GroupAttrFactory $groupAttrFactory
     * @param \Magento\Backend\Model\SessionFactory $sessionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\Conf\Model\GroupAttrFactory $groupAttrFactory,
        \Amasty\Conf\Model\GroupAttrRepository $groupAttrRepository,
        \Magento\Backend\Model\SessionFactory $sessionFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->groupAttrFactory = $groupAttrFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->sessionFactory = $sessionFactory;
        $this->groupAttrRepository = $groupAttrRepository;
        parent::__construct($context);
    }
}
