<?php

namespace blastoff;
use \Exception;

/**
 * Controller for AJAX commands.
 * All methods should operate in public scope and 
 *   return bool to indicate whether no errors occurred.
 */

class BoardController {

  public $data = array();
  public $messages = array();
  
  /**
   * Load a board's data.
   */
  public function load() {
  
    // get the ID from the postdata
    $id = !empty($_POST['id']) ? $_POST['id'] : false;
    if (!$id) {
      $this->messages[] = 'You did not say which board to load.';
      return false;
    }
    
    // if the board was loaded, return it.
    if (!$data = $this->_read($id)) {
      $this->messages[] = 'Your board was not found.';
    }
    elseif (!$data = json_decode($data, true)) {
      $this->messages[] = 'The board\'s save file is corrupt.';
    }
    else {
      $this->data = array(
        'lists' => $data['lists'],
        'title' => $data['title'],
      );
    }
    
    return !!$data;
    
  } // end load()
  
  // create a new board
  // set data to the new board's ID, or 0 if fail
  // requires: lists, password
  public function create() {
  
    // pull the data
    $lists = !empty($_POST['lists']) ? $_POST['lists'] : false;
    $password = !empty($_POST['password']) ? $_POST['password'] : false;
    $title = !empty($_POST['title']) ? $_POST['title'] : false;
    
    // check the password
    $hash = $salt = false;
    if (!is_string($password) || strlen($password) < 6 || strlen($password) > 64) {
      $this->messages[] = 'Passwords must be between 6 and 64 characters.';
    }
    // create the auth info
    else {
      $auth = new Authenticator();
      try {
        $salt = $auth->salt();
        $hash = $auth->hashed($password, $salt);
      }
      catch (Exception $e) { 
        $this->messages[] = $e->getMessage();
      }
    }
    
    // return false if there was a password problem,
    //   or if the link list wasn't valid.
    if ($errors = $this->_validateListSet($lists)) {
      $this->messages = array_merge($this->messages, $errors);
      return false;
    }
    elseif (!$hash || !$salt) return false;
    
    // check the title
    if (!$title) {
      $this->messages = 'You didn\'t enter a board title.';
      return false;
    }
    elseif (strlen($title) > 255) {
      $this->messages = 'Your title must be less than 256 characters.';
      return false;
    }
    
    $id = $this->data = $this->_nextId();
    $lists = $this->_scrubUrls($lists);
    
    // save the new board
    return $this->_write($id, compact('lists', 'hash', 'salt', 'title'));

  } // end create()
  
  // save an existing board
  public function update() {
  
    // pick up the input
    $lists = !empty($_POST['lists']) ? $_POST['lists'] : false;
    $id = empty($_POST['id']) ? false : $_POST['id'];
    $password = !empty($_POST['password']) ? $_POST['password'] : false;
    $title = !empty($_POST['title']) ? $_POST['title'] : false;
    
    $auth = new Authenticator();
    // attempt to read the existing board
    if (!$id || (!$board = $this->_read($id))) {
      $this->messages[] = 'Your board could not be found.';
      return false;
    }
    elseif (!$board = json_decode($board, true)) {
      $this->messages[] = 'The board\'s save file is corrupt.';
    }
    // check the provided credentials
    elseif (!$auth->check($password, $board['hash'], $board['salt'])) {
      $this->messages[] = 'You provided an incorrect password.';
      return false;
    }
    // validate the new lists
    elseif ($errors = $this->_validateListSet($lists)) {
      $this->messages = array_merge($this->messages, $errors);
      return false;
    }
    
    // check the title
    if (!$title) {
      $this->messages = 'You didn\'t enter a board title.';
      return false;
    }
    elseif (strlen($title) > 255) {
      $this->messages = 'Your title must be less than 256 characters.';
      return false;
    }
    
    // resave the board
    $data = array (
      'lists' => $lists,
      'salt' => $board['salt'],
      'hash' => $board['hash'],
      'title' => $title,
    );
    
    return $this->_write($id, $data);
  
  } // end update()
  
