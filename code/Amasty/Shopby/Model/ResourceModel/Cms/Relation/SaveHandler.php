<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\ResourceModel\Cms\Relation;

use Braintree\Exception;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SaveHandler
 * @package Amasty\Shopby\Model\ResourceModel\Cms\Relation
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var \Amasty\Shopby\Api\CmsPageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var \Amasty\Shopby\Model\Cms\PageFactory
     */
    protected $pageFactory;

    /**
     * SaveHandler constructor.
     * @param \Amasty\Shopby\Api\CmsPageRepositoryInterface $cmsPageRepository
     */
    public function __construct(
        \Amasty\Shopby\Api\CmsPageRepositoryInterface $cmsPageRepository,
        \Amasty\Shopby\Model\Cms\PageFactory $factory
    ) {
        $this->pageRepository = $cmsPageRepository;
        $this->pageFactory = $factory;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $settings = $entity->getData('amshopby_settings');

        if (is_array($settings)) {
            try {
                $shopbyPage = $this->pageRepository->getByPageId($entity->getId());
            } catch (NoSuchEntityException $e) {
                $shopbyPage = $this->pageFactory->create();
            }
            $shopbyPage->setData(array_merge(['page_id' => $entity->getId()], $settings));
            $this->pageRepository->save($shopbyPage);
        }

        return $entity;
    }
}
