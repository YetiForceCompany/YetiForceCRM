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
class Settings_SalesProcesses_Module_Model extends Settings_Vtiger_Module_Model {

	/**
	 * Gets config data for sales processes
	 * @return - array of settings success, false on failure
	 */
	public static function getConfig() {
		global $log;
		$log->debug("Entering Settings_SalesProcesses_Module_Model::getConfig() method ...");

		$db = PearDatabase::getInstance();
		$sql = 'SELECT `products_rel_potentials` FROM `vtiger_salesprocesses_settings`;';

		$result = $db->query( $sql );
		$num = $db->num_rows( $result );
		
		$holidays = array();
		if ( $num == 1 ) {
			$productsRelPotentials = $db->query_result( $result, 0, 'products_rel_potentials' );
			$holidays['products_rel_potentials'] = $productsRelPotentials;
		}
		$log->debug("Exiting Settings_SalesProcesses_Module_Model::getConfig() method ...");
		return $holidays;
	}

	/**
	 * Saves config data for sales processes
	 * @param <Int> $productsRelPotentials - show only products related to potential
	 * @return - true or false
	 */
	public static function saveConfig( $productsRelPotentials ) {
		global $log;
		$log->debug("Entering Settings_SalesProcesses_Module_Model::saveConfig(".$productsRelPotentials.") method ...");

		$db = PearDatabase::getInstance();
		$sql = 'UPDATE `vtiger_salesprocesses_settings` SET `products_rel_potentials` = ? WHERE `id` = 1 LIMIT 1;';
		$params = array( intval($productsRelPotentials) );

		$result = $db->pquery( $sql, $params, true );
		$num = $db->num_rows( $result );

		$log->debug("Exiting Settings_SalesProcesses_Module_Model::saveConfig() method ...");
		if ( $db->getAffectedRowCount( $result ) ) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Checks if products are set to be narrowed to only those related to Potential
	 * @return - true or false
	 */
	public static function checkRelatedToPotentialsLimit() {
		global $log;
		$log->debug("Entering Settings_SalesProcesses_Module_Model::checkRelatedToPotentialsLimit() method ...");

		$db = PearDatabase::getInstance();
		$sql = 'SELECT `products_rel_potentials` FROM `vtiger_salesprocesses_settings`;';

		$result = $db->pquery( $sql );
		$productsRelPotentials = $db->query_result( $result, 0, 'products_rel_potentials' );

		$log->debug("Exiting Settings_SalesProcesses_Module_Model::checkRelatedToPotentialsLimit() method ...");
		if ( $productsRelPotentials == 1 ) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Checks if limit can be applied to this module
	 * @return - true or false
	 */
	public static function isLimitForModule( $moduleName ) {
		$validModules = array( 'Quotes', 'Calculations', 'SalesOrder', 'Invoice' );
		return in_array( $moduleName, $validModules );
	}
}
