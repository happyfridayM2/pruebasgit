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

namespace Magezon\PageBuilder\Instagram\Hydrator;

use Magezon\PageBuilder\Instagram\Hydrator\Component\Feed;
use Magezon\PageBuilder\Instagram\Hydrator\Component\Media;
use Magezon\PageBuilder\Instagram\Transport\TransportFeed;

class HtmlHydrator
{
    /**
     * @var \stdClass
     */
    private $data;

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return Feed
     *
     * @throws \Exception
     */
    public function getHydratedData()
    {
        $feed = $this->generateFeed();

        if ($this->data->fetchType=='hashtag') {
            $edges = $this->data->edge_hashtag_to_media->edges;
        } else {
            $edges = $this->data->edge_owner_to_timeline_media->edges;
        }

        foreach ($edges as $edge) {

            /** @var \stdClass $node */
            $node = $edge->node;

            $media = new Media();

            $media->setId($node->id);
            $media->setTypeName($node->__typename);

            if ($node->edge_media_to_caption->edges) {
                $media->setCaption($node->edge_media_to_caption->edges[0]->node->text);
            }

            $media->setHeight($node->dimensions->height);
            $media->setWidth($node->dimensions->width);

            $media->setThumbnailSrc($node->thumbnail_src);
            $media->setDisplaySrc($node->display_url);

            $date = new \DateTime();
            $date->setTimestamp($node->taken_at_timestamp);

            $media->setDate($date);

            $media->setComments($node->edge_media_to_comment->count);
            $media->setLikes($node->edge_liked_by->count);

            $media->setLink(TransportFeed::INSTAGRAM_ENDPOINT . "p/{$node->shortcode}/");

            $media->setThumbnails($node->thumbnail_resources);

            $media->setVideo((bool)$node->is_video);

            if (property_exists($node, 'video_view_count')) {
                $media->setVideoViewCount((int)$node->video_view_count);
            }
            
            $feed->addMedia($media);
        }

        return $feed;
    }

    /**
     * @return Feed
     */
    private function generateFeed()
    {
        $feed = new Feed();
        $feed->setId($this->data->id);

        if ($this->data->fetchType=='hashtag') {
            $feed->setMediaCount($this->data->edge_hashtag_to_media->count);
            $feed->setEndCursor($this->data->edge_hashtag_to_media->page_info->end_cursor);
            $feed->setProfilePicture($this->data->profile_pic_url);
        } else if ($this->data->fetchType=='username') {
            $feed->setUserName($this->data->username);
            $feed->setBiography($this->data->biography);
            $feed->setFullName($this->data->full_name);
            $feed->setProfilePicture($this->data->profile_pic_url_hd);
            $feed->setMediaCount($this->data->edge_owner_to_timeline_media->count);
            $feed->setFollowers($this->data->edge_followed_by->count);
            $feed->setFollowing($this->data->edge_follow->count);
            $feed->setExternalUrl($this->data->external_url);
            $feed->setPrivate($this->data->is_private);
            $feed->setVerified($this->data->is_verified);
            $feed->setEndCursor($this->data->edge_owner_to_timeline_media->page_info->end_cursor);
        }

        return $feed;
    }
}
