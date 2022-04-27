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
	public static $list = [
		'WebserviceStandard', 'WebservicePremium', 'ManageConsents', 'SMS',
	];

	/** @var string[] List of GUI tabs */
	public static $listTab = [
		'WebserviceStandard', 'WebservicePremium', 'ManageConsents', 'SMS',
	];

	/** @var array List of db tables */
	public static $listTables = [
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
}
