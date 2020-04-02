<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Controller\Adminhtml;

use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Class Group
 * @package Amasty\Shopby\Controller\Adminhtml
 */
abstract class Group extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_Shopby::group_attributes';

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
     * @var \Amasty\Shopby\Model\GroupAttrFactory
     */
    protected $groupAttrFactory;

    /**
     * @var \Amasty\Shopby\Model\GroupAttrRepository
     */
    protected $groupAttrRepository;

    /**
     * @var \Magento\Backend\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var  TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * Group constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Amasty\Shopby\Model\GroupAttrFactory $groupAttrFactory
     * @param \Amasty\Shopby\Model\GroupAttrRepository $groupAttrRepository
     * @param \Magento\Backend\Model\SessionFactory $sessionFactory
     * @param TypeListInterface $typeList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\Shopby\Model\GroupAttrFactory $groupAttrFactory,
        \Amasty\Shopby\Api\Data\GroupAttrRepositoryInterface $groupAttrRepository,
        \Magento\Backend\Model\SessionFactory $sessionFactory,
        TypeListInterface $typeList
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->groupAttrFactory = $groupAttrFactory;
        $this->groupAttrRepository = $groupAttrRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->sessionFactory = $sessionFactory;
        $this->cacheTypeList = $typeList;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Shopby::group_attributes');
    }
}
