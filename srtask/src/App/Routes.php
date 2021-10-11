<?php

declare(strict_types=1);

$app->get('/secret/{hash}', 'App\Controller\Home:getSecret');
$app->post('/secret', 'App\Controller\Home:postSecret');
