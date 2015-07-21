<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Vtiger_RelatedModule_Widget extends Vtiger_Basic_Widget {
	public function getUrl() {
		return 'module='.$this->Module.'&view=Detail&record='.$this->Record.'&mode=showRelatedRecords&relatedModule='.$this->Data['relatedmodule'].'&page=1&limit='.$this->Data['limit'].'&col='.$this->Data['columns'].'&r='.$this->Data['no_result_text'];
	}
	public function getWidget() {
		$widget = array();
		$model = Vtiger_Module_Model::getInstance($this->Data['relatedmodule']);
		if( $model->isPermitted('DetailView') ) {
			$this->Config['url'] = $this->getUrl();
			$this->Config['tpl'] = 'Basic.tpl';
			if($this->Data['action'] == 1){
				$createPermission = $model->isPermitted('EditView');
				$this->Config['action'] = ($createPermission == true) ? 1 : 0;
				$this->Config['actionURL'] = $model->getQuickCreateUrl();
			}
			if(isset($this->Data['filter'])){
				$filterArray = explode('::',$this->Data['filter']);
				$this->Config['field_name'] = $filterArray[2];
			}
			if(isset($this->Data['checkbox']) && $this->Data['checkbox'] != '-'){
				$this->Config['url'] .= '&whereCondition['.getTableNameForField(getTabModuleName($this->Data['relatedmodule']), $this->Data['checkbox']).'.'.$this->Data['checkbox'].']=1';
				$on = 'LBL_SWITCH_ON_'.strtoupper($this->Data['checkbox']);
				$translateOn = vtranslate($on,$model->getName());
				if($on == $translateOn){
					$translateOn = vtranslate('LBL_YES',$model->getName());
				}
				$off = 'LBL_SWITCH_OFF_'.strtoupper($this->Data['checkbox']);
				$translateOff = vtranslate($off,$model->getName());
				
				if($off == $translateOff){
					$translateOff = vtranslate('LBL_NO',$model->getName());
				}
				$this->Config['checkboxLables'] = ['on' => $translateOn, 'off' => $translateOff];
			}
			$widget = $this->Config;
		}
		return $widget;
	}
	public function getConfigTplName() {
		return 'RelatedModuleConfig';
	}
}
