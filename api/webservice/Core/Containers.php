<?php
/**
 * API containers file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core;

/**
 * API containers class.
 */
class Containers
{
	/** @var string[] List of available API containers */
	public static $list = [
		'RestApi', 'Portal', 'ManageConsents'
	];

	/** @var string[] List of GUI tabs */
	public static $listTab = [
		'RestApi', 'Portal', 'ManageConsents'
	];
	/** @var array List of db tables */
	public static $listTables = [
		'RestApi' => [
			'user' => 'w_#__api_user',
			'session' => 'w_#__api_session',
		],
		'Portal' => [
			'user' => 'w_#__portal_user',
			'session' => 'w_#__portal_session',
		]
	];
}
