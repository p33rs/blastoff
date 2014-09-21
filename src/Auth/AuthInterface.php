<?php
namespace p33rs\Blastoff\Auth;
interface AuthInterface {
    /**
     * Generate a random salt.
     */
    public function salt();

    /**
     * Hash a password using the given salt.
     * @param $password
     * @param $salt
     * @return mixed
     */
    public function hashed($password, $salt);

    /**
     * Compare a given password and salt to a pre-hashed value.
     * @param $input
     * @param $stored
     * @param $salt
     * @return mixed
     */
    public function check($input, $stored, $salt);
}