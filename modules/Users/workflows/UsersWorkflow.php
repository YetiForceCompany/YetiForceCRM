<?php

/**
 * UsersWorkflow.
 *
 * @package   Workflow
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class UsersWorkflow
{
	/**
	 * Send email after creating a new user.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function newUser(Vtiger_Record_Model $recordModel)
	{
		\App\Mailer::sendFromTemplate([
			'template' => 'NewUser',
			'moduleName' => $recordModel->getModuleName(),
			'recordId' => $recordModel->getId(),
			'to' => $recordModel->get('email1'),
			'password' => $recordModel->get('user_password'),
		]);
	}
}
