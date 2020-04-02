<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\Category;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Manager
 */
class Manager extends \Magento\Framework\DataObject
{
    const CATEGORY_FORCE_MIXED_MODE = 'amshopby_force_mixed_mode';

    const CATEGORY_SHOPBY_IMAGE_URL = 'amshopby_category_image_url';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Session $catalogSession,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession = $catalogSession;
        $this->eventManager = $context->getEventManager();
        $this->logger = $logger;
        parent::__construct($data);
    }

    /**
     * @return int
     */
    public function getRootCategoryId()
    {
        return $this->storeManager->getStore()->getRootCategoryId();
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return int
     */
    protected function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param null $controllerAction
     * @return bool|\Magento\Catalog\Api\Data\CategoryInterface
     */
    public function init($controllerAction = null)
    {
        $category = $this->getCategoryModel();
        if ($category) {
            $this->catalogSession->setLastVisitedCategoryId($category->getId());
            if (!$this->coreRegistry->registry('current_category')) {
                $this->coreRegistry->register('current_category', $category);
            }

            $this->eventManager->dispatch(
                'amshopby_category_manager_init_after',
                ['category' => $category]
            );

            try {
                $this->eventManager->dispatch(
                    'catalog_controller_category_init_after',
                    ['category' => $category, 'controller_action' => $controllerAction]
                );
            } catch (LocalizedException $e) {
                $this->logger->critical($e);
                return false;
            }
        }

        return $category;
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\CategoryInterface
     */
    protected function getCategoryModel()
    {
        $categoryId = $this->getRootCategoryId();
        if (!$categoryId) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $this->getCurrentStoreId());
            // Workaround to show filters on 'all-products' in Magento 2.2
            $category->setData('is_anchor', 1);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }

        return $category;
    }
}
