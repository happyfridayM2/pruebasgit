<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Plugin\Ajax;

use Magento\Framework\View\Result\Page;

class InitAjaxSearchPage extends AjaxAbstract
{
    public function aroundRenderLayout(
        \Magento\Framework\App\View $subject,
        \Closure $proceed,
        $output = ''
    ) {
        $page = $subject->getPage();

        if (!($page instanceof Page) || !$this->isAjax() || $this->request->getRouteName() !== 'catalogsearch') {
            return $proceed($output);
        }

        $responseData = $this->getAjaxResponseData($page);
        $this->response->setBody($this->jsonEncoder->encode($responseData));
    }
}
