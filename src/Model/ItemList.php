<?php
namespace p33rs\Blastoff\Model;

/**
 * Class ItemList
 * PHP reserves the "List" keyword so we gotta use this annoying classname.
 */
class ItemList extends AbstractModel {

    const MAX_STR_LEN = 1024;
    const MAX_LIST_LEN = 128;

    /** @var string */
    private $title;
    /** @var Item[] */
    private $items;

    protected function hydrate(array $data) {
        $this->setTitle(empty($data['title']) ? null : $data['title']);
        if (!empty($data['items']) && is_array($data['items'])) {
            $items = [];
            foreach ($data['items'] as $item) {
                $items[] = new Item($item);
            }
            $this->setItems($items);
        } else {
            $this->setItems([]);
        }
    }

    public function toArray() {
        $data = [
            'title' => $this->getTitle(),
            'items' => []
        ];
        $items = $this->getItems();
        foreach ($items as $item) {
            $data['items'][] = $item->toArray();
        }
        return $data;
    }

    public function validate() {
        if (!$this->title) {
            return 'No title was set.';
        }
        if (strlen($this->title) > self::MAX_STR_LEN) {
            return 'Title too long.';
        }
        if (count($this->getItems()) > self::MAX_LIST_LEN) {
            return 'Too many list items. Max is ' . self::MAX_LIST_LEN;
        }
        foreach ($this->getItems() as $item) {
            if (!($item instanceof Item)) {
                return 'An invalid item was provided.';
            } elseif ($error = $item->validate()) {
                return $error;
            }
        }
        return null;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items ? : [];
    }

    /**
     * @param Item[] $items
     * @throws Exception
     * @return this
     */
    public function setItems(array $items)
    {
        foreach ($items as $item) {
            if (!($item instanceof Item)) {
                throw new Exception('invalid item provided');
            }
        }
        $this->items = $items;
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