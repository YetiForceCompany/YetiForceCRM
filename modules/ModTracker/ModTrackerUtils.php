<?php
require_once('include/utils/utils.php');
require_once dirname(__FILE__) . '/ModTracker.php';

class ModTrackerUtils
{

	static function modTrac_changeModuleVisibility($tabid, $status)
	{
		if ($status == 'module_disable') {
			ModTracker::disableTrackingForModule($tabid);
		} else {
			ModTracker::enableTrackingForModule($tabid);
		}
	}

	public function modTrac_getModuleinfo()
	{
		$adb = PearDatabase::getInstance();
		$query = $adb->pquery("SELECT vtiger_modtracker_tabs.visible,vtiger_tab.name,vtiger_tab.tabid
								FROM vtiger_tab
								LEFT JOIN vtiger_modtracker_tabs ON vtiger_modtracker_tabs.tabid = vtiger_tab.tabid
								WHERE vtiger_tab.isentitytype = 1 && vtiger_tab.name NOT IN('Emails')", array());
		$rows = $adb->num_rows($query);

		for ($i = 0; $i < $rows; $i++) {
			$infomodules[$i]['tabid'] = $adb->query_result($query, $i, 'tabid');
			$infomodules[$i]['visible'] = $adb->query_result($query, $i, 'visible');
			$infomodules[$i]['name'] = $adb->query_result($query, $i, 'name');
		}

		return $infomodules;
	}
}

?>
