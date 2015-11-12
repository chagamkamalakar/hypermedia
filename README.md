# <a href="http://apievangelist.com/2014/01/07/what-is-a-hypermedia-api" target="_blank">HyperMedia </a> Client

The main goal of this project is to provide fluent client interface to access REST Server API complying <a href="https://en.wikipedia.org/wiki/HATEOAS" target="_blank"> HATEOS.</a>

## Install

Via Composer
    composer require chagamkamalakar/hypermedia="dev-master@dev"

## how to use it:

the main part is HttpClient trait. which has 3 abstract methods

abstract function getBaseURL();
abstract function authDetailsUserNameAndPWD();
abstract function possibleURLs($resource);

In order to make use of this things
 1) must implement above 3 methods
 2) your class must implement ArrayAccess interface to provide pagination support ( no need to implement any method,
 all these methods are implemented by Pagination trait)

# abstract function getBaseURL();
    return the base uri for API Server
# abstract function authDetailsUserNameAndPWD()
    If limit rating is there on API ( for Github the limit is 60 req/hr without any authentication)
    provide credentials (user_name & password) respectively in array format ['user_name','password'].

# abstract function possibleURLs($resource);
    see the GitHub example file code there is description is given with an example for Accessing Github api


To understand how to use it see the GitHub file.it's an example to access GitHub data

Pagination is supported in ArrayLike access

$client = new \GuzzleHttp\Client();
$gitHub = new \HyperMedia\GitHub($client);
$user = $gitHub->users('laravel');

$repos = $user->repos();
$repo = $repos[5];


$issues = $repo->issues();
$tags = $repo->tags();
$issue = $issues[0];
echo $issue->title. "\n";
echo $issue->body ."\n";

$issue = $issues[50];
echo $issue->title. "\n";
echo $issue->body ."\n";









