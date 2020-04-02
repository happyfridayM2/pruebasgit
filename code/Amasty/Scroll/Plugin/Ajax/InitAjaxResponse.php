<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Plugin\Ajax;

use Magento\Catalog\Controller\Category\View;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Raw;

class InitAjaxResponse extends AjaxAbstract
{
    /**
     * @param $controller
     * @param null $page
     *
     * @return Raw|null
     */
    public function afterExecute($controller, $page = null)
    {
        if (!$this->isAjax() || !$page instanceof Page) {
            return $page;
        }

        $responseData = $this->getAjaxResponseData($page);
        $response = $this->prepareResponse($responseData);

        return $response;
    }

    /**
     * @param array $data
     *
     * @return Raw
     */
    protected function prepareResponse(array $data)
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-Type', 'application/json');
        $response->setContents($this->jsonEncoder->encode($data));

        return $response;
    }
}
