<?php

function db()
{
    $dsn = 'mysql:host=127.0.0.1;dbname=elcolectivo;charset=utf8';

    $connection = new PDO($dsn, 'root', '');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $connection;
}
