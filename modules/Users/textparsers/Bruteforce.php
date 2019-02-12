<?php

/**
 * Special function to display the last fails login.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Users_Bruteforce_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_BRUTAL_FORCE';

	/** @var mixed Parser type */
	public $type = 'mail';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '<table><tr><td>' . App\Language::translate('SINGLE_Users', 'Users') . '</td><td>' . App\Language::translate('LBL_IP', 'Settings:BruteForce') . '</td><td>' . App\Language::translate('LBL_DATE') . '</td><td>' . App\Language::translate('LBL_BROWSERS', 'Settings:BruteForce') . '</td></tr>';
		$configBruteForce = Settings_BruteForce_Module_Model::getBruteForceSettings();
		$attemptsLogin = (new \App\Db\Query())->select(['user_name', 'user_ip', 'login_time', 'browser'])->from('vtiger_loginhistory')
			->where([
					'and',
					['user_ip' => \App\RequestUtil::getRemoteIP(true)],
					['status' => 'Failed login'],
					['>=', 'login_time', (new DateTime())->modify("-{$configBruteForce['timelock']} minutes")->format('Y-m-d H:i:s')]
				])->all();
		foreach ($attemptsLogin as $data) {
			$html .= "<tr><td>{$data['user_name']}</td><td>{$this->displayIpAddress($data['user_ip'])}</td><td>{$data['login_time']}</td><td>{$data['browser']}</td></tr>";
		}
		return $html . '</table>';
	}

	/**
	 * Function to display ip address.
	 *
	 * @param string $ip
	 */
	private function displayIpAddress($ip)
	{
		$ipAddress = explode('.', $ip);
		unset($ipAddress[count($ipAddress) - 1]);

		return implode('.', $ipAddress) . '.xxx';
	}
}
