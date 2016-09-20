<?php

class Settings_Notifications_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Function to get Id of this record instance
	 * @return <Integer> Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record instance
	 * @return <String> Name
	 */
	public function getName()
	{
		return $this->get('name');
	}

	public function getEditUrl()
	{
		return '/index.php?parent=Settings&module=Notifications&view=CreateNotification&id=' . $this->getId();
	}

	static function getInstanceById($id)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM a_yf_notification_type WHERE id = ?', [$id]);
		$data = $db->getRow($result);
		return self::getInstanceFromArray($data);
	}

	static function getInstanceFromArray($array = [])
	{
		$model = new self();
		$model->setData($array);
		return $model;
	}
}
