<?php

ini_set('memory_limit','1000M');

include(__DIR__.'../../vendor/autoload.php');

use Symfony\Component\Console\Application;

$app = new Application("NlpTools part of speech tagger", "0.1");
$app->add(new Commands\Tag());
$app->add(new Commands\Features());
$app->add(new Commands\Evaluate());
$app->run();
