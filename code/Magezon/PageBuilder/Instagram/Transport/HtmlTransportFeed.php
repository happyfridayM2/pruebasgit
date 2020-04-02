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
use Magezon\PageBuilder\InstagramException\InstagramException;
use Magezon\PageBuilder\Instagram\Storage\Cache;
use Magezon\PageBuilder\Instagram\Storage\CacheManager;

class HtmlTransportFeed extends TransportFeed
{
    /**
     * HtmlTransportFeed constructor.
     *
     * @param Client            $client
     * @param CacheManager|null $cacheManager
     */
    public function __construct(Client $client, $type, CacheManager $cacheManager = null)
    {
        parent::__construct($client, $type, $cacheManager);
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
        $endpoint = $this->getEndPoint() . $userName . '/';

        $headers = [
            'headers' => [
                'user-agent' => self::USER_AGENT
            ]
        ];

        $res = $this->client->request('GET', $endpoint, $headers);

        $html = (string)$res->getBody();

        preg_match('/<script type="text\/javascript">window\._sharedData\s?=(.+);<\/script>/', $html, $matches);

        if (!isset($matches[1])) {
            throw new InstagramException('Unable to extract JSON data');
        }

        $data = json_decode($matches[1]);

        if ($data === null) {
            throw new InstagramException(json_last_error_msg());
        }

        $type = $this->getType();

        if ($this->cacheManager instanceof CacheManager) {
            $newCache = new Cache();
            if (isset($data->rhx_gis)) {
                $newCache->setRhxGis($data->rhx_gis);
            }
            if ($type=='hashtag') {
                $newCache->setUserId($data->entry_data->TagPage[0]->graphql->hashtag->id);   
            } else {
                $newCache->setUserId($data->entry_data->ProfilePage[0]->graphql->user->id);   
            }

            if ($res->hasHeader('Set-Cookie')) {
                $newCache->setCookie($res->getHeaders()['Set-Cookie']);
            }

            $this->cacheManager->set($newCache, $userName);
        }

        if ($type=='hashtag') {
            return $data->entry_data->TagPage[0]->graphql->hashtag;
        } else {
            return $data->entry_data->ProfilePage[0]->graphql->user;
        }
    }
}
