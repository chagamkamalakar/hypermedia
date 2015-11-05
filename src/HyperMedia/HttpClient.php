<?php

namespace HyperMedia;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use HyperMedia\Exceptions\InvalidResourceAccessException;
use HyperMedia\Helpers\Inflect;
use HyperMedia\Helpers\LinkHeaderParser;


trait HttpClient
{
    protected $base_url = "";
    protected  $http_client = null;
    public $response = null;
    protected $body;
    public $uri = "";

    use Pagination;
    use Resource;

    function __construct(Client $client)
    {
        $this->http_client = $client;
        $this->sendRequest($this->getBaseURL());
    }

    public function __call($method,$args=[])
    {
        //var_dump($method,$this->getClient());
        $uri = $this->verifyNextStatePossibility($method);


        //to give fluent interface functionality
        return $this->createClientObject($uri,$args);
    }

    public function __get($property){
        $body = $this->extractBodyFromResponse();

        if(isset($body[$property]))
            return $body[$property];
        return null;
    }

    function appendResourceNameToURLIfGiven($args){
        $this->uri .= $this->extractResourceName($args);
    }
    /**
     * delegate method call to GuzzleHttp/Client object
     * @param $uri
     * @return \Psr\Http\Message\ResponseInterface
     */
    function sendRequest($url){
        try {
            $this->response = $this->http_client->get($url,['auth' => $this->authDetailsUserNameAndPWD() ]);
        }catch (ClientException $ce){
            return null;
        }

        return $this->response;
    }

     abstract function authDetailsUserNameAndPWD();

    function createClientObject($uri,$args=null){


        $client = new self($this->http_client);
        $client->uri = $uri;
        /*
         this is to append resource name to url
         function call-  $client->users('laravel')
         format like this  github.com/users/laravel
        */
        $client->appendResourceNameToURLIfGiven($args);

        //$uri = $this->prepareURI($method,$args);
        $client->sendRequest($client->uri);
        // throws exception
        //$this->validateLastRequestStatus();

        $client->uri = $uri;
        //$client->response = $this->response;
        $client->header_links = $client->extractLinkHeaderFromResponse();
        // this is to store like array of arrays for /users
        $client->collection = array_merge($client->collection, $client->extractBodyFromResponse());
        return $client;
    }

    function validateLastRequestStatus() {
        //var_dump($this->response,"validate");
        if(!is_null($this->response)) {

            $status_code = $this->response->getStatusCode();

            if ($status_code >= 200 && $status_code <= 299) {
                return true;
            }
        }
        throw  new InvalidResourceAccessException("Invalid Resource is accessed");
    }

    function prepareURL($resource_uri){
        return  $this->getBaseURL() . '/' . $resource_uri;
    }
    function prepareURI($resource_uri,$args){
        $uri = $this->uri;
        // if $url not empty, add '/' as every object has uri which gets concatenated to previous uri
        $uri .= empty($uri)? "" : '/';
        $uri .= $resource_uri;
        //var_dump($uri);
        $uri .= $this->extractResourceName($args);
        return $uri;
    }

    function isSuccess() {

    }

    function extractBodyFromResponse() {
        return json_decode($this->response->getBody(),true);
    }
    function extractLinkHeaderFromResponse() {
        $link_header = $this->response->getHeader('Link');
        //var_dump($link_header);
        if( count($link_header)){
            return LinkHeaderParser::parse($link_header[0]);
        }
        return [];
    }

    /**
     * @return string base_url
     */
     function getBaseURL() {
         return static::$base_url;
     }

     function verifyNextStatePossibility($resource) {
         $body = $this->extractBodyFromResponse();

         $next_states = $this->possibleURLs($resource);

         foreach($next_states as  $state){
             if(array_key_exists($state,$body)){
                 $uri = $this->formatURL($body, $state);
                 return $uri;
             }
         }
         throw new InvalidResourceAccessException("Not possible to access the specified resource");
     }

     function possibleURLs($resource){
         $plural = Inflect::pluralize($resource). "_url";
         $singular = Inflect::singularize($resource) . "_url";
         return [$plural,$singular];
     }

    /**
     * @param $body
     * @param $state
     * @return mixed
     */
    public function formatURL($body, $state)
    {
        return trim(LinkHeaderParser::removeBraces($body[$state]),'/');
    }

    /**
     * @param $args
     * @param $uri
     * @return string
     */
    public function extractResourceName($args)
    {
        if (!is_null($args) && count($args)) {
             return  '/'.$args[0];
        }
        return '';
    }

    /**
     * @return bool
     */
    public function isResponseBodyArray()
    {
        /* Case 1:
           json file for  github.com/users
         * [
         *  {
         *  },
         *  {
         *  }
         * ]
         *
         * json_decode -- returns as an array of objects
         */
        /* Case 2:
         *
         * json file fir github.com/users/laravel
         *
         * {
         * }
         * json_decode -- returns as object
         */
        return is_array(json_decode($this->response->getBody()));
    }

}
