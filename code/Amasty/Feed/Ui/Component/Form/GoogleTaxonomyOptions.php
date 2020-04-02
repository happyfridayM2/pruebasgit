<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Ui\Component\Form;

use Amasty\Feed\Model\Category\ResourceModel\TaxonomyCollectionFactory;

/**
 * Class GoogleTaxonomyOptions
 */
class GoogleTaxonomyOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var TaxonomyCollectionFactory
     */
    private $collectionFactory;

    public function __construct(TaxonomyCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $result = [];

        /** @var \Amasty\Feed\Model\Category\ResourceModel\Taxonomy $codes */
        $codes = $this->collectionFactory->create()
            ->distinct(true)
            ->addFieldToSelect('language_code')
            ->getData();

        foreach ($codes as $code) {
            $result[$code['language_code']] = $code['language_code'];

            if ($code['language_code'] == 'en-US') {
                $result[$code['language_code']] = '[default] ' . $code['language_code'];
            }
        }

        return $result;
    }
}
