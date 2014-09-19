<?php

  include ('includes/_setup.php');
  
  $id = !empty($_GET['board']) ? $_GET['board'] : '0';
  
  include(BO_PATH_TPL.'/index.tpl');
  