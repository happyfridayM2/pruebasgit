<?php
/**
 * @category  Trustedshops
 * @package   Trustedshops\Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */

namespace Trustedshops\Trustedshops\Block;

class Rating extends Base
{
    /**
     * check if ratings are active
     *
     * @return bool
     */
    public function isActive()
    {
        if (!parent::isActive()) {
            return false;
        }

        if ($this->isExpert()) {
            if (!$this->getConfig('collect_orders', 'trustbadge')) {
                return false;
            }
            if (!$this->getConfig('expert_collect_reviews', 'product')) {
                return false;
            }
            return $this->getConfig('expert_rating_active', 'product');
        }

        if (!$this->getConfig('collect_reviews', 'product')) {
            return false;
        }
        return $this->getConfig('rating_active', 'product');
    }

    /**
     * get the star color
     *
     * @return string
     */
    public function getStarColor()
    {
        return $this->getConfig('rating_star_color', 'product');
    }

    /**
     * get the star size
     *
     * @return string
     */
    public function getStarSize()
    {
        return $this->getConfig('rating_star_size', 'product');
    }

    /**
     * get the font size
     *
     * @return string
     */
    public function getFontSize()
    {
        return $this->getConfig('rating_font_size', 'product');
    }

    /**
     * get the expert rating code
     * and replace variables
     *
     * @return string
     */
    public function getCode()
    {
        $expertCode = $this->getConfig('rating_code', 'product');
        return $this->replaceVariables($expertCode);
    }
}
