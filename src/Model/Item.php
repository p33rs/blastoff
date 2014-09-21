<?php
namespace p33rs\Blastoff\Model;

class Item extends AbstractModel {

    const MAX_STR_LEN = 1024;

    /** @var string */
    private $text;
    /** @var string|null */
    private $url;

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
        return [
            'text' => $this->getText(),
            'url' => $this->getUrl()
        ];
    }

    public function validate() {
        if (!$this->text) {
            return 'No text is set.';
        }
        if (strlen($this->getText()) > self::MAX_STR_LEN) {
            return 'text too long';
        }
        if (strlen($this->getUrl()) > self::MAX_STR_LEN) {
            return 'url too long';
        }
        return null;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text ?: '';
    }

    /**
     * @param string $text
     * @return this
     */
    public function setText($text)
    {
        $this->text = trim($text);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url ?: '';
    }

    /**
     * @param null|string $url
     * @return this
     */
    public function setUrl($url)
    {
        $this->url = trim($url);
        return $this;
    }

}