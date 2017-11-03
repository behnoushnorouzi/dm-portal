<?php
/**
 * Created by PhpStorm.
 * User: Behnoush
 * Date: 02/11/2017
 * Time: 11:32
 */

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwitterFunctions
{
    private $twitter_api_secret;
    private $twitter_api_key;
    private $twitter_redirect_uri;
    private $twitter_api_access_token;
    private $twitter_api_token_secret;
    private $router;
    private $session;


    public function __construct($twitter_api_secret, $twitter_api_key, $twitter_redirect_uri, $twitter_api_access_token, $twitter_api_token_secret, $router, Session $session)
    {
        $this->router = $router;
        $this->session = $session;
        $this->twitter_api_key = $twitter_api_key;
        $this->twitter_api_secret = $twitter_api_secret;
        $this->twitter_redirect_uri = $twitter_redirect_uri;
        $this->twitter_api_access_token = $twitter_api_access_token;
        $this->twitter_api_token_secret = $twitter_api_token_secret;


        $this->oauth = new \Abraham\TwitterOAuth\TwitterOAuth($this->twitter_api_key, $this->twitter_api_secret, $this->twitter_api_access_token, $this->twitter_api_token_secret);
    }

//    public function requestToken()
//    {
//        return $this->oauth->oauth('oauth/request_token',
//            ['oauth_callback' => $this->router->generate(
//                $this->twitter_redirect_uri, array(), UrlGeneratorInterface::ABSOLUTE_URL)]);
//    }
//
//    public function getAuthorisation($oauth_token)
//    {
//        return $this->oauth->url('oauth/authenticate', ['oauth_token'=> $oauth_token]);
//    }

    public function postTweetWithoutMedia()
    {
       return $this->oauth->post('statuses/update' , ['status'=> 'Salut les gens']);
    }

}