<?php

namespace Tests;


use GuzzleHttp\Client;
use HyperMedia\GitHub;

class GitHubTest extends \PHPUnit_Framework_TestCase
{
    protected $prophet;
    /**
     * @const string
     */
    const BASEURL = 'https://api.github.com/';

    /**
     * @var GuzzleHttp\Message\Response
     */
    private $response;

    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * Set up - runs before each test
     */
    protected function setUp()
    {

        //$this->prepareMockedGuzzleClient();
        //$this->prepareMockedGuzzleResponse();
        $this->clientException = $this->getMockBuilder('\GuzzleHttp\Exception\ClientException')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Tear down - runs after each test
     */
    protected function tearDown()
    {
        unset($this->client);
        unset($this->token);
        unset($this->response);
        unset($this->clientException);
    }

    /**
     * Test that true does in fact equal true
     */
    public function testTrueIsTrue()

    {
        $url = self::BASEURL . 'users';
        $client = $this->prepareMockedGuzzleClient($url);
        $gitHub = new GitHub($client);
        //$gitHub->setClient($client);
        $users = $gitHub->users();
    }
    private function prepareMockedGuzzleClient($url)
    {
        // mock Client
        $this->client = $this->getMock('\GuzzleHttp\Client', ['request']);

        // set Promises & Predictions
        $this->client->expects($this->once())
                ->method('request')
                ->with('get',$url)
                ->will($this->returnValue($this->prepareMockedGuzzleResponse()));
        return $this->client;
    }

    private function prepareMockedGuzzleResponse(){

        /*$this->getMockBuilder('\GuzzleHttp\Message\Response')
           ->disableOriginalConstructor()
           ->setMethods( ['getBody','getHeader'])
           ->getMock();*/

        // Mock Response
        $this->response = $this->getMock('\GuzzleHttp\Message\Response',['getBody','getHeader']);

        // set Promises & Predictions
        $this->response->expects($this->once())
            ->method('getHeader')
            ->with('Link')->will($this->returnValue(array()));
        $this->response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue( json_encode(['OOOOOOOOOOOOOOOOOOPS'])));

        return $this->response;
    }
}
