<?php
/**
 * SabreDav PDO Acl principal backend file.
 * This backend assumes all principals are in a single collection. The default collection
 * is 'principals/', but this can be overriden.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav\Backend;

use Sabre\DAVACL;

/**
 * SabreDav PDO Acl principal backend class.
 */
class AclPrincipal extends DAVACL\PrincipalBackend\PDO
{
	/**
	 * PDO table name for 'principals'.
	 *
	 * @var string
	 */
	public $tableName = 'dav_principals';

	/**
	 * PDO table name for 'group members'.
	 *
	 * @var string
	 */
	public $groupMembersTableName = 'dav_groupmembers';
}
