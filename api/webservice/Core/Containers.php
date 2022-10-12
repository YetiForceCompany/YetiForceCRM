<?php
/**
 * API containers file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	const LIST = [
		'WebserviceStandard', 'WebservicePremium', 'ManageConsents', 'SMS', 'Token', 'PBX', 'OAuth'
	];

	/** @var string[] List of GUI tabs */
	const LIST_TAB = [
		'WebserviceStandard', 'WebservicePremium', 'ManageConsents', 'SMS',
	];

	/** @var array List of db tables */
	const LIST_TABLES = [
		'WebserviceStandard' => [
			'user' => 'w_#__api_user',
			'session' => 'w_#__api_session',
			'loginHistory' => 'l_#__api_login_history',
		],
		'WebservicePremium' => [
			'user' => 'w_#__portal_user',
			'session' => 'w_#__portal_session',
			'loginHistory' => 'l_#__portal_login_history',
		],
		'ManageConsents' => [
			'user' => 'w_#__manage_consents_user',
		],
		'SMS' => [
			'user' => 'w_#__sms_user',
		],
	];

	/** @var array List of container configuration fields */
	const CONFIG_FIELDS = [
		'SMS' => ['name' => 'M', 'status' => 'M', 'type' => 'M', 'ips' => 'M'],
		'PBX' => ['name' => 'M', 'status' => 'M', 'type' => 'M', 'ips' => 'O'],
		'Token' => ['name' => 'M', 'status' => 'M', 'type' => 'M', 'ips' => 'O', 'url' => 'O'],
		'OAuth' => ['name' => 'M', 'status' => 'M', 'type' => 'M', 'ips' => 'O'],
	];
}
