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

namespace Magezon\PageBuilder\Instagram\Transport;

use GuzzleHttp\Client;
use Magezon\PageBuilder\Instagram\Storage\CacheManager;

abstract class TransportFeed
{
    const INSTAGRAM_ENDPOINT      = 'https://www.instagram.com/';
    const INSTAGRAM_HASH_ENDPOINT = 'https://instagram.com/explore/tags/';
    const USER_AGENT              = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36';
    const QUERY_HASH              = '42323d64886122307be10013ad2dcc44';

    /**
     * @var CacheManager|null
     */
    protected $cacheManager;

    /**
     * @var Client
     */
    protected $client;

    /**
     * TransportFeed constructor.
     *
     * @param Client            $client
     * @param CacheManager|null $cacheManager
     */
    public function __construct(Client $client, $type, CacheManager $cacheManager = null)
    {
        $this->cacheManager = $cacheManager;
        $this->client       = $client;
        $this->type         = $type;
    }

    public function getEndPoint()
    {
        if ($this->type == 'hashtag') {
            return self::INSTAGRAM_HASH_ENDPOINT;
        } else {
            return self::INSTAGRAM_ENDPOINT;
        }
    }

    public function getType()
    {
        return $this->type;
    }
}
