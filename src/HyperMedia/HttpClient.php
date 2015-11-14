<?php

namespace HyperMedia;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use HyperMedia\Exceptions\ForBiddenException;
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
        // load the main/home page
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
            $this->response = $this->http_client->get($url,['auth' => $this->authDetailsUserNameAndPWD()]);
        }catch (ClientException $ce){
            // if request has response code - 403, then throw exception
            $this->isRequestForBidden($ce);

            // if some other exception
            throw new \Exception("Unknown problem happened,please debug it");
        }
        return $this->response;
    }


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

        if(!is_null($this->response)) {

            $status_code = $this->response->getStatusCode();

            if ($status_code >= 200 && $status_code <= 299) {
                return true;
            } else if($status_code == 401 || $status_code == 403){
                $body = $this->extractBodyFromResponse();
                throw new ForBiddenException($body['message']);
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
        if(!is_null($this->response)) {
            return json_decode($this->response->getBody(), true);
        }
        return "";
    }
    function extractLinkHeaderFromResponse() {
        $link_header = $this->response->getHeader('Link');
        //var_dump($link_header);
        if( count($link_header)){
            return LinkHeaderParser::parse($link_header[0]);
        }
        return [];
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
     * Case 1:
     *   json file for  github.com/users
     * [
     *  {
     *  },
     *  {
     *  }
     * ]
     *
     * json_decode -- returns as an array of objects
     *
     * Case 2:
     *
     * json file fir github.com/users/laravel
     *
     * {
     * }
     * json_decode -- returns as object
     *
     * @return bool
     */
    public function isResponseBodyArray()
    {
        return is_array(json_decode($this->response->getBody()));
    }


    abstract function authDetailsUserNameAndPWD();
    abstract function getBaseURL();
    abstract function possibleURLs($resource);


    /**
     * this is called when exception is thrown on sending request to API Server
     * @param ClientException $ce
     * @throws \Exception
     */
    public function isRequestForBidden(ClientException $ce)
    {
        $this->response = $ce->getResponse();
        $this->validateLastRequestStatus();

    }

}
