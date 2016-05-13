<?php namespace Yeti;

use Sabre\DAVACL;

/**
 * PDO principal backend
 *
 *
 * This backend assumes all principals are in a single collection. The default collection
 * is 'principals/', but this can be overriden.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class DAVACL_PrincipalBackend_PDO extends DAVACL\PrincipalBackend\PDO
{

	/**
	 * PDO table name for 'principals'
	 *
	 * @var string
	 */
	public $tableName = 'dav_principals';

	/**
	 * PDO table name for 'group members'
	 *
	 * @var string
	 */
	public $groupMembersTableName = 'dav_groupmembers';

}
