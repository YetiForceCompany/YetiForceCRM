<?php

/**
 * List View Model Class for Mail Settings
 * @package YetiForce.Settings.Record
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */

class Settings_Mail_ListView_Model extends Settings_Vtiger_ListView_Model
{

	public function getBasicListQuery()
	{
		$query = parent::getBasicListQuery();
		$orderBy = $this->get('orderby');
		if(empty($orderBy)){
			$query->orderBy(['priority' => SORT_DESC, 'date' => SORT_ASC]);
		}else{
			$query->orderBy($orderBy);
		}
		return $query;
	}
	
	public function getBasicLinks(){
		return [];
	}
}
