<?php
/**
 * Created by PhpStorm.
 * User: kamal
 * Date: 5/11/15
 * Time: 7:49 AM
 */

$user = require_once __DIR__.'/Fixtures/1_user_resource.php';
$content = $user['body'];
var_dump($content);