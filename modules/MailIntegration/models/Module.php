<?php
/**
 * MailIntegration module model class.
 *
 * @package   Module
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class MailIntegration_Module_Model extends Vtiger_Module_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getSettingLinks(): array
	{
		return [
			[
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_DOWNLOAD_OUTLOOK_INSTALLATION_FILE',
				'linkurl' => 'index.php?parent=Settings&module=MailIntegration&action=Download&mode=outlook',
				'linkicon' => 'fab fa-windows',
			]
		];
	}
}
