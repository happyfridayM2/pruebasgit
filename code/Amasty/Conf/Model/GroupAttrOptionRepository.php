<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model;

use Amasty\Conf\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class GroupAttrOptionRepository implements \Amasty\Conf\Api\GroupAttrOptionRepositoryInterface
{
    /**
     * @var array
     */
    protected $groupAttrOptions = [];

    /**
     * @var ResourceModel\GroupAttrOption
     */
    private $groupAttrOption;

    /**
     * @var GroupAttrOptionFactory
     */
    private $groupAttrOptionFactory;

    /**
     * @var ResourceModel\GroupAttrOption\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\Conf\Model\ResourceModel\GroupAttrOption $groupOptionAttrOption,
        \Amasty\Conf\Model\GroupAttrOptionFactory $groupOptionAttrOptionFactory,
        \Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory $collectionFactory
    ) {
        $this->groupAttrOption = $groupOptionAttrOption;
        $this->groupAttrOptionFactory = $groupOptionAttrOptionFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\GroupAttrOptionInterface $groupOption)
    {
        if ($groupOption->getId()) {
            $groupOption = $this->getByOption($groupOption->getOptionId())->addData($groupOption->getData());
        }

        try {
            $this->groupAttrOption->save($groupOption);
            unset($this->groupAttrOptions[$groupOption->getId()]);
        } catch (\Exception $e) {
            if ($groupOption->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save group with ID %1. Error: %2',
                        [$groupOption->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new group. Error: %1', $e->getMessage()));
        }

        return $groupOption;
    }

    /**
     * {@inheritdoc}
     */
    public function getByOption($optionId)
    {
        if (!isset($this->groupAttrOptions[$optionId])) {
            /** @var \Amasty\Conf\Model\GroupAttrOption $groupOption */
            $groupOption = $this->groupAttrOptionFactory->create();
            $this->groupAttrOption->load($groupOption, $optionId, 'option_id');
            if (!$groupOption->getId()) {
                return $groupOption;
            }
            $this->groupAttrOptions[$optionId] = $groupOption;
        }
        return $this->groupAttrOptions[$optionId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByOptionAndGroup($optionId, $groupId)
    {
        $key = $optionId . '-' . $groupId;
        if (!isset($this->groupAttrOptions[$key])) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('option_id', $optionId)
                ->addFieldToFilter('group_id', $groupId);

            /** @var \Amasty\Conf\Model\GroupAttrOption $groupOption */
            $groupOption = $collection->getFirstItem();

            if (!$groupOption->getId()) {
                return $groupOption;
            }
            $this->groupAttrOptions[$key] = $groupOption;
        }

        return $this->groupAttrOptions[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\GroupAttrOptionInterface $groupOption)
    {
        try {
            $this->groupAttrOption->delete($groupOption);
            unset($this->groupAttrOptions[$groupOption->getId()]);
        } catch (\Exception $e) {
            if ($groupOption->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove group with ID %1. Error: %2',
                        [$groupOption->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove group. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($optionId)
    {
        $model = $this->getByOption($optionId);
        $this->delete($model);
        return true;
    }

    /**
     * @param $optionId
     * @param $groupId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByGroupIdAndOptionId($optionId, $groupId)
    {
        $model = $this->getByOptionAndGroup($optionId, $groupId);
        $this->delete($model);
        return true;
    }
}
