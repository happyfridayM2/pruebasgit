<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\Integration;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class IntegrationException extends LocalizedException
{
    /**
     * @param Phrase|null $phrase
     * @param \Exception|null $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase(
                'Requested Improved Navigation submodule is disabled. Only read methods is allowed.'
            );
        }

        parent::__construct($phrase, $cause);
    }
}
