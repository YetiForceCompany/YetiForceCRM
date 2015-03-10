<?php
namespace Yeti;
use Sabre\DAVACL;

class DAVACL_PrincipalBackend_PDO extends DAVACL\PrincipalBackend\PDO {
    /**
     * Sets up the backend.
     *
     * @param PDO $pdo
     * @param string $tableName
     * @param string $groupMembersTableName
     * @deprecated We are removing the tableName arguments in a future version
     *             of sabredav. Use the public properties instead.
     */
    function __construct(\PDO $pdo, $tableName = 'dav_principals', $groupMembersTableName = 'dav_groupmembers') {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->groupMembersTableName = $groupMembersTableName;
    }
}