  /**
   * Validate the structure of a board's list arrays.
   * Also validates individual list arrays.
   * @param array $lists An array of lists from a board.
   * @return array A list of errors.
   */
  private function _validateListSet($lists) {
    $errors = array();
    // are the lists valid?
    if (!is_array($lists) || !$lists) {
      $errors[] = 'No lists were provided.';
    }
    else {
      foreach($lists as $list) {
        $listName = 'An unnamed list ';
        if (isset($list['title']) && trim((string) $list['title'])) {
          $listName = 'A list called ' . $list['title'] . ' ' ;
        }
        $listErrors = $this->_validateList($list);
        if ($listErrors) {
          $errors[] = $listName . 'had the following errors: ' . implode(', ', $listErrors);
        }
      }
    }
    return $errors;
  } // end _validateBoard()
  
  /**
   * Validate the individual list arrays.
   * @param array $lists An array of items from a list.
   * @return array A list of errors.
   */
  private function _validateList($list) {
    $errors = array();
    
    // quit on no list
    if (
      !is_array($list) || 
      !isset($list['items']) ||
      !is_array($list['items'])
    ) {
      $errors[] = 'The list is empty.';
    }
    else {
      // check each list item
      foreach ($list['items'] as $item) {
        // is there text?
        if (!isset($item['text']) || !is_string($item['text']) || !trim($item['text'])) {
          $errors[] = 'There was an unnamed list item.';
        }
      }
    }
    
    // fail on no title
    if (!isset($list['title']) || !is_string($list['title']) || !trim($list['title'])) {
      $errors[] = 'The list does not have a valid title.';
    }
    
    return $errors;
  } // end _validateList()
  
  /**
   * Write the given data to the given board file
   * @param string $id The ID of the board to save.
   * @param array $data The data to save into the board.
   * @return bool was the save successful?
   */
  private function _write($id, $data) {
    $path = BO_PATH_BOARDS.'/'.$id.'.json';
    return !!file_put_contents($path, json_encode($data));
  } // end _write()
  
  /**
   * Load the given board.
   * @param string $id The ID of the board to read.
   * @return mixed The board contents, or false.
   */
  private function _read($id) {
    $path = BO_PATH_BOARDS.'/'.$id.'.json';
    if (!file_exists($path)) {
      return false;
    }
    $json = file_get_contents($path);
    $json = str_replace("\n", '', $json);
    return $json;
  } // end _read()
  
  /**
   * Go through the URLs in our well-formed list array
   *   and remove any quotation marks or angle brackets.
   * @param array $lists An array of lists from a board.
   * @return void
   */
  private function _scrubUrls($lists) {
    foreach ($lists as $listsKey => $list) {
      foreach ($list['items'] as $itemsKey => $item) {
        if (empty($item['url'])) continue;
        $lists[$listsKey][$itemsKey]['url'] = $this->_lazyEscape($item['url']);
      }
    }
    return $lists;
  } // end _scrubUrls()
  
  /**
   * Escape a parameter before committing it to JSON.
   * @param string $s String to strip <>" from
   * @return string The escaped string
   */
  private function _lazyEscape($string) {
    $string = str_replace('"', '%22', $string);
    $string = str_replace('<', '%3c', $string);
    $string = str_replace('>', '%3e', $string);
    return $string;
  } // end _lazyEscape
  
  /**
   * generate the id of the next board
   * @return string the name of the next 
   *   board that will be saved.
   */
  private function _nextId() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyz';
    $string = '';
    for ($x = 0; $x < 6; $x++) {
      $string .= $alphabet[mt_rand(0, 25)];
    }
    $number = count(glob(BO_PATH_BOARDS.'/*.json')) + 1;
    return $number . '-' . $string;
  } // end _nextId()
  
}