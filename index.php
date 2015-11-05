<?php
require_once './vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$gitHub = new \HyperMedia\GitHub($client);
//var_dump($gitHub->extractBodyFromResponse());
$user = $gitHub->users('laravel');
//var_dump($user->extractBodyFromResponse());

$repos = $user->repos();
//var_dump($repos->extractBodyFromResponse());
$repo = $repos[5];
//var_dump($repo->extractBodyFromResponse());

$issues = $repo->issues();
$tags = $repo->tags();
$issue = $issues[0];
echo $issue->title. "\n";
echo $issue->body ."\n";
//var_dump($issues->header_links);
$issue = $issues[50];
echo $issue->title. "\n";
echo $issue->body ."\n";
//var_dump($issue->title,$issue->body);
//var_dump($issue->title,$issue->body);
//var_dump($tags->extractBodyFromResponse(),$tags->uri);
//var_dump($repo->response->getHeader('X-RateLimit-Limit'));

//var_dump($repo->extractBodyFromResponse());
