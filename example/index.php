<?php


use Example\GitHub;

require_once  __DIR__.'/../vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$gitHub = new GitHub($client);
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
