<?php

namespace HyperMedia;

use GuzzleHttp\Client;
use HyperMedia\Helpers\Inflect;

class GitHub implements \ArrayAccess
{
    use HttpClient;
    /**
     * either provide base_url or override this getBaseURL
     *
     * @return url (base url for you API server)
     */
    function getBaseURL()
    {
        return 'https://api.github.com';
    }

    /**
     * return array of user_name & password to avoid limit rating on API access if accessed without any authentication
     * ( ex. direct GITHUB limit is 60 req/hr) to avoid those provide user credentials
     *
     * format must be - ['user_name' ,'password']
     *
     * @return array|null
     */
    function authDetailsUserNameAndPWD(){
        // provide user name & password
        return [];
    }

    /**
     * next possible are set in pair (key ,value) -- key is name , value - absolute url
     * EX: on github response format would be like
     *  issues_url: "https://api.github.com/issues"
     *  user_url: "https://api.github.com/users/{user}"
     *  user_repositories_url: "https://api.github.com/users/{user}/repos{?type,page,per_page,sort}"
     *
     *    $github->users('user_name');  -- method name is "users" , but key is "user"
     *
     *    ourput : ['users_url','user_url']
     *
     *
     * @param  string (method name)
     * @return array
     */
    function possibleURLs($resource){
        $plural = Inflect::pluralize($resource). "_url";
        $singular = Inflect::singularize($resource) . "_url";
        return [$plural,$singular];
    }

}