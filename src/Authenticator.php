<?php

namespace blastoff;
use \Exception;

class Authenticator {
  
  /**
   * @return a completely random salt for bluefish.
   */
  public function salt() {
    $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/.";
    $salt = '';
    for ($x = 0; $x < 21; $x++) {
      $salt .= $alphabet[mt_rand(0, 63)];
    }
    return $salt;
  }
  
  /**
   * Hash a password using the specified salt.
   *   Truncates salt info from result.
   * @param string $password The plaintext password to hash
   * @param string $salt The salt to hash with
   * @return string The hashed password, without prefix info
   */
  public function hashed($password, $salt) {
    if (!$password || !is_string($password)) throw new Exception ('You didn\'t provide a password.');
    if (!$salt || !is_string($salt)) throw new Exception ('You didn\'t provide a password salt.');
    return substr(crypt($password, (BO_CR_PREFIX.$salt.BO_CR_SUFFIX)), strlen(BO_CR_PREFIX.'.'.$salt));
  }
  
  /**
   * Check a hashed value against an unhashed string and salt.
   * @param string $input The unhashed string
   * @param string $hashed The hashed string, probably from db
   * @param string $salt The salt (presumably) used to make $hashed.
   * @return bool Whether the given $input and $salt result in $hashed
   */
  public function check($input, $stored, $salt) {
    if (!$input) throw new Exception('You didn\'t submit a password to check.');
    if (!$stored || !$salt) throw new Exception('There is no password info to check.');
    return $stored == $this->hashed($input, $salt);
  }
  
}