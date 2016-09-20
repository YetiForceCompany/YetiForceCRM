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

class ChangesList
{

	private $moduleList = array('all');

	public function process($data)
	{
		$adb = PearDatabase::getInstance();
		$html = '';
		if ($data['record'] != '') {

			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta($data['module'], $data['record']);
			if (count($delta) == 0) {
				return '';
			}
			$tabid = \includes\Modules::getModuleId($data['module']);
			$html = '<ul>';
			foreach ($delta as $fieldName => $values) {
				if ($fieldName != 'modifiedtime' && in_array($fieldName, array('record_id', 'record_module')) == false && strstr($fieldName, 'label') === false) {
					$result = $adb->pquery("SELECT uitype,fieldlabel FROM vtiger_field WHERE fieldname = ? && tabid = ?", array($fieldName, $tabid), true);
					$fieldlabel = $adb->query_result_raw($result, 0, 'fieldlabel');
					$uitype = $adb->query_result_raw($result, 0, 'uitype');

					$oldValue = $values['oldValue'];
					if ($oldValue == '')
						$oldValue = 'LBL_NULL_VALUE';
					$currentValue = $values['currentValue'];
					if ($currentValue == '')
						$currentValue = 'LBL_NULL_VALUE';

					if ($uitype == 10 && $oldValue != 'LBL_NULL_VALUE' && $currentValue != 'LBL_NULL_VALUE') {
						$oldValue = vtlib\Functions::getCRMRecordLabel($oldValue);
						$currentValue = vtlib\Functions::getCRMRecordLabel($currentValue);
					} elseif (in_array($uitype, array('53', '52', '77')) && $oldValue != 'LBL_NULL_VALUE' && $currentValue != 'LBL_NULL_VALUE') {
						$oldValue = vtlib\Functions::getOwnerRecordLabel($oldValue);
						$currentValue = vtlib\Functions::getOwnerRecordLabel($currentValue);
					} elseif ($uitype == 56 && $oldValue != 'LBL_NULL_VALUE' && $currentValue != 'LBL_NULL_VALUE') {
						$oldValue = ($oldValue == 1) ? vtranslate('LBL_YES', $data['module']) : vtranslate('LBL_NO', $data['module']);
						$currentValue = ($currentValue == 1) ? vtranslate('LBL_YES', $data['module']) : vtranslate('LBL_NO', $data['module']);
					} else {
						$oldValue = vtranslate($oldValue, $data['module']);
						$currentValue = vtranslate($currentValue, $data['module']);
					}
					$html .= '<li>' . vtranslate('LBL_CHANGED', $data['module']) . ' <strong>' . vtranslate($fieldlabel, $data['module']) . '</strong> ' . vtranslate('LBL_FROM') . ' <i>' . $oldValue . '</i> ' . vtranslate('LBL_TO') . ' <i>' . $currentValue . '</i></li>';
				}
			}
			$html .= '</ul>';
			return $html;
		}
	}

	public function getListAllowedModule()
	{
		return $this->moduleList;
	}
}
