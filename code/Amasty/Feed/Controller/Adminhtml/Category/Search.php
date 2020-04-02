<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Amasty\Feed\Model\RegistryContainer;
use Amasty\Feed\Model\Category\ResourceModel\TaxonomyCollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Search
 *
 * @package Amasty\Feed
 */
class Search extends \Amasty\Feed\Controller\Adminhtml\AbstractCategory
{
    const LANGUAGE_CODE = 'language_code';

    /**
     * @var TaxonomyCollectionFactory
     */
    private $taxonomyCollectionFactory;

    public function __construct(
        Context $context,
        TaxonomyCollectionFactory $taxonomyCollectionFactory
    ) {
        parent::__construct($context);
        $this->taxonomyCollectionFactory = $taxonomyCollectionFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultCategory = [];

        $category = $this->getRequest()->getParam('category');
        $source = $this->getRequest()->getParam('source');

        if ($source && $category) {
            /** @var \Amasty\Feed\Model\Category\ResourceModel\Taxonomy[] $categories */
            $categories = $this->taxonomyCollectionFactory->create()
                ->addFieldToFilter(RegistryContainer::TYPE_CATEGORY, ['like' => '%' . $category . '%'])
                ->addFieldToFilter(self::LANGUAGE_CODE, ['eq' => $source])
                ->addFieldToSelect('category')
                ->getData();

            foreach ($categories as $item) {
                $resultCategory[] = $item['category'];
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($resultCategory);

        return $resultJson;
    }
}
