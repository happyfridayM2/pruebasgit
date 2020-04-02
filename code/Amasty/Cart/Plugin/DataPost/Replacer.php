<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Plugin\DataPost;

use Amasty\Cart\Helper\Data;

class Replacer
{
    const DATA_POST_AJAX = 'data-post-ajax';
    const DATA_POST = 'data-post';
    const REPLACE_REGEX = '(<a[^>]*(%s)?[^>]*)%s([^>]*(%s)?)';
    const HREF_ATTR = '@href="#"@';
    const COMPARE_REGEX = '@(<a[^>]*tocompare[^>]*)data-post([^>]*)@';
    const WISHLIST_REGEX = '@(<a[^>]*)data-post([^>]*towishlist[^>]*)@';

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param string $html
     * @param array $patterns
     */
    public function dataPostReplace(&$html, $patterns = ['@' . self::DATA_POST . '@'])
    {
        if ($this->helper->isActionsAjax()) {
            foreach ($patterns as $pattern) {
                $html = preg_replace(
                    $pattern,
                    '$1' . self::DATA_POST_AJAX . '$2',
                    $html
                );
            }
            $html = preg_replace(self::HREF_ATTR, '', $html);
        }
    }
}
