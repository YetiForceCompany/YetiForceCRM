<?php
/**
 * Login history.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mriusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Login history.
 */
class Settings_LoginHistory_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Function to get the Id.
	 *
	 * @return <Number> Profile Id
	 */
	public function getId()
	{
		return $this->get('login_id');
	}

	/**
	 * Function to get the Profile Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('user_name');
	}

	public function getAccessibleUsers()
	{
		$usersListArray = [];
		$dataReader = (new \App\Db\Query())->select(['user_name'])
			->from('vtiger_users')
			->createCommand()->query();
		while ($userName = $dataReader->readColumn(0)) {
			$usersListArray[$userName] = $userName;
		}
		$dataReader->close();

		return $usersListArray;
	}

	/**
	 * Function to retieve display value for a field.
	 *
	 * @param string $fieldName - field name for which values need to get
	 * @param mixed  $recordId
	 *
	 * @return string
	 */
	public function getDisplayValue(string $fieldName, $recordId = false)
	{
		switch ($fieldName) {
			case 'login_time':
			case 'logout_time':
				$value = $this->get($fieldName);
				if ($value && '0000-00-00 00:00:00' !== $value) {
					return App\Fields\DateTime::formatToDisplay($value);
				}
					return '--';
			case 'user_name':
				return $this->getForHtml($fieldName);
			case 'status':
				return App\Language::translate($this->get($fieldName), 'Settings::Vtiger');
			default:
				return $this->get($fieldName);
		}
	}
}
