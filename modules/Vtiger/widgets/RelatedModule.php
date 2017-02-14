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
				$this->Config['action'] = ($createPermission === true) ? 1 : 0;
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
								$whereCondition[] = [$name, 'n', implode(',', $value)];
								$whereConditionOff[] = [$name, 'e', implode(',', $value)];
							}
							$this->getCheckboxLables($model, 'switchHeader', 'LBL_SWITCHHEADER_');
							$this->Config['switchHeader']['on'] = \App\Json::encode($whereCondition);
							$this->Config['switchHeader']['off'] = \App\Json::encode($whereConditionOff);
							$whereCondition = [$whereCondition];
							break;
						default:
							break;
					}
				}
			}
			$this->Config['buttonHeader'] = Settings_Widgets_Module_Model::getHeaderButtons($this->Data['relatedmodule']);
			if (isset($this->Data['checkbox']) && $this->Data['checkbox'] !== '-') {
				if (strpos($this->Data['checkbox'], '.') !== false) {
					$separateData = explode('.', $this->Data['checkbox']);
					$columnName = $separateData[1];
				} else {
					$columnName = $this->Data['checkbox'];
				}

				$whereOnCondition[] = [$columnName, 'e', 1];
				$whereOffCondition[] = [$columnName, 'e', 0];
				$whereCondition = [$whereOnCondition];

				$this->Config['checkbox']['on'] = \App\Json::encode($whereOnCondition);
				$this->Config['checkbox']['off'] = \App\Json::encode($whereOffCondition);
				$this->getCheckboxLables($model, 'checkbox', 'LBL_SWITCH_');
			}
			if (!empty($whereCondition)) {
				$this->Config['url'] .= '&search_params=' . \App\Json::encode($whereCondition);
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
