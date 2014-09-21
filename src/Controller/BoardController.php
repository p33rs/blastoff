<?php
namespace p33rs\Blastoff\Controller;
use p33rs\Blastoff\Model\Board;
use p33rs\Blastoff\Model\Item;
use p33rs\Blastoff\Model\ItemList;
use p33rs\Blastoff\Response;
use p33rs\Blastoff\Storage\StorageInterface;

class BoardController
{

    const PW_LEN = 6;

    /**
     * Load a board's data.
     */
    public function load()
    {

        // get the ID from the postdata
        $id = !empty($_POST['id']) ? $_POST['id'] : false;
        if (!$id) {
            throw new Exception('No board ID provided');
        }

        $board = Board::load($id);
        if (!$board) {
            throw new Exception('That board doesn\'t exist.');
        }

        return $board->toArray();

    }

    public function create()
    {
        $board = !empty($_POST['board']) ? $_POST['board'] : false;
        if (!is_array($board)) {
            throw new Exception('Invalid board.');
        }

        $assembled = $this->assemble($board);
        $error = $assembled->validate();
        if ($error) {
            throw new Exception($error);
        }

        $password = !empty($_POST['password']) ? $_POST['password'] : false;
        $password = (string) $password;
        if (!$password) {
            throw new Exception('A password is required.');
        } elseif (strlen($password) < self::PW_LEN) {
            throw new Exception('Passwords must be more than 6 characters.');
        }

        $assembled->applyPassword($password)->save();

        return ['id' => $assembled->getId()];
    }

    public function update()
    {
        // get the ID from the postdata
        $id = !empty($_POST['id']) ? $_POST['id'] : false;
        if (!$id) {
            throw new Exception('No board ID provided');
        }
        // assemble the board
        $board = !empty($_POST['board']) ? $_POST['board'] : false;
        if (!is_array($board)) {
            throw new Exception('Invalid board.');
        }

        $assembled = $this->assemble($board);
        $error = $assembled->validate();
        if ($error) {
            throw new Exception($error);
        }

        $password = !empty($_POST['password']) ? $_POST['password'] : false;
        $assembled->save($password);

        return [];
    }

    public function assemble(array $board)
    {
        return new Board($board);
    }
    
}