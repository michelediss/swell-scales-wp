<?php

function scssphp_autoload($class) {
    $prefix = 'ScssPhp\\ScssPhp\\';
    $base_dir = __DIR__ . '/scssphp/src/';

    // Verifica se la classe utilizza il namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, passa al prossimo autoloader registrato
        return;
    }

    // Ottieni il nome relativo della classe
    $relative_class = substr($class, $len);

    // Sostituisci il namespace prefix con la base directory, sostituisci i namespace
    // separatori con i separatori di directory nei relativi nomi di classe, appendi
    // con .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Se il file esiste, richiedilo
    if (file_exists($file)) {
        require $file;
    }
}

spl_autoload_register('scssphp_autoload');
