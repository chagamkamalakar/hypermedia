<?php

namespace HyperMedia;

use GuzzleHttp\Client;

class GitHub implements \ArrayAccess
{
    use HttpClient;
    /*
     * either provide base_url or override this getBaseURL
     */
    function getBaseURL()
    {
        return 'https://api.github.com';
    }

    function authDetailsUserNameAndPWD(){
        // provide user name & password
        return ['', ''];
    }

}