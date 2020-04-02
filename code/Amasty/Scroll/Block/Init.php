<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Block;

use Amasty\Scroll\Helper\Data;
use Amasty\Scroll\Model\Source\Loading;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;

class Init extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Http
     */
    private $request;

    public function __construct(
        Context $context,
        Data $helper,
        EncoderInterface $jsonEncoder,
        Http $request,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->request = $request;
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * @return string
     */
    public function getProductsBlockSelector()
    {
        $originSelectors = $this->helper->getModuleConfig('advanced/product_container_group');

        //compatibility with Amasty_PromoBanners
        $selectors = ($originSelectors === null) ? ['.products.wrapper'] : explode(',', $originSelectors);
        foreach ($selectors as &$selector) {
            $selector = rtrim($selector);
            $selector .= ':not(.amasty-banners)';
        }

        return implode(',', $selectors);
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        $currentPage = (int)$this->request->getParam('p', 1);
        $iconUrl = $this->getViewFileUrl((string)$this->helper->getModuleConfig('general/loading_icon'));

        $params = [
            'actionMode'              => $this->helper->getModuleConfig('general/loading'),
            'product_container'       => $this->getProductsBlockSelector(),
            'loadingImage'            => $iconUrl,
            'pageNumbers'             => $this->helper->getModuleConfig('general/page_numbers'),
            'pageNumberContent'       => __('PAGE #'),
            'loadNextStyle'           => $this->helper->getModuleConfig('button/styles'),
            'loadingafterTextButton'  => $this->helper->getModuleConfig('button/label_after'),
            'loadingbeforeTextButton' => $this->helper->getModuleConfig('button/label_before'),
            'progressbar'             => $this->helper->getModuleConfig('info'),
            'progressbarText'         => __('PAGE %1 of %2'),
            'current_page'            => $currentPage,
        ];

        if ($this->helper->getModuleConfig('general/loading') == Loading::COMBINED) {
            $pagesBeforeButton = $this->helper->getModuleConfig('general/num_pages_before_button');
            $params['pages_before_button'] = (int)$pagesBeforeButton ?: Loading::DEFAULT_COMBINED_VALUE;
        }

        return $this->jsonEncoder->encode($params);
    }
}
