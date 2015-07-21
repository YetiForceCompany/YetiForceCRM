<?php
namespace Yeti;
use Sabre\DAV;

class DAV_Auth_Backend_PDO extends DAV\Auth\Backend\PDO {
    /**
     * Creates the backend object.
     *
     * If the filename argument is passed in, it will parse out the specified file fist.
     *
     * @param PDO $pdo
     * @param string $tableName The PDO table name to use
     * @deprecated The tableName argument will be removed from a future version
     *             of sabredav. Use the public property instead.
     */
    function __construct(\PDO $pdo, $tableName = 'dav_users') {

        $this->pdo = $pdo;
        $this->tableName = $tableName;

    }
}
