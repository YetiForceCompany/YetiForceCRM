<?php
/**
 * Internal client mail composer driver file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\Composers;

/**
 * Internal client mail composer driver class.
 */
class InternalClient extends Base
{
	/** {@inheritdoc} */
	const NAME = 'LBL_INTERNAL_CLIENT';

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return \App\Privilege::isPermitted('OSSMail') && file_exists(ROOT_DIRECTORY . '/public_html/modules/OSSMail/roundcube/');
	}

	/** {@inheritdoc} */
	public function sendMail(\App\Request $request): array
	{
		if ($request->isEmpty('record')) {
			return [
				'popup' => \OSSMail_Module_Model::getComposeParameters()['popup'],
				'url' => 'index.php?module=OSSMail&view=Compose&to=' . $request->get('email'),
			];
		}
		$record = $request->getInteger('record');
		if (!\App\Record::isExists($record)) {
			return [
				'popup' => \OSSMail_Module_Model::getComposeParameters()['popup'],
				'url' => 'index.php?module=OSSMail&view=Compose&to=' . $request->get('email'),
			];
		}
		$moduleName = \App\Record::getType($record);
		$url = \OSSMail_Module_Model::getComposeUrl($moduleName, $record, $request->getByType('view'), $request->getByType('type'));
		if ($to = $request->get('email')) {
			$url .= '&to=' . $to;
		}
		return [
			'status' => true,
			'url' => $url,
			'popup' => \OSSMail_Module_Model::getComposeParameters()['popup'],
		];
	}
}
