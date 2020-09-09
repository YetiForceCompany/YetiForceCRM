<?php

/**
 * MailClient module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class Settings_MailClient_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 's_#__mail_client';
	public $baseIndex = 'id';
	public $listFields = ['default_host' => 'LBL_IMAP_SERVER', 'default_port' => 'LBL_PORT_CONNECT_IMAP', 'smtp_server' => 'LBL_SMTP_SERVER', 'smtp_port' => 'LBL_SMTP_PORT'];
	public $name = 'MailClient';

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=MailClient&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string URL
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=MailClient&parent=Settings&view=Edit';
	}
}
