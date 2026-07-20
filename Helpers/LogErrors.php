<?php

/**
 * Funcion para instanciar la clase \Monolog\Logger para llevar el control de errores del sistema.
 * 
 */
function getLoggerSystem()
{

    try {
        $logger = new \Monolog\Logger(LOG_CHANNEL);
        $path = LOG_PATH . "/" . date('Ymd') . '.log';
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::DEBUG));
        $logger->pushHandler(new \Monolog\Handler\FirePHPHandler());
        return $logger;
    } catch (\Throwable $th) {
    }
}
