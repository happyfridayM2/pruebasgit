<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Framework\App;

use Magento\Framework\App\FrontController as DefaultFronController;
use Magento\Framework\App\RequestInterface;
use Amasty\Shopby\Helper\Category as CategoryHelper;

class FrontController
{
    const SHOPBY_EXTRA_PARAM = 'amshopby';

    /**
     * @var bool|null
     */
    private $isCategorySingleSelect = null;

    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    public function __construct(CategoryHelper $categoryHelper)
    {
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * @param DefaultFronController $subject
     * @param RequestInterface $request
     * @return array
     */
    public function beforeDispatch(DefaultFronController $subject, RequestInterface $request)
    {
        $this->parseAmshopbyParams($request);
        return [$request];
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    private function parseAmshopbyParams(RequestInterface $request)
    {
        if ($amShopbyParams = $request->getParam(self::SHOPBY_EXTRA_PARAM, [])) {
            $amShopbyParams = is_array($amShopbyParams) ? $amShopbyParams : [];
            foreach ($amShopbyParams as $key => $value) {
                if ($key == CategoryHelper::CATEGORY_FILTER_PARAM
                    && $this->isCategorySingleSelect()
                ) {
                    continue;
                }
                $request->setQueryValue($key, implode(",", $value));
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function isCategorySingleSelect()
    {
        if ($this->isCategorySingleSelect === null) {
            $this->isCategorySingleSelect = !$this->categoryHelper->getSetting()->isMultiselect();
        }

        return $this->isCategorySingleSelect;
    }
}
