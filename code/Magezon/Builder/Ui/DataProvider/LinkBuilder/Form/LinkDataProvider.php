<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Builder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Builder\Ui\DataProvider\LinkBuilder\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

class LinkDataProvider extends AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string                                  $name              
     * @param string                                  $primaryFieldName  
     * @param string                                  $requestFieldName  
     * @param CollectionFactory                       $collectionFactory 
     * @param \Magento\Framework\App\RequestInterface $request           
     * @param array                                   $meta              
     * @param array                                   $data              
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request    = $request;
    }

    public function getData()
    {
        $this->loadedData[''] = $this->request->getParam('source');
        return $this->loadedData;
    }

    public function getMeta()
    {
        return parent::getMeta();
    }
}
