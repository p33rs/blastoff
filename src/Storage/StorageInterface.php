<?php
namespace p33rs\Blastoff\Storage;
interface StorageInterface
{

    const CFG_STORAGE_URL = 'storageUrl';
    const CFG_STORAGE_PASS = 'storagePass';
    const CFG_STORAGE_NAME = 'storageName';
    const CFG_STORAGE_USER = 'storageUser';
    const CFG_STORAGE_PORT = 'storagePort';

    /**
     * @param $id
     * @return array
     */
    function load($id);

    /**
     * Create a new record.
     * If an ID key exists in $data, it should be ignored.
     * @param array $data
     * @return string The ID of the new record.
     */
    function create(array $data);

    /**
     * Update existing record.
     * If the ID key exists in $data, it should be ignored.
     * @param $id
     * @param array $data
     * @return this
     */
    function update($id, array $data);

}