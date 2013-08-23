<?php

ini_set('memory_limit','1000M');

include(__DIR__.'../../vendor/autoload.php');

$app = new Application(
	"NlpTools part of speech tagger",
	"0.1",
	"Angelos Katharopoulos"
);
$app->add(new Commands\Tag());
$app->add(new Commands\Features());
$app->add(new Commands\Evaluate());
$app->add(new Commands\Train());
$app->run();
