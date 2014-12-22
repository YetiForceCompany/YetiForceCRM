<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
require_once 'modules/WSAPP/synclib/models/BaseModel.php';

class WSAPP_SyncStateModel extends WSAPP_BaseModel{

	// Represents the module which will the synchronize will happen
	protected $type;

	public function getLastSyncTime(){
		return $this->get('lastSyncTime');
	}

	public function setLastSyncTime($lastSyncTime){
		return $this->set('lastSyncTime',$lastSyncTime);
	}

	public function setMoreRecords($more){
		return $this->set('more',$more);
	}

	public function hasMoreRecords(){
		return ($this->get('more')==1) ? true : false;
	}

	public function getSyncTrackerId(){
		return $this->get('synctrackerid');
	}

	public function setSyncTrackerId($value){
		return $this->set('synctrackerid',$value);
	}

	public function getSyncToken(){
		return $this->get('synctoken');
	}

	public function setSyncToken($syncToken){
		return $this->set('synctoken',$syncToken);
	}

	public function setType($type){
		$this->type = $type;
		return $this;
	}

	public function getType(){
		return $this->type;
	}

	public function getInstanceFromSyncResult($syncResult){
		$model = new self();
		return $model->setLastSyncTime($syncResult['lastModifiedTime'])->setMoreRecords($syncResult['more']);
	}

	public function getInstanceFromQueryResult($rowData){
		$model = new self();
		return $model->setData($rowData);
	}
	
}

?>
