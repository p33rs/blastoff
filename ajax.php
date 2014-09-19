<?php

  include ('includes/_setup.php');
  
  // prepare the result
  $result = array(
    'data' => array(),
    'success' => false,
    'messages' => array(),
  );
  
  set_error_handler (function($number, $string, $f, $l, $c) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    $result['messages'][] = $string;
    echo json_encode($result);
  });
  
  // create a controller
  $controller = new blastoff\BoardController();
  
  // get the command
  $command = empty($_POST['command']) ? null : $_POST['command'];
  
  if (!$command || !in_array($command, get_class_methods($controller))) {
    $result['messages'] = 'Your request could not be understood.';
  }
  else {
    $result['success'] = $controller->$command();
    $result['data'] = $controller->data;
    $result['messages'] = $controller->messages;
  }
  
  echo json_encode($result);