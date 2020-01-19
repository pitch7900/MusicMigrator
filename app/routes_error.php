<?php

$app->post('/configuration/writesetup','ErrorController:postWriteConfig')
            ->setName('writeconfig');

$app->get('[{path:.*}]', 'ErrorController:getConfigError')
            ->setName('configerror');