<?php

/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * *********************************************************************************************************************************** */

class FormatValue {

	public function formatValueTo($attributeMap, $value) {
		
		$methodName = 'getValueFrom' . ucfirst($attributeMap['crmfieldtype']);
		
		if(method_exists($this, $methodName)){
			return $this->$methodName($value, $attributeMap);
		}
		
		return $value;
	}
	
	protected function getValueFromDatediff($value, $attr) {
		
		list($firstDateFld, $secondDateFld) = explode('|', $attr['crmfield']);
		$firstDate = $value[$firstDateFld];
		$secendDate = $value[$secondDateFld];
		
		$formatDate = Users_Record_Model::getCurrentUserModel()->get('date_format');
		
		$formatedFirstDate = DateTimeField::__convertToDBFormat($firstDate, $formatDate);
		$formatedSecendDate = DateTimeField::__convertToDBFormat($secendDate, $formatDate);
		
		$firstDateObj = DateTime::createFromFormat('Y-m-d', $formatedFirstDate );
		$secendDateObj = DateTime::createFromFormat('Y-m-d', $formatedSecendDate );
		
		$interval =$firstDateObj->diff($secendDateObj);
		
		return $interval->format('%a');
	}
	
	protected function getValueFromPaymenttype($value, $attr) {
		
		if (false !== strpos($value, 'Transfer')
				|| false !== strpos($value, 'Przelew')
				|| false !== strpos($value, 'transfer')) {
			return '42';
		}
		
		if (false !== strpos($value, 'cash')) {
			return '10';
		}
		
		return '';
	}
	
	protected function getValueFromIfcondition($value, $attr) {
		list($firstFld, $secondFld) = explode('|', $attr['crmfield']);
		$testCondition = $attr['testcondition'];
		
		$methodName = 'testCondition' . ucfirst($testCondition);
		
		if(method_exists(FormatValue, $methodName)){
			return static::$methodName($firstFld, $secondFld, $value); 
		}
		
		return $value;
	}
	
	protected function testConditionEmpty($firstFld, $secondFld, $info) {
		if (empty($info[$firstFld])) {
			return $info[$secondFld];
		}
		
		return $info[$firstFld];
	}

	private function getValueFromCompany($value, $attr) {
		return Settings_Vtiger_CompanyDetails_Model::getInstance()->get($attr['crmfield']);
	}
	
	private function getValueFromReference($recordId, $attr) {
		$db = PearDatabase::getInstance();
		$attributes = explode( ' ', $attr['crmfield'] );
			
		if (isRecordExists($recordId)) {

			$recordSql = getListQuery($attr['refmoule'], ' AND crmid = ' . $recordId);

			$recordResult = $db->query($recordSql, TRUE);
			$record = $db->raw_query_result_rowdata($recordResult);

			foreach( $attributes as $attribute ){
				$referenceValue = $record[$attribute];
				if (empty($referenceValue)) { // some reference column names are diffrent than field name ex. account_id - accountid
					$referenceValue = $record[str_replace('_', '', $attribute)];
				}
				$retVal[] = $referenceValue;
			}

			return implode( ' ', $retVal );
		}
		
		return '';
	}
	
	private function getValueFromDate($value, $attr){
		$recordData = $this->explodeValue($value, $attr);
		return DateTimeField::convertToDBFormat($recordData);
	}
	
	private function getValueFromCurrency($value, $attr) {
		return $this->getCurrencyShortcut($value);
	}

	public function explodeValue($value, $attr = NULL){
		
		if ($attr['delimiter']) {
			$delimiter = $attr['delimiter'];
		} else {
			$delimiter = ' ';
		}
		
		$tab = explode($delimiter, $value);
		
		if ($attr) {
			return $tab[$attr['partvalue']];
		} else {
			return $tab;
		}
	}
	
	private function getCurrencyShortcut($value) {
		return Vtiger_Functions::getSingleFieldValue('vtiger_currencies', 'currency_code', 'currency_name', $value);
	}
}
