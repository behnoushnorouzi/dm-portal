<?php

namespace AppBundle\Services;

use \Abraham\TwitterOAuth\TwitterOAuth;

class TwitterFunctions
{
    private $consumer_secret;
    private $consumer_key;
    private $access_token;
    private $token_secret;
    private $oauth;

    /**
     * TwitterFunctions constructor.
     * @param $consumer_key
     * @param $consumer_secret
     * @param $access_token
     * @param $token_secret
     * @param $oauth
     */
    public function __construct($consumer_key, $consumer_secret, $access_token, $token_secret, $oauth)
    {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->access_token = $access_token;
        $this->token_secret = $token_secret;
        $this->oauth = $oauth;

        $this->oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->access_token, $this->token_secret);
    }

    /**
     * @param $tweet
     * @return array|object
     */
    public function postTweetWithoutMedia($tweet): array
    {
        return $this->oauth->post('statuses/update', ['status' => $tweet]);
    }

    /**
     * @param $tweet
     * @param $media
     * @return array|object
     */
    public function postTweetWithMedia($tweet, $media): array
    {
        $file = $this->oauth->upload('media/upload', ['media' => $media]);

        return $this->oauth->post('statuses/update', ['status' => $tweet, 'media_ids' => $file->media_id]);
    }

}
