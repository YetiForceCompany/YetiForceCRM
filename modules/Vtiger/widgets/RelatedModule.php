<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Vtiger_RelatedModule_Widget extends Vtiger_Basic_Widget
{

	public function getUrl()
	{
		$url = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showRelatedRecords&relatedModule=' . $this->Data['relatedmodule'] . '&page=1&limit=' . $this->Data['limit'] . '&col=' . $this->Data['columns'];
		if (isset($this->Data['no_result_text'])) {
			$url .= '&r=' . $this->Data['no_result_text'];
		}
		return $url;
	}

	public function getWidget()
	{
		$widget = [];
		$model = Vtiger_Module_Model::getInstance($this->Data['relatedmodule']);
		if ($model->isPermitted('DetailView')) {
			$whereCondition = [];
			$this->Config['url'] = $this->getUrl();
			$this->Config['tpl'] = 'Basic.tpl';
			if ($this->Data['action'] == 1) {
				$createPermission = $model->isPermitted('CreateView');
				$this->Config['action'] = ($createPermission == true) ? 1 : 0;
				$this->Config['actionURL'] = $model->getQuickCreateUrl();
			}
			if (isset($this->Data['showAll'])) {
				$this->Config['url'] .= '&showAll=' . $this->Data['showAll'];
			}
			if (isset($this->Data['switchHeader']) && $this->Data['switchHeader'] != '-') {
				$switchHeaderData = Settings_Widgets_Module_Model::getHeaderSwitch([$this->Data['relatedmodule'], $this->Data['switchHeader']]);
				if ($switchHeaderData) {
					switch ($switchHeaderData['type']) {
						case 1:
							$whereConditionOff = [];
							foreach ($switchHeaderData['value'] as $name => $value) {
								$whereCondition[$name] = ['comparison' => 'NOT IN', 'value' => $value];
								$whereConditionOff[$name] = ['comparison' => 'IN', 'value' => $value];
							}
							$this->getCheckboxLables($model, 'switchHeader', 'LBL_SWITCHHEADER_');
							$this->Config['switchHeader']['on'] = \includes\utils\Json::encode($whereCondition);
							$this->Config['switchHeader']['off'] = \includes\utils\Json::encode($whereConditionOff);
							$whereCondition = [$whereCondition];
							break;
						default:
							break;
					}
				}
			}
			if (isset($this->Data['checkbox']) && $this->Data['checkbox'] != '-') {
				$whereCondition[][$this->Data['checkbox']] = 1;
				$this->Config['checkbox']['on'] = \includes\utils\Json::encode([$this->Data['checkbox'] => 1]);
				$this->Config['checkbox']['off'] = \includes\utils\Json::encode([$this->Data['checkbox'] => 0]);
				$this->getCheckboxLables($model, 'checkbox', 'LBL_SWITCH_');
			}
			if (!empty($whereCondition)) {
				$this->Config['url'] .= '&whereCondition=' . \includes\utils\Json::encode($whereCondition);
			}
			$widget = $this->Config;
		}
		return $widget;
	}

	public function getCheckboxLables($model, $type, $prefix)
	{
		$on = $prefix . 'ON_' . strtoupper($this->Data[$type]);
		$translateOn = vtranslate($on, $model->getName());
		if ($on == $translateOn) {
			$translateOn = vtranslate('LBL_YES', $model->getName());
		}
		$off = $prefix . 'OFF_' . strtoupper($this->Data[$type]);
		$translateOff = vtranslate($off, $model->getName());

		if ($off == $translateOff) {
			$translateOff = vtranslate('LBL_NO', $model->getName());
		}
		$this->Config[$type . 'Lables'] = ['on' => $translateOn, 'off' => $translateOff];
	}

	public function getConfigTplName()
	{
		return 'RelatedModuleConfig';
	}
}
