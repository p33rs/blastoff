<?php
namespace p33rs\Blastoff\Model;
abstract class AbstractModel {
    /**
     * @param array $data data to preload with
     */
    public function __construct(array $data = []) {
        if ($data) {
            $this->hydrate($data);
        }
    }
    /** @return array Array containing only json_encode-ready types */
    abstract public function toArray();
    /** @return null|string string if error, null if valid */
    abstract public function validate();
    /**
     * @param array $data
     * @return void
     */
    abstract protected function hydrate(array $data);
}