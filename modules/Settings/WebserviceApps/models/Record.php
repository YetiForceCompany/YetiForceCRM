<?php

/**
 * Record Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Length key.
	 */
	const KEY_LENGTH = 32;

	/**
	 * Function to get id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Return name server.
	 *
	 * @return type
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Functtion to get instance without data.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Static function to get model.
	 *
	 * @param int $recordId
	 *
	 * @return bool|\self
	 */
	public static function getInstanceById($recordId)
	{
		if (empty($recordId)) {
			return false;
		}
		$model = self::getCleanInstance();
		$data = (new \App\Db\Query())->from('w_#__servers')
			->where(['id' => $recordId])
			->one(\App\Db::getInstance('webservice'));
		if ($data) {
			$model->setData($data);
		}
		return $model;
	}

	/**
	 * Delete the record.
	 */
	public function delete()
	{
		\App\Db::getInstance('webservice')->createCommand()
			->delete('w_#__servers', ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Save record.
	 */
	public function save()
	{
		$db = \App\Db::getInstance('webservice');
		$data = [
			'status' => $this->get('status') ? 1 : 0,
			'name' => $this->get('name'),
			'acceptable_url' => $this->get('acceptable_url'),
			'pass' => App\Encryption::getInstance()->encrypt($this->get('pass')),
			'accounts_id' => $this->get('accounts_id'),
		];
		if ($this->isEmpty('id')) {
			$data['type'] = $this->get('type');
			$data['api_key'] = App\Encryption::getInstance()->encrypt(\App\Encryption::generatePassword(self::KEY_LENGTH));
			$db->createCommand()->insert('w_#__servers', $data)->execute();
			$this->set('id', $db->getLastInsertID('w_#__servers_id_seq'));
		} else {
			$db->createCommand()->update('w_#__servers', $data, ['id' => $this->getId()])->execute();
		}
	}
}
