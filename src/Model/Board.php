<?php
namespace p33rs\Blastoff\Model;
use p33rs\Blastoff\Config;
use p33rs\Blastoff\Auth\AuthInterface;
use p33rs\Blastoff\Storage\StorageInterface;
use \Exception;
class Board extends AbstractModel {

    /** @var string */
    private $id;
    /** @var string */
    private $salt;
    /** @var string */
    private $hash;
    /** @var string */
    private $title;
    /** @var ItemList[] */
    private $itemLists;

    /** @var AuthInterface */
    private $auth;
    /** @var StorageInterface */
    private $storage;

    const CFG_STORAGE = 'storageAdapter';
    const CFG_STORAGE_INTERFACE = '\\p33rs\\Blastoff\\Storage\\StorageInterface';
    const CFG_AUTH = 'storageAdapter';
    const CFG_AUTH_INTERFACE = '\\p33rs\\Blastoff\\Auth\\AuthInterface';

    const MAX_LISTS = 64;
    const MAX_TITLE = 1024;

    public function __construct(array $data = []) {
        $authName = Config::get(self::CFG_AUTH);
        if (is_a($authName, self::CFG_AUTH_INTERFACE)) {
            $this->auth = new $authName;
        } else {
            throw new Exception('invalid storage adapter');
        }
        $storageName = Config::get(self::CFG_STORAGE);
        if (is_a($storageName, self::CFG_STORAGE_INTERFACE)) {
            $this->storage = new $storageName();
        } else {
            throw new Exception('invalid storage adapter');
        }
        parent::construct($data);
    }

    public function load($id) {
        $data = $this->storage->load($id);
        $this->hydrate($data);
        if (!$this->getId()) {
            throw new Exception('Loaded corrupt file.');
        }
    }

    /**
     * @returns this
     * @param string|null $password If this record has auth data, a password is required for changes
     * @throws Exception on validation fail
     * @see Board::hasAuth
     */
    public function save($password = null) {
        if ($error = $this->validate()) {
            throw new Exception($error);
        }
        if ($this->hasAuth()) {
            $this->auth->check($password, $this->getHash(), $this->getSalt());
            $this->storage->update($this->getId(), $this->toArray());
        } else {
            $id = $this->storage->create($this->toArray());
            $this->setId($id);
        }
        return $this;
    }

    /**
     * Get validation errors.
     * If everything is copacetic, returns null.
     * @return null|string
     */
    public function validate() {
        if (!$this->hash || !$this->salt) {
            return 'No password is stored.';
        }
        if (!$this->title) {
            return 'The board has no title.';
        }
        if (strlen($this->title) > self::MAX_TITLE) {
            return 'Title too long.';
        }
        $lists = $this->getItemLists();
        if (count($lists) > self::MAX_LISTS) {
            return 'Too many lists. Max is ' . self::MAX_LISTS;
        }
        foreach ($lists as $list) {
            if (!($list instanceof ItemList)) {
                return 'Invalid list was provided.';
            } elseif ($error = $list->validate()) {
                return $error;
            }
        }
        return null;
    }

    /**
     * Load an array into here.
     * @param $data
     * @throws Exception
     */
    protected function hydrate(array $data) {
        $this->setId(empty($data['id']) ? null : $data['id']);
        $this->setTitle(empty($data['title']) ? null : $data['title']);
        if (!empty($data['itemLists']) && is_array($data['itemLists'])) {
            $lists = [];
            foreach ($data['itemLists'] as $list) {
                $lists[] = new ItemList($list);
            }
            $this->setItemLists($lists);
        } else {
            $this->setItemLists([]);
        }
        if (!empty($data['hash']) && !empty($data['salt'])) {
            $this->setHash($data['hash']);
            $this->setSalt($data['salt']);
        }
    }

    /**
     * @return array
     */
    public function toArray() {
        $data = [
            'id' => $this->getId(),
            'hash' => $this->getHash(),
            'salt' => $this->getSalt(),
            'title' => $this->getTitle(),
            'lists' => []
        ];
        $lists = $this->getItemLists();
        foreach($lists as $list) {
            $data['lists'][] = $list->toArray();
        }
        return $data;
    }

    /**
     * Do we need a password to resave this thing?
     * @return bool
     */
    public function hasAuth() {
        return !!$this->getId();
    }

    /**
     * @param $password
     * @return this
     */
    public function applyPassword($password) {
        $salt = $this->auth->salt();
        $hashed = $this->auth->hashed($password, $salt);
        $this->setSalt($salt)->setHash($hashed);
        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return this
     */
    private function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     * @return this
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return ItemList[]
     */
    public function getItemLists()
    {
        return $this->itemLists ? : [];
    }

    /**
     * @param ItemList[] $itemLists
     * @throws Exception
     * @return this
     */
    public function setItemLists(array $itemLists)
    {
        foreach ($itemLists as $list) {
            if (!($list instanceof ItemList)) {
                throw new Exception('invalid itemlist provided');
            }
        }
        $this->itemLists = $itemLists;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return this
     */
    public function setTitle($title)
    {
        $this->title = trim($title);
        return $this;
    }



}