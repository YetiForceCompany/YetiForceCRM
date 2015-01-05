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
class OSSMail_Module_Model extends Vtiger_Module_Model {

	public function getDefaultViewName() {
		return 'index';
	}
        
	public function getSettingLinks() {
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$settingsLinks = array();

		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT fieldid FROM vtiger_settings_field WHERE name =  'OSSMail' AND description =  'OSSMail'", true);
		
		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_MODULE_CONFIGURATION',
			'linkurl' => 'index.php?module=OSSMail&parent=Settings&view=index&block=4&fieldid=' . $db->query_result($result, 0, 'fieldid'),
			'linkicon' => $layoutEditorImagePath
		);
		
		return $settingsLinks;
	}

	public function createBookMailsFiles() {
		global $adb;
		$files = array();
		
		$result = $adb->query( 'SELECT * FROM vtiger_contactsbookmails;');
        $fstart = '<?php $bookMails = array(';
		$fend .= ');';
        for($i = 0; $i < $adb->num_rows($result); $i++){
            $name = $adb->query_result($result, $i, 'name');
			$email = $adb->query_result($result, $i, 'email');
			$users = $adb->query_result_raw($result, $i, 'users');
			if($users != ''){
				$users = explode(',',$users);
				foreach ($users as $user){
					$files[$user] .= "'$name <$email>',";
				}
			}
        }
		foreach ($files as $user => $file){
			file_put_contents( 'cache/addressBook/mails_'.$user.'.php' , $fstart.$file.$fend );
		}
	}
}