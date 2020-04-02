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
use GuzzleHttp\Cookie\CookieJar;
use Magezon\PageBuilder\InstagramException\InstagramException;
use Magezon\PageBuilder\Instagram\Storage\Cache;
use Magezon\PageBuilder\Instagram\Storage\CacheManager;

class JsonTransportFeed extends TransportFeed
{
    /**
     * @var string
     */
    private $endCursor;

    /**
     * @param Client            $client
     * @param CacheManager|null $cacheManager
     */
    public function __construct(Client $client, $type, $endCursor, CacheManager $cacheManager = null)
    {
        $this->endCursor = $endCursor;
        parent::__construct($client, $type, $cacheManager);
    }

    /**
     * @param $rhxgis
     * @param $variables
     *
     * @return string
     */
    private function generateGis($rhxgis, $variables)
    {
        return sha1($rhxgis . ':' . json_encode($variables));
    }

    /**
     * @param $userName
     *
     * @return mixed
     *
     * @throws InstagramException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Magezon\PageBuilder\InstagramException\CacheException
     */
    public function fetchData($userName)
    {
        /** @var Cache $cache */
        $cache = $this->cacheManager->getCache($userName);

        $variables = [
            'id'    => $cache->getUserId(),
            'first' => '12',
            'after' => $this->endCursor,
        ];

        $cookieJar = CookieJar::fromArray($cache->getCookie(), 'www.instagram.com');

        $headers = [
            'headers' => [
                'user-agent'       => self::USER_AGENT,
                'x-requested-with' => 'XMLHttpRequest',
                'x-instagram-gis'  => $this->generateGis($cache->getRhxGis(), $variables)
            ],
            'cookies' => $cookieJar
        ];

        $endpoint = $this->getEndPoint() . 'graphql/query/?query_hash=' . self::QUERY_HASH . '&variables=' . json_encode($variables);

        $res = $this->client->request('GET', $endpoint, $headers);

        $data = (string)$res->getBody();
        $data = json_decode($data);

        if ($data === null) {
            throw new InstagramException(json_last_error_msg());
        }

        // save to cache for next request
        $newCache = new Cache();
        $newCache->setRhxGis($cache->getRhxGis());
        $newCache->setUserId($cache->getUserId());
        if ($res->hasHeader('Set-Cookie')) {
            $newCache->setCookie($res->getHeaders()['Set-Cookie']);
        }

        $this->cacheManager->set($newCache, $userName);

        return $data->data->user;
    }
}
