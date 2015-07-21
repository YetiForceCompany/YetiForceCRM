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
class Vtiger_PotentialsList_Widget extends Vtiger_Basic_Widget {
	var $allowedModules  = ['Accounts'];
	
	public function getUrl() {
		return 'module=Potentials&view=Widget&fromModule='.$this->Module.'&record='.$this->Record.'&mode=showPotentialsList&page=1&limit='.$this->Data['limit'];
	}
	public function getWidget() {
		$this->Config['url'] = $this->getUrl();
		$this->Config['tpl'] = 'PotentialsListBasic.tpl';
		$this->Config['relatedmodule'] = 'Potentials';
		return $this->Config;
	}
	public function getConfigTplName() {
		return 'PotentialsListConfig';
	}
}
