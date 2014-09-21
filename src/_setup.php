<?php

  define('BO_CR_PREFIX', '$2a$10$');
  define('BO_CR_SUFFIX', '$');
  define('BO_PREFIX', 'blastoff');
  define('BO_PATH_ROOT', dirname(__DIR__));
  define('BO_PATH_BOARDS', BO_PATH_ROOT.'/boards');
  define('BO_PATH_INCLUDES', BO_PATH_ROOT.'/includes');
  define('BO_PATH_TPL', BO_PATH_ROOT.'/templates');
  
  function __autoload($class) {
    $path = BO_PATH_INCLUDES;
    $path .= str_replace('\\', '/', preg_replace('/^blastoff/', '', $class));
    $path .= '.php';
    if (!file_exists($path)) throw new Exception('A required file could not be located. ('.$path.')');
    require_once($path);
    if (!class_exists($class)) throw new Exception('A required class could not be loaded. ('.$class.')');
  }