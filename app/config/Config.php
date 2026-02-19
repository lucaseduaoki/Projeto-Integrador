<?php

define('DEV_ENVIRONMENT', true);

if (DEV_ENVIRONMENT == true){
    ini_set('display_erros', 1);
    ini_set('display_startup_erros', 1);
    error_reporting(E_ALL);
}

define('APP_NAME', 'Projeto Integrador');