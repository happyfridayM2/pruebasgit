<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Plugin\Ajax;

use Amasty\Scroll\Helper\Data;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page;

class AjaxAbstract
{
    const OSN_CONFIG = 'amasty.xnotif.config';

    const QUICKVIEW_CONFIG = 'amasty.quickview.config';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        Data $helper,
        Http $request,
        RawFactory $resultRawFactory,
        UrlInterface $url,
        UrlHelper $urlHelper,
        Response $response,
        EncoderInterface $jsonEncoder,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->request = $request;
        $this->response = $response;
        $this->urlHelper = $urlHelper;
        $this->url = $url;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * @param
     *
     * @return bool
     */
    protected function isAjax()
    {
        $isAjax = $this->request->isAjax();
        $isScroll = $this->request->getParam('is_scroll');

        return $this->helper->isEnabled() && $isAjax && $isScroll;
    }

    /**
     * @param Page $page
     *
     * @return array
     */
    protected function getAjaxResponseData(Page $page)
    {
        $currentPage = (int)$this->request->getParam('p', 1);
        $products = $page->getLayout()->getBlock('category.products.list');
        if (!$products) {
            $products = $page->getLayout()->getBlock('search_result_list');

        }

        if ($products) {
            $html = $this->getAdditionalConfigs($page);
            $html .= $products->toHtml();

            //fix bug with multiple adding to cart
            $search = '[data-role=tocart-form]';
            $replace = ".amscroll-pages[amscroll-page='" . $currentPage . "'] " . $search;
            $html = str_replace($search, $replace, $html);

            $this->replaceUencFromHtml($html);
            $html = $this->applyEventChanges($html);
        }

        return [
            'categoryProducts' => $html ?? '',
            'currentPage'      => $currentPage
        ];
    }

    /**
     * Compatibility with Google Page SpeedOptimizer
     * @param string $html
     *
     * @return string|mixed
     */
    protected function applyEventChanges(string $html)
    {
        $dataObject = $this->dataObjectFactory->create(
            [
                'data' => [
                    'page' => $html,
                    'pageType' => 'catalog_category_view'
                ]
            ]
        );
        $this->eventManager->dispatch('amoptimizer_process_ajax_page', ['data' => $dataObject]);
        $html = $dataObject->getData('page');

        return $html;
    }

    /**
     * replace uenc for correct redirect
     *
     * @param $html
     */
    private function replaceUencFromHtml(&$html)
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $this->url->getCurrentUrl();
        $refererUrl = $this->urlHelper->removeRequestParam($refererUrl, 'is_scroll');

        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        $html = str_replace($currentUenc, $newUenc, $html);
    }

    /**
     * @param Page $page
     *
     * @return string
     */
    private function getAdditionalConfigs($page)
    {
        $html = $this->getQuickviewHtml($page);
        $html .= $this->getOsnHtml($page);

        return $html;
    }

    /**
     * @param Page $page
     *
     * @return string
     */
    private function getQuickviewHtml($page)
    {
        return $this->getBlockHtml($page, self::QUICKVIEW_CONFIG);
    }

    /**
     * @param Page $page
     *
     * @return string
     */
    private function getOsnHtml($page)
    {
        return $this->getBlockHtml($page, self::OSN_CONFIG);
    }

    /**
     * @param Page $page
     * @param string $blockName
     *
     * @return string
     */
    private function getBlockHtml($page, $blockName)
    {
        return $page->getLayout()->getBlock($blockName) ? $page->getLayout()->getBlock($blockName)->toHtml() : '';
    }
}
