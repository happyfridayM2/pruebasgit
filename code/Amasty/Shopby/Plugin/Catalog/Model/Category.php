<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model;

use \Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class Category
{
    const THUMBNAIL = 'thumbnail';

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Catalog\Model\Category $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetAttributes(\Magento\Catalog\Model\Category $subject, $result)
    {
        if (!isset($result[self::THUMBNAIL])) {
            try {
                $attribute = $this->attributeRepository->get(
                \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE,
                self::THUMBNAIL
                );
                $result[self::THUMBNAIL] = $attribute;
            } catch (LocalizedException $e) {
                $this->logger->critical($e);
            }
        }

        return $result;
    }
}
