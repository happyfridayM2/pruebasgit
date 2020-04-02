<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Amasty\Feed\Api\FeedRepositoryInterface;
use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class UpgradeDataTo227
 */
class UpgradeDataTo227
{
    /**
     * @var CollectionFactory
     */
    private $feedCollectionFactory;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var FeedRepositoryInterface
     */
    private $feedRepository;

    public function __construct(
        FeedRepositoryInterface $feedRepository,
        CollectionFactory $feedCollectionFactory,
        EncryptorInterface $encryptor
    ) {
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->encryptor = $encryptor;
        $this->feedRepository = $feedRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $feeds = $this->feedCollectionFactory->create()->getItems();

        /** @var Feed $feed */
        foreach ($feeds as $feed) {
            $oldPass = $feed->getDeliveryPassword();

            if ($oldPass) {
                $feed->setDeliveryPassword($this->encryptor->encrypt($feed->getDeliveryPassword()));
                $this->feedRepository->save($feed);
            }
        }
    }
}
