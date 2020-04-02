<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Plugin;

use Amasty\ShopbySeo\Helper\Meta;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class CategoryViewPlugin
 * @package Amasty\ShopbySeo\Plugin
 */
class CategoryViewPlugin
{
    /**
     * @var  Meta
     */
    protected $metaHelper;

    public function __construct(Meta $metaHelper)
    {
        $this->metaHelper = $metaHelper;
    }

    /**
     * @param Action $subject
     * @param Page $result
     * @return ResultInterface
     */
    public function afterExecute(Action $subject, $result)
    {
        if ($result instanceof Page) {
            $this->metaHelper->setPageTags($result->getConfig());
        }
        return $result;
    }
}
