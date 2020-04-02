<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_PageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\PageBuilder\Instagram\Storage;

class Cache
{
    /**
     * @var string
     */
    public $rhxGis;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var array
     */
    public $cookie = [];

    /**
     * @return string
     */
    public function getRhxGis()
    {
        return $this->rhxGis;
    }

    /**
     * @param string $rhxGis
     */
    public function setRhxGis($rhxGis)
    {
        $this->rhxGis = $rhxGis;
    }

    /**
     * @return array
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param array $cookie
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}
