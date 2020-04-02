<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Request;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Class Builder
 * @package Amasty\Shopby\Model\Request
 */
class Builder extends \Magento\Framework\Search\Request\Builder
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var array
     */
    protected $removablePlaceholders = [];

    /**
     * @var int
     */
    protected $baseCategory;

    /**
     * @var array
     */
    private $aggregationsOnly = [];

    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Search\Request\Config $config,
        \Magento\Framework\Search\Request\Binder $binder,
        \Magento\Framework\Search\Request\Cleaner $cleaner,
        \Magento\Framework\App\Request\Http $http
    ) {
        parent::__construct($objectManager, $config, $binder, $cleaner);
        $this->httpRequest = $http;
    }

    /**
     * @param string $placeholder
     * @param mixed $value
     * @return $this
     */
    public function bind($placeholder, $value)
    {
        $this->removablePlaceholders[$placeholder] = $placeholder == \Amasty\Shopby\Helper\Category::ATTRIBUTE_CODE
            ? $this->makeCategoryPlaceholder($placeholder, $value)
            : $value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setAggregationsOnly($value)
    {
        if (is_string($value)) {
            $this->aggregationsOnly = [$value];
        } elseif (is_array($value)) {
            $this->aggregationsOnly = $value;
        }

        return $this;
    }

    /**
     * @param $placeholder
     * @param $value
     * @return array
     */
    public function makeCategoryPlaceholder($placeholder, $value)
    {
        if (!$this->baseCategory) {
            $this->baseCategory = $this->httpRequest->getParam('id') ?: $value;
        }

        $oldValueExist = isset($this->removablePlaceholders[$placeholder])
            && $this->removablePlaceholders[$placeholder] !== $value;
        if ($oldValueExist) {
            $value = $this->makeCategoryPlaceholderList($this->removablePlaceholders[$placeholder], $value);
        }

        return $value;
    }

    /**
     * @param $removablePlaceholders
     * @param $value
     * @return array
     */
    public function makeCategoryPlaceholderList($removablePlaceholders, $value)
    {
        $removablePlaceholders = array_unique(array_merge((array) $removablePlaceholders, (array) $value));
        $removablePlaceholders = array_values(array_diff($removablePlaceholders, (array) $this->baseCategory));

        return $removablePlaceholders;
    }

    /**
     * @param $placeholder
     * @return $this
     */
    public function removePlaceholder($placeholder)
    {
        if (array_key_exists($placeholder, $this->removablePlaceholders)) {
            unset($this->removablePlaceholders[$placeholder]);
        }
        return $this;
    }

    /**
     * @param $placeholder
     * @return bool
     */
    public function hasPlaceholder($placeholder)
    {
        return array_key_exists($placeholder, $this->removablePlaceholders);
    }

    /**
     * Create request object
     *
     * @return RequestInterface
     */
    public function create()
    {
        $this->commitCancelablePlaceholders();
        $request = parent::create();
        $this->removeAggregations($request);
        return $request;
    }

    /**
     * @param $request
     * @return $this
     */
    private function removeAggregations($request)
    {
        if (!empty($this->aggregationsOnly)) {
            $buckets = $request->getAggregation();
            foreach ($buckets as $key => $bucket) {
                if (!in_array($bucket->getField(), $this->aggregationsOnly)) {
                    unset($buckets[$key]);
                }
            }

            $reflection = new \ReflectionClass($request);
            $bucketProperty = $reflection->getProperty('buckets');
            $bucketProperty->setAccessible(true);
            $bucketProperty->setValue($request, $buckets);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function commitCancelablePlaceholders()
    {
        foreach ($this->removablePlaceholders as $key => $value) {
            parent::bind($key, $value);
        }

        return $this;
    }
}
