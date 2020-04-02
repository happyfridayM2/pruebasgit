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

namespace Magezon\PageBuilder\Instagram;

use GuzzleHttp\Client;
use Magezon\PageBuilder\InstagramException\CacheException;
use Magezon\PageBuilder\InstagramException\InstagramException;
use Magezon\PageBuilder\Instagram\Hydrator\HtmlHydrator;
use Magezon\PageBuilder\Instagram\Hydrator\JsonHydrator;
use Magezon\PageBuilder\Instagram\Storage\CacheManager;
use Magezon\PageBuilder\Instagram\Transport\HtmlTransportFeed;
use Magezon\PageBuilder\Instagram\Transport\JsonTransportFeed;

class Api
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var Client
     */
    private $client = null;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $hashTag;

    /**
     * @var string
     */
    private $endCursor = null;

    /**
     * Api constructor.
     * @param Client|null       $client
     * @param CacheManager|null $cacheManager
     */
    public function __construct(CacheManager $cacheManager = null, Client $client = null)
    {
        $this->cacheManager = $cacheManager;
        $this->client       = $client ?: new Client();
    }

    /**
     * @return Hydrator\Component\Feed
     *
     * @throws Exception\CacheException
     * @throws InstagramException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function getFeed()
    {
        // Inspired by https://github.com/pgrimaud/instagram-user-feed

        // if (empty($this->userName)) {
        //     throw new InstagramException('Username cannot be empty');
        // }

        if ($this->endCursor) {
            if (!$this->cacheManager instanceof CacheManager) {
                throw new CacheException('CacheManager object must be specified to use pagination');
            }
            $feed     = new JsonTransportFeed($this->client, $this->getType(), $this->endCursor, $this->cacheManager);
            $hydrator = new JsonHydrator();
        } else {
            $feed     = new HtmlTransportFeed($this->client, $this->getType(), $this->cacheManager);
            $hydrator = new HtmlHydrator();
        }

        $dataFetched = $feed->fetchData($this->key);
        $dataFetched->fetchType = $this->getType();

        $hydrator->setData($dataFetched);

        return $hydrator->getHydratedData();
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @param string $endCursor
     */
    public function setEndCursor($endCursor)
    {
        $this->endCursor = $endCursor;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}
