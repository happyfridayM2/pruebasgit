<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\Page;

/**
 * Class CategoryViewAjax
 * @package Amasty\Shopby\Plugin\Ajax
 */
class CategoryViewAjax extends Ajax
{
    /**
     * @param Action $controller
     *
     * @return array
     */
    public function beforeExecute(Action $controller)
    {
        if ($this->isAjax($controller->getRequest())) {
            $this->getActionFlag()->set('', 'no-renderLayout', true);
        }

        return [];
    }

    /**
     * @param Action $controller
     * @param Page $page
     *
     * @return \Magento\Framework\Controller\Result\Raw|Page
     */
    public function afterExecute(Action $controller, $page)
    {
        if (!$this->isAjax($controller->getRequest())) {
            return $page;
        }

        $responseData = $this->getAjaxResponseData();
        $response = $this->prepareResponse($responseData);
        return $response;
    }
}
