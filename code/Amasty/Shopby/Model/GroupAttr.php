<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model;

use Amasty\Shopby\Api\Data\GroupAttrInterface;
use Amasty\Shopby\Api\Data\GroupAttrOptionInterface;
use Amasty\Shopby\Api\Data\GroupAttrValueInterface;

/**
 * Class GroupAttr
 * @package Amasty\Shopby\Model
 */
class GroupAttr extends \Magento\Framework\Model\AbstractModel implements GroupAttrInterface
{
    /**
     * @var GroupAttrOptionInterface[]
     */
    private $options = [];

    /**
     * @var GroupAttrValueInterface[]
     */
    private $values = [];

    protected function _construct()
    {
        $this->_init(\Amasty\Shopby\Model\ResourceModel\GroupAttr::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @return int
     */
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @return string
     */
    public function getGroupCode()
    {
        return $this->getData(self::GROUP_CODE);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @return int
     */
    public function getVisual()
    {
        return $this->getData(self::VISUAL);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @return bool
     */
    public function getEnabled()
    {
        return $this->getData(self::ENABLED);
    }

    /**
     * @return bool
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param $id
     * @return GroupAttrInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @param $id
     * @return GroupAttrInterface
     */
    public function setAttributeId($id)
    {
        return $this->setData(self::ATTRIBUTE_ID, $id);
    }

    /**
     * @param $name
     * @return GroupAttrInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @param $code
     * @return GroupAttrInterface
     */
    public function setGroupCode($code)
    {
        return $this->setData(self::GROUP_CODE, $code);
    }

    /**
     * @param $url
     * @return GroupAttrInterface
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * @param $visual
     * @return GroupAttrInterface
     */
    public function setVisual($visual)
    {
        return $this->setData(self::VISUAL, $visual);
    }

    /**
     * @param $type
     * @return GroupAttrInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @param $pos
     * @return GroupAttrInterface
     */
    public function setPosition($pos)
    {
        return $this->setData(self::POSITION, $pos);
    }

    /**
     * @param $enabled
     * @return GroupAttrInterface
     */
    public function setEnabled($enabled)
    {
        return $this->setData(self::ENABLED, $enabled);
    }

    /**
     * @param GroupAttrOptionInterface $option
     * @return $this
     */
    public function addOption(GroupAttrOptionInterface $option)
    {
        $this->options[] = $option;
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return GroupAttrOptionInterface[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function hasOptions()
    {
        return !empty($this->options);
    }

    /**
     * @param GroupAttrValueInterface $value
     * @return $this
     */
    public function addValue(GroupAttrValueInterface $value)
    {
        $this->values[] = $value;
        return $this;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function setValues(array $values = [])
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @return GroupAttrValueInterface[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return bool
     */
    public function hasValues()
    {
        return !empty($this->values);
    }
}
