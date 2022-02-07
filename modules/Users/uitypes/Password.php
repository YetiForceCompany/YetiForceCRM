<?php
/**
 * User password uitype file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * User password uitype class.
 */
class Users_Password_UIType extends Vtiger_Password_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		$res = Settings_Password_Record_Model::checkPassword($value);
		if (false !== $res) {
			throw new \App\Exceptions\Security($res, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function convertToSave($value, Vtiger_Record_Model $recordModel)
	{
		return $recordModel->encryptPassword($value);
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		return '';
	}
}
