<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/hello/:name', 'sayHello');

$app->run();

function sayHello($name) {
	echo '{"result": ' . json_encode($name) . '}';
}

?>