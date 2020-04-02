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

class GroupAttrRepository implements \Amasty\Conf\Api\GroupAttrRepositoryInterface
{
    /**
     * @var array
     */
    protected $groupAttrs = [];
    
    /**
     * @var ResourceModel\GroupAttr
     */
    private $groupAttr;
    
    /**
     * @var GroupAttrFactory
     */
    private $groupAttrFactory;

    public function __construct(
        \Amasty\Conf\Model\ResourceModel\GroupAttr $groupAttr,
        \Amasty\Conf\Model\GroupAttrFactory $groupAttrFactory
    ) {

        $this->groupAttr = $groupAttr;
        $this->groupAttrFactory = $groupAttrFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\GroupAttrInterface $group)
    {
        if ($group->getGroupId()) {
            $group = $this->get($group->getGroupId())->addData($group->getData());
        }

        try {
            $this->groupAttr->save($group);
            unset($this->groupAttrs[$group->getGroupId()]);
        } catch (\Exception $e) {
            if ($group->getGroupId()) {
                throw new CouldNotSaveException(
                    __('Unable to save group with ID %1. Error: %2', [$group->getGroupId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new group. Error: %1', $e->getMessage()));
        }
        
        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function get($groupId)
    {
        if (!isset($this->groupAttrs[$groupId])) {
            /** @var \Amasty\Conf\Model\GroupAttr $group */
            $group = $this->groupAttrFactory->create();
            $this->groupAttr->load($group, $groupId);
            if (!$group->getGroupId()) {
                return $group;
            }
            $this->groupAttrs[$groupId] = $group;
        }
        return $this->groupAttrs[$groupId];
    }

    public function getByGroupCode($groupCode)
    {
        if (!isset($this->groupAttrs[$groupCode])) {
            /** @var \Amasty\Conf\Model\GroupAttr $group */
            $group = $this->groupAttrFactory->create();
            $this->groupAttr->load($group, $groupCode, 'group_code');
            if (!$group->getGroupId()) {
                return false;
            }
            $this->groupAttrs[$groupCode] = $group;
            $this->groupAttrs[$group->getGroupId()] = $group;
        }
        return $this->groupAttrs[$groupCode];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\GroupAttrInterface $group)
    {
        try {
            $this->groupAttr->delete($group);
            unset($this->groupAttrs[$group->getGroupId()]);
        } catch (\Exception $e) {
            if ($group->getGroupId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove group with ID %1. Error: %2', [$group->getGroupId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove group. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($groupId)
    {
        $model = $this->get($groupId);
        $this->delete($model);
        return true;
    }
}
