<?php
return array(
    'colors' => true,
    'databases' => array(
        'master' => array(
            'dsn'      => 'mysql:dbname=api;host=localhost',
            'user'     => 'apiuser',
            'password' => 'apipw',
        ),
        'slave' => array(
            // PDO Connection settings.
            'dsn'      => 'mysql:dbname=api;host=localhost',
            'user'     => 'apiuser',
            'password' => 'apipw',
        ),
    ),
);
