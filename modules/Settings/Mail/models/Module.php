<?php

/**
 * Mail module model class
 * @package YetiForce.Settings.Module
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_Module_Model extends Settings_Vtiger_Module_Model
{

	public $baseTable = 's_#__mail_queue';
	public $baseIndex = 'id';
	public $listFields = ['smtp_id' => 'LBL_SMTP_NAME', 'date' => 'LBL_DATE', 'owner' => 'LBL_CREATED_BY' , 'subject' => 'LBL_SUBJECT', 'status' => 'LBL_STATUS', 'priority' => 'LBL_PRIORITY'];
	public $name = 'Mail';
	public $filterFields = ['smtp_id', 'status', 'priority'];

	/**
	 * Function to get the url for default view of the module
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=Mail&parent=Settings&view=List';
	}
	
	/**
	 * Function to get the url for create view of the module
	 * @return string URL
	*/ 
	public function getCreateRecordUrl()
	{
		return '';
	}
	
	public function getFilterFields()
	{
		return $this->filterFields;
	}
	
}
