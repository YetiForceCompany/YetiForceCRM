<?php
/**
 * API containers file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
			'loginHistory' => 'l_#__api_login_history',
		],
		'Portal' => [
			'user' => 'w_#__portal_user',
			'session' => 'w_#__portal_session',
			'loginHistory' => 'l_#__portal_login_history',
		],
		'ManageConsents' => [
			'user' => 'w_#__manage_consents_user'
		]
	];
}
