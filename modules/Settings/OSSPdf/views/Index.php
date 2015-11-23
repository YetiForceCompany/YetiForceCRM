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
require_once( 'include/utils/UserInfoUtil.php' );

class Settings_OSSPdf_Index_View extends Settings_Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request)
	{
		parent::preProcess($request);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);

		$jsFileNames = [
			'modules.Settings.OSSPdf.general'
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function process(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $request->getModule(FALSE);

		$names = array('Emails', 'PBXManager', 'ModComments', 'SMSNotifier', 'OSSPdf');
		foreach ($names as $id) {
			$in .= "'" . $id . "',";
		}
		$in = trim($in, ',');
		$wynik = $db->query("select tabid,name,customized from vtiger_tab where isentitytype='1' and presence<> '2' and name not in ( $in )", true);
		$modules = array();
		$BUTTONINFO = array();

		for ($i = 0; $i < $db->num_rows($wynik); $i++) {
			$label = $db->query_result($wynik, $i, "name");
			$modules[$label] = vtranslate($label, $label);
			$tabid = $db->query_result($wynik, $i, "tabid");
			$wyn = $db->query("select * from vtiger_links where linklabel='Pdf' and tabid='$tabid'", true);

			if ($db->num_rows($wyn) > 0) {
				$BUTTONINFO[$db->query_result($wynik, $i, "name")] = 1;
			} else {
				$BUTTONINFO[$db->query_result($wynik, $i, "name")] = 0;
			}
		}
		$mode = $request->get('mode');
		$viewer = $this->getViewer($request);

		if ($mode == 'delete_buttons') {
			$name = $request->get('formodule');
			include_once( 'vtlib/Vtiger/Module.php' );
			$modCommentsModule = Vtiger_Module::getInstance($name);
			$modCommentsModule->deleteLink('LISTVIEWSIDEBARWIDGET', 'Pdf');
			$modCommentsModule->deleteLink('DETAILVIEWSIDEBARWIDGET', 'Pdf');
			$modCommentsModule->deleteLink('DETAILVIEWBASIC', 'LBL_QUICK_GENERATE_MAIL'); // do ewentualnej poprawy
			$modCommentsModule->deleteLink('DETAILVIEWBASIC', 'Generate default PDF');
			$viewer->assign('deleted_buttons', $name);
			$request->set('mode', '');
		}

		if ($mode == 'create_buttons') {
			$name = $request->get('formodule');
			$recordModel = Vtiger_Module_Model::getCleanInstance('OSSPdf');
			$recordModel->add_links($name);
			$viewer->assign('created_buttons', $name);
			$request->set('mode', '');
		}

		$users = getAllUserName();
		$groups = getAllGroupName();

		$viewer->assign('users', $users);
		$viewer->assign('groups', $groups);
		$viewer->assign('modules', $modules);
		$viewer->assign('BUTTONINFO', $BUTTONINFO);

		if (empty($mode)) {
			$pobierz = $db->query("select * from vtiger_osspdf_config where conf_id = 'GENERALCONFIGURATION'", true);
			$selected = Array();
			for ($i = 0; $i < $db->num_rows($pobierz); $i++) {
				$viewer->assign($db->query_result($pobierz, $i, "name"), $db->query_result($pobierz, $i, "value"));
				$selected[$db->query_result($pobierz, $i, "name")] = $db->query_result($pobierz, $i, "value");
			}
			$viewer->assign('SELECTED', $selected);
		} elseif ('update' == $mode) {

			if ($request->get('ifsave') != '') {
				$ifsave = 'yes';
			} else {
				$ifsave = 'no';
			}

			if ($request->get('ifattach') != '') {
				$ifattach = 'yes';
			} else {
				$ifattach = 'no';
			}

			$names = array('Emails', 'PBXManager', 'ModComments', 'SMSNotifier', 'OSSPdf');
			foreach ($names as $id) {
				$in .= "'" . $id . "',";
			}
			$in = trim($in, ',');
			$wynik = $db->query("select tabid,name from vtiger_tab where isentitytype='1' and presence<> '2' and name not in ( $in )", true);
			$modules = array();
			for ($i = 0; $i < $db->num_rows($wynik); $i++) {
				$name = $db->query_result($wynik, $i, "name");
				$val = $request->get($name);
				//	echo '<br/>'.$name.' wartosc: '.$val;
				$wart = $db->query("update vtiger_osspdf_config set value='$val' where name = '$name' and conf_id='GENERALCONFIGURATION'", true);
			}


			$wynik = $db->query("update vtiger_osspdf_config set value='$ifsave' where name = 'ifsave' and conf_id='GENERALCONFIGURATION'", true);
			$wynik = $db->query("update vtiger_osspdf_config set value='$ifattach' where name = 'ifattach' and conf_id='GENERALCONFIGURATION'", true);
			$pobierz = $db->query("select * from vtiger_osspdf_config where conf_id = 'GENERALCONFIGURATION'", true);
			$selected = Array();
			for ($i = 0; $i < $db->num_rows($pobierz); $i++) {
				$viewer->assign($db->query_result($pobierz, $i, "name"), $db->query_result($pobierz, $i, "value"));
				$selected[$db->query_result($pobierz, $i, "name")] = $db->query_result($pobierz, $i, "value");
			}

			$viewer->assign('SELECTED', $selected);
		}

		// Operation to be restricted for non-admin users.
		if (!Users_Record_Model::getCurrentUserModel()->isAdminUser()) {
			$viewer->assign('IS_ADMIN', 'false');
		} else {
///Wczytanie pliku konfiguracyjnego
			$includes_dir = "modules/" . $module . "/config";
			$viewer->assign('IS_ADMIN', 'true');
			$mode = $request->get('mode');

			$id = $request->get("id");
			$viewer->assign('recordid', $id);

			$dir = dir("modules/OSSPdf/special_functions");
			$special_functions_list = array();
			$indeks = 0;
			while ($file = $dir->read()) {
				if ($file != '.' && $file != '..' && $file != 'example.php') {
					include( "modules/OSSPdf/special_functions/" . $file );
					$functionname = str_replace(".php", "", $file);
					if (isset($variables_list)) {
						$variableRegex = '/^[ \t]*\\$([^=]+)=([^;]+)/';
						$plik = fopen('modules/OSSPdf/special_functions/' . $file, 'r');

						$list = array();
						while (!feof($plik)) {
							$linia = fgets($plik);

							if (preg_match($variableRegex, $linia, $m)) {
								$nazwa = trim($m[1]);
								$wartosc = trim($m[2]);
								if (isset($variables_list[$nazwa])) {
									$list[$nazwa]['value'] = trim($wartosc, "'");
									$list[$nazwa]['label'] = vtranslate($variables_list[$nazwa], "OSSPdf");
								}
							}
						}
						$special_functions_list[$file]['id'] = $indeks;
						$special_functions_list[$file]['functionname'] = vtranslate($functionname, "OSSPdf");
						$special_functions_list[$file]['variables'] = $list;
						if ($indeks == 0) {
							$special_functions_list[$file]['selected'] = 'true';
						} else {
							$special_functions_list[$file]['selected'] = 'false';
						}
						unset($variables_list);
						$indeks++;
					}
				}
			}

			$viewer->assign('counter', $indeks);
			$viewer->assign('functionlist', $special_functions_list);
		}
		$viewer->view('GeneralConfiguration.tpl', $moduleName);
	}
}
