<?php
/**
 * Created by PhpStorm.
 * User: kamal
 * Date: 3/11/15
 * Time: 5:37 PM
 */

namespace HyperMedia;


use HyperMedia\Exceptions\InvalidResourceAccessException;

/**
 * This is to provide pagination support & index/array based access
 * Class Pagination
 * @package HyperMedia
 */
trait Pagination
{
    public $header_links = [];
    protected $collection = array();


    public function offsetExists($index) {
        return isset($this->collection[$index]);
    }

    public function offsetGet($index) {
        //var_dump($index);
        $this->autoPaginate($index);
        if($this->offsetExists($index)) {
            return $this->prepareObject($index);
        }
        return null;
    }

    public function offsetSet($index, $value) {
        if($index) {
            $this->collection[$index] = $value;
        } else {
            $this->collection[] = $value;
        }
        return true;

    }

    public function offsetUnset($index) {
        unset($this->collection[$index]);
        return true;
    }

    public function getContents() {
        return $this->collection;
    }

    /**
     * it moves to next page based on 'Link' header rel (first,prev,next,last)
     */
    public function autoPaginate($index){

        $current_size = count($this->collection);

        while($current_size < $index) {

            if($this->isNextPageAvailable()){
                return null;
            }
            // get link header
            //var_dump($this->header_links,$this->extractLinkHeaderFromResponse());
            $next_url = $this->header_links['next'];
            $next_url = $next_url[0]['uri'];
            // if next rel is present, then get next page

            // send request and store details
            /*$client = $this->createClientObject($next);
            var_dump($client->uri);
            $this->header_links = $client->header_links;
            $this->collection = array_merge($this->collection,$client->collection);*/

            $this->retrieveNextPage($next_url);
            $current_size = $this->collectionSize();
        }
        return $this->collection[$index];
    }
    protected function isNextPageAvailable(){
        return !(count($this->header_links) && isset($this->header_links['next']));
    }

    /**
     * @param $response
     */
    public function retrieveNextPage($url)
    {
        $this->next_uri = $url;
        $this->response = $this->sendRequest($url);
        $this->header_links = $this->extractLinkHeaderFromResponse();
        $this->collection = array_merge($this->collection, $this->extractBodyFromResponse());
    }

    /**
     * @return int
     */
    public function collectionSize()
    {
        return count($this->collection);
    }

    /**
     * @param $index
     * @return mixed
     */
    public function prepareObject($index)
    {
        //var_dump($index, count($this->extractBodyFromResponse()));
        //die;
        $item = $this->collection[$index];
        $client = $this->createClientObject($item['url']);//$this->sendRequest($item['url']);

        return $client;
    }


}