<?php

/**
 * Record Model
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_POS_Record_Model extends Settings_Vtiger_Record_Model
{

	public function getId()
	{
		return $this->get('id');
	}

	public function getName()
	{
		return $this->get('user_name');
	}

	public static function getInstanceById($recordId)
	{
		if (empty($recordId)) {
			return false;
		}
		$model = new self();
		$data = (new \App\Db\Query())->from('w_#__pos_users')->where(['id' => $recordId])->one();
		if ($data !== false) {
			$data['action'] = explode(',', $data['action']);
			$model->setData($data);
		}
		return $model;
	}
}
