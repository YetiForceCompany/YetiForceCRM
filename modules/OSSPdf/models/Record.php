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

class OSSPdf_Record_Model extends Vtiger_Record_Model
{

	// function
	function getFooterHeaderInfo($pdftype, $id, $template)
	{
		$this->id = $id;
		$db = PearDatabase::getInstance();

		$result = $db->query("SELECT osspdf_enable_header, osspdf_enable_footer, header_content, footer_content, osspdf_enable_numbering FROM vtiger_osspdf WHERE osspdfid = $template", true);
		$header_content = $db->query_result($result, 0, 'header_content');
		$footer_content = $db->query_result($result, 0, 'footer_content');

		$osspdf_enable_numbering = $db->query_result($result, 0, 'osspdf_enable_numbering');

		$header_permission = $db->query_result($result, 0, 'osspdf_enable_header');
		$footer_permission = $db->query_result($result, 0, 'osspdf_enable_footer');

		$header_content = htmlspecialchars_decode($header_content);
		$header_content = str_replace("&nbsp;", "", $header_content);

		$footer_content = htmlspecialchars_decode($footer_content);
		$footer_content = str_replace("&nbsp;", "", $footer_content);

		$position = (int) strpos($header_content, '<!--');
		if ($position != 0) {
			$header_content = strstr($header_content, '<!--', true);
		}
		$header_content = str_replace("px;", "pt", $header_content);

		$position = (int) strpos($footer_content, '<!--');
		if ($position != 0) {
			$footer_content = strstr($footer_content, '<!--', true);
		}
		$footer_content = str_replace("px;", "pt", $footer_content);
		$userLang = Users_Record_Model::getCurrentUserModel()->get('language');

		include("languages/" . $userLang . "/OSSPdf.php");

		$tmp = explode("<script>", $header_content);
		$header_content = $tmp[0];
		$tmp = explode("<script>", $footer_content);
		$footer_content = $tmp[0];
		return array($header_content, $header_permission, $footer_permission, $footer_content, $osspdf_enable_numbering);
	}

	function getContent($pdftype, $id, $template)
	{
		$this->id = $id;
		$db = PearDatabase::getInstance();

		$result = $db->query("SELECT content FROM vtiger_osspdf WHERE osspdfid = $template", true);
		$content = $db->query_result($result, 0, 'content');

		$content = htmlspecialchars_decode($content);
		$content = str_replace("&nbsp;", "", $content);

		$position = (int) strpos($content, '<!--');
		if ($position != 0) {
			$content = strstr($content, '<!--', true);
		}
		//	echo '<br/>'.$position.'<br/>'.$content;exit;
		$content = str_replace("px;", "pt", $content);
		//setCurrentLanguage();
		$userLang = Users_Record_Model::getCurrentUserModel()->get('language');
		include("languages/" . $userLang . "/OSSPdf.php");

		if (strlen($content) == 0) {
			$content = vtranslate('LBL_NO_EMPTY_TEMPLATES', 'OSSPdf');
		}

		$tmp = explode("<script>", $content);
		$content = $tmp[0];
		return $content;
	}

	//////////////////////////////////////////////////////////////////////////
	//// Field value completing from main module
	////
	////
	function replaceModuleFields($content, $fields, $module, $recordid)
	{
		$db = PearDatabase::getInstance();
		require_once( 'include/utils/CommonUtils.php' );
		require_once( 'include/fields/CurrencyField.php' );
		require_once( 'include/utils/utils.php' );

		$userLang = Users_Record_Model::getCurrentUserModel()->get('language');
		include("languages/" . $userLang . "/OSSPdf.php");

		#################################################################################
		$ui_datefields = Array('70', '5', '23');
		#################################################################################
		$ui_currfields = Array('71', '72', '7');
		#################################################################################
		$uitypelist = Array('10', '58', '51', '57', '68', '59', '75', '80', '76', '73', '81', '78');
		$uitype2module = Array(
			'58' => 'Campaigns',
			'51' => 'Accounts',
			'57' => 'Contacts',
			'68' => 'Accounts;Contacts',
			'59' => 'Products',
			'75' => 'Vendors',
			'80' => 'SalesOrder',
			'76' => 'Potentials',
			'73' => 'Accounts',
			'81' => 'Vendors',
			'78' => 'Quotes');
		#################################################################################
		if ($module == 'Activity') {
			$wynik = $db->query("select tabid,name from vtiger_tab where name='Calendar'", true);
		} else {
			$wynik = $db->query("select tabid,name from vtiger_tab where name='$module'", true);
		}
		$moduleid = $db->query_result($wynik, 0, "tabid");
		foreach ($fields as $key => $field) {
			$pobierz_pola = $db->query("select fieldid,fieldlabel, uitype from vtiger_field where fieldname='$key' and tabid='$moduleid'", true);
			$label = $db->query_result($pobierz_pola, 0, "fieldlabel");
			$field_uitype = $db->query_result($pobierz_pola, 0, "uitype");
			$fieldid = $db->query_result($pobierz_pola, 0, "fieldid");
			################################################
			if (in_array($field_uitype, $ui_datefields)) {
				$field = getValidDisplayDate($field);
			}
			################################################
			if (in_array($field_uitype, $ui_currfields)) {
				$currfield = new CurrencyField($field);
				$field = $currfield->getDisplayValue();
			}
			////////////////////
			/// For fields that are related it is required to download an appropriate name instead of related record’s ID
			if (in_array($field_uitype, $uitypelist)) {
				if ($field != 0) {
					$singleid = getSalesEntityType($field);
					$newvalue = $db->query("select tablename, entityidfield,fieldname from vtiger_entityname where modulename = '$singleid'", true);
					$tablename = $db->query_result($newvalue, 0, "tablename");
					$fieldname = $db->query_result($newvalue, 0, "fieldname");
					$fieldnames = explode(',', $fieldname);
					$tableid = $db->query_result($newvalue, 0, "entityidfield");
					$newvalue2 = $db->query("select $fieldname from $tablename where $tableid = '$field'", true);
					$field = "";
					foreach ($fieldnames as $partname) {
						$field .= $db->query_result($newvalue2, 0, $partname) . " ";
					}
				}
			}

			/// For user fields
			if ($field_uitype == 53 || $field_uitype == 52) {
				$robocza = getUserName($field);
				if ($robocza == "") {
					$robocza = getGroupName($field)[0];
				}
				$field = $robocza;
			}
			/// For field with folder name
			/* if( $field_uitype == 26 ) {
			  $new_value = $db->query( "select foldername from vtiger_attachmentsfolder where folderid = '$field'", true );
			  $field = $db->query_result( $new_value, 0, "foldername" );
			  } */
			if ($field_uitype == 27) {
				if ($field == 'I') {
					$field = getTranslatedString('Internal', $module);
				} elseif ($field == 'E') {
					$field = getTranslatedString('External', $module);
				}
			}
			/// For field with language
			if ($field_uitype == 32) {
				$name = '%' . $field . '%';
				$new_value = $db->query("select name from vtiger_language where prefix like '$name'", true);
				$field = $db->query_result($new_value, 0, "name");
			}
			/// For field with role in organization
			if ($field_uitype == 98) {
				$new_value = $db->query("select rolename from vtiger_role where roleid = '$field'", true);
				$field = $db->query_result($new_value, 0, "rolename");
			}
			if ($field_uitype == 117) {
				$new_value = $db->query("select currency_code from vtiger_currency_info where id = '$field'", true);
				$field = $db->query_result($new_value, 0, "currency_code");
			}
			/// For user’s image
			if ($field_uitype == 105) {
				if ($field != "") {
					$sql = "SELECT CONCAT(vtiger_attachments.path,vtiger_users.imagename) AS sciezka FROM vtiger_attachments
                    INNER JOIN vtiger_salesmanattachmentsrel ON ( vtiger_salesmanattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid )
                    INNER JOIN vtiger_users ON ( vtiger_users.id = vtiger_salesmanattachmentsrel.smid )
                      WHERE vtiger_salesmanattachmentsrel.smid = '$recordid'";

					$pobierz_zdjecie = $db->query($sql, true);
					if ($db->num_rows($pobierz_zdjecie) > 0) {
						$field = '<img src="' . $db->query_result($pobierz_zdjecie, 0, "sciezka") . '"></img>';
					}
				}
			}
			/// For checkbox type fields
			if ($field_uitype == 56) {
				if ($field == 1) {
					$field = getTranslatedString('yes', "OSSPdf");
				} elseif ($field == 0) {
					$field = getTranslatedString('no', "OSSPdf");
				}
			}
			if ($field_uitype == 10 && is_numeric($field)) {
				if ($field != 0) {
					$field = Vtiger_Functions::getCRMRecordLabel($field);
				} elseif ($field == 0) {
					$field = '';
				}
			}
			if ($field_uitype == 69) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordid, $module);
				$details = $recordModel->getImageDetails();
				if (is_array($details[0])) {
					$field = $details[0]['path'] . '_' . $details[0]['orgname'];
				} else {
					$field = '';
				}
			}
			/// For fields with VAT for products
			if ($field_uitype == 83) {
				$pobierz_tax = $db->query("select * from vtiger_producttaxrel where productid = '$recordid'", true);
				for ($i = 0; $i < $db->num_rows($pobierz_tax); $i++) {
					$taxid = $db->query_result($pobierz_tax, $i, "taxid");
					$taxvalue = $db->query_result($pobierz_tax, $i, "taxpercentage");
					if ($taxid == 1) {
						$field .= getTranslatedString('LBL_VAT') . getTranslatedString('COVERED_PERCENTAGE') . ': ' . $taxvalue . '%';
					} elseif ($taxid == 2) {
						$field .= getTranslatedString('LBL_SALES') . getTranslatedString('COVERED_PERCENTAGE') . ': ' . $taxvalue . '%';
					} elseif ($taxid == 3) {
						$field .= getTranslatedString('LBL_SERVICE') . getTranslatedString('COVERED_PERCENTAGE') . ': ' . $taxvalue . '%';
					}
					$field .= '<br/>';
				}
			}
			/// For selection lists
			if ($field_uitype == 15 || $field_uitype == 16 || ( $field_uitype == 55 && $fieldname == 'salutationtype' )) {
				if ($module == 'Activity') {
					$field = getTranslatedString($field, "Calendar");
				} else {
					$field = getTranslatedString($field, $module);
				}
			}

			if ($field_uitype == 19) {
				$field = htmlspecialchars_decode($field);
			}
			$content = str_replace("#$key#", nl2br($field), $content);
			if ($module == 'Activity') {
				$content = str_replace("#label_$key#", getTranslatedString($label, "Calendar"), $content);
			} else {
				$content = str_replace("#label_$key#", getTranslatedString($label, $module), $content);
			}

			if ($field_uitype == 33) {
				$content = str_replace('|##|', ',', $content);
			}
		}
		return $content;
	}

	function replaceRelatedModuleFields($content, $module, $recordid, $fields, &$site_URL)
	{
		$db = PearDatabase::getInstance();
		require_once( 'include/utils/utils.php' );
		$userLang = Users_Record_Model::getCurrentUserModel()->get('language');
		include("languages/" . $userLang . "/OSSPdf.php");
		require_once( 'include/utils/CommonUtils.php' );
		require_once( 'include/fields/CurrencyField.php' );

		#################################################################################
		$uitypelist2 = Array('10', '58', '51', '57', '68', '59', '75', '80', '76', '73', '81', '78');
		$uitype2module2 = Array(
			'58' => 'Campaigns',
			'51' => 'Accounts',
			'57' => 'Contacts',
			'68' => 'Accounts;Contacts',
			'59' => 'Products',
			'75' => 'Vendors',
			'80' => 'SalesOrder',
			'76' => 'Potentials',
			'73' => 'Accounts',
			'81' => 'Vendors',
			'78' => 'Quotes');
		#################################################################################
		$uitypelist = Array('10', '58', '51', '57', '68', '59', '75', '80', '76', '73', '81', '52', '53', '78');
		$uitype2module = Array(
			'58' => 'Campaigns',
			'51' => 'Accounts',
			'57' => 'Contacts',
			'68' => 'Accounts;Contacts',
			'59' => 'Products',
			'75' => 'Vendors',
			'80' => 'SalesOrder',
			'76' => 'Potentials',
			'73' => 'Accounts',
			'81' => 'Vendors',
			'52' => 'Users',
			'53' => 'Users',
			'78' => 'Quotes');
		#################################################################################
		$ui_datefields = Array('70', '5', '23');
		#################################################################################
		$ui_currfields = Array('71', '72', '7');
		#################################################################################
		if ($module == 'Activity') {
			$wynik = $db->query("select tabid,name from vtiger_tab where name='Calendar'", true);
		} else {
			$wynik = $db->query("select tabid,name from vtiger_tab where name='$module'", true);
		}

		$moduleid = $db->query_result($wynik, 0, "tabid");
		$list = Array();
		$pobierz = $db->query("select fieldid,uitype, fieldname from vtiger_field where tabid = '$moduleid'", true);

		for ($i = 0; $i < $db->num_rows($pobierz); $i++) {
			$uitype = $db->query_result($pobierz, $i, "uitype");
			$fieldid = $db->query_result($pobierz, $i, "fieldid");

			if (in_array($uitype, $uitypelist)) {
				if ($uitype == '10') {
					$wynik = $db->query("select relmodule from vtiger_fieldmodulerel where fieldid = '$fieldid'", true);

					for ($k = 0; $k < $db->num_rows($wynik); $k++) {
						$list[$db->query_result($wynik, $k, "relmodule")] = $fields[$db->query_result($pobierz, $i, "fieldname")];
					}
				} else {
					$zmienna = $uitype2module[$uitype];
					$zmienna = explode(';', $zmienna);
					foreach ($zmienna as $value) {
						$list[$value] = $fields[$db->query_result($pobierz, $i, "fieldname")];
					}
				}
			}
		}

		if (count($list) > 0) {
			foreach ($list as $name => $record) {
				$modulename = $name;
				require_once "modules/$modulename/$modulename.php";

				if ($modulename == 'Users') {
					$obiekt = new $modulename();
					$pobierz_usera = $db->query("select * from vtiger_users where id = '$record'", true);
					if ($db->num_rows($pobierz_usera) > 0) {
						$obiekt->retrieve_entity_info($record, $modulename);
					}
				} else {
					$obiekt = new $modulename();
					$assigned_module = getSalesEntityType($record);

					if (isRecordExists($record) && $assigned_module == $modulename) {
						$obiekt->retrieve_entity_info($record, $modulename);
					}
				}

				$pobierz = $db->query("select tabid from vtiger_tab where name = '$modulename'", true);
				$moduleid = $db->query_result($pobierz, 0, "tabid");

				$pobierz_bloki = $db->query("select blockid, blocklabel from vtiger_blocks where tabid = '$moduleid'", true);
				$relatedfield_list = Array();

				for ($k = 0; $k < $db->num_rows($pobierz_bloki); $k++) {
					$blockid = $db->query_result($pobierz_bloki, $k, "blockid");
					$label = $db->query_result($pobierz_bloki, $k, "blocklabel");
					$pobierz_pola = $db->query("select fieldname,fieldlabel,uitype from vtiger_field where block='$blockid' and tabid = '$moduleid'", true);

					for ($i = 0; $i < $db->num_rows($pobierz_pola); $i++) {
						$field_uitype = $db->query_result($pobierz_pola, $i, "uitype");
						$label = $db->query_result($pobierz_pola, $i, "fieldlabel");
						$key = $db->query_result($pobierz_pola, $i, "fieldname");
						$value = $obiekt->column_fields[$key];
						################################################
						/// for date type fields
						if (in_array($field_uitype, $ui_datefields) && !empty($field)) {
							$value = getValidDisplayDate($value);
						}
						################################################
						/// for currency type fields
						if (in_array($field_uitype, $ui_currfields)) {
							$currfield = new CurrencyField($value);
							$value = $currfield->getDisplayValue();
						}
						//// for users language field
						if ($field_uitype == 27) {
							if ($value == 'I') {
								$value = getTranslatedString('Internal', $modulename);
							} elseif ($value == 'E') {
								$value = getTranslatedString('External', $modulename);
							}
						}
						if ($field_uitype == 32) {
							$name = '%' . $value . '%';
							$new_value = $db->query("select name from vtiger_language where prefix like '$name'", true);
							$value = getTranslatedString($db->query_result($new_value, 0, "name"));
						}
						/// dla pól z przypisanym użytkownikie,
						if ($field_uitype == 53 || $field_uitype == 52) {
							$value = getUserName($value);
						}
						/// dla pól z folderem
						/* if( $field_uitype == 26 ) {
						  $new_value = $db->query( "select foldername from vtiger_attachmentsfolder where folderid = '$value'", true );
						  $value = $db->query_result( $new_value, 0, "foldername" );
						  } */
						/// Dla pól z roląużytkownika w organizacji
						if ($field_uitype == 98) {
							$new_value = $db->query("select rolename from vtiger_role where roleid = '$value'", true);
							$value = $db->query_result($new_value, 0, "rolename");
						}
						/// Dla pól typu checkbox
						if ($field_uitype == 56) {
							if ($value == 1) {
								$value = getTranslatedString('yes', "OSSPdf");
							} elseif ($value == 0) {
								$value = getTranslatedString('no', "OSSPdf");
							}
						}

						/// Dla pola ze zdjęciem użytkownika
						if ($field_uitype == 105) {
							$recordModel = Users_Record_Model::getInstanceFromUserObject($obiekt);
							$details = $recordModel->getImageDetails();

							if (is_array($details[0])) {
								$value = $details[0]['path'] . '_' . $details[0]['orgname'];
							} else {
								$value = '';
							}
						}

						/// Dla pól typu lista wyboru
						if ($field_uitype == 15 || $field_uitype == 16 || ( $field_uitype == 55 && $key == 'salutationtype' )) {
							$value = getTranslatedString($value, $modulename);
						}
						if ($field_uitype == 83) {
							$pobierz_tax = $db->query("select * from vtiger_producttaxrel where productid = '$record'", true);
							for ($a = 0; $a < $db->num_rows($pobierz_tax); $a++) {
								$taxid = $db->query_result($pobierz_tax, $a, "taxid");
								$taxvalue = $db->query_result($pobierz_tax, $a, "taxpercentage");
								if ($taxid == 1) {
									$value .= getTranslatedString('LBL_VAT') . getTranslatedString('COVERED_PERCENTAGE') . ': ' . $taxvalue . '%';
								} elseif ($taxid == 2) {
									$value .= getTranslatedString('LBL_SALES') . getTranslatedString('COVERED_PERCENTAGE') . ': ' . $taxvalue . '%';
								} elseif ($taxid == 3) {
									$value .= getTranslatedString('LBL_SERVICE') . getTranslatedString('COVERED_PERCENTAGE') . ': ' . $taxvalue . '%';
								}
								$value .= '<br/>';
							}
						}
						########################

						if (in_array($field_uitype2, $uitypelist2)) {
							if ($field_uitype == 10) {
								$pobierz_wartosc = $db->query("select relmodule from vtiger_fieldmodulerel where fieldid = '$fieldid'", true);

								for ($i = 0; $i < $db->num_rows($pobierz_wartosc); $i++) {
									$module .= $db->query_result($pobierz_wartosc, $i, "relmodule") . ';';
								}
							} else {
								$module = $uitype2module[$field_uitype];
							}
							$module = trim($module, ';');
							$ids = explode(';', $module);
							foreach ($ids as $singleid) {
								$newvalue = $db->query("select tablename, entityidfield,fieldname from vtiger_entityname where modulename = '$singleid'", true);
								$tablename = $db->query_result($newvalue, 0, "tablename");
								$fieldname = $db->query_result($newvalue, 0, "fieldname");
								$tableid = $db->query_result($newvalue, 0, "entityidfield");
								$newvalue2 = $db->query("select $fieldname from $tablename where $tableid = '$value'", true);
								$value = $db->query_result($newvalue2, 0, $fieldname);
							}
						}
						########################
						if ($field_uitype == 10 && is_numeric($value)) {
							if ($value != 0) {
								$value = Vtiger_Functions::getCRMRecordLabel($value);
							} elseif ($value == 0) {
								$value = '';
							}
						}

						$string = "#" . $modulename . "_" . $key . "#";
						$string2 = "#" . $modulename . "_label_" . $key . "#"; //."# TLUMACZENIE: ". getTranslatedString( $label, $modulename );
						$pozycja = (int) strpos($content, $string2);

						$content = str_replace($string2, getTranslatedString($label, $modulename), $content);

						if ($record != 0 && ( $assigned_module == $modulename || $modulename == 'Users' )) {
							$content = str_replace($string, $value, $content);
						} else {
							$content = str_replace($string, '', $content);
						}
					}
				}
			}
		}

		return $content;
	}

	///////////////////////////////////////////
	///////////////////////////////////////////////
	function replaceCustomFields($content, $table, $record)
	{
		$db = PearDatabase::getInstance();

		$table = strtolower($table);
		$key = $table;
		if ($key == 'quotes')
			$key = 'quote';
		$key = $key . "id";

		$result = $db->pquery("SELECT * FROM vtiger_" . $table . "cf WHERE $key = $record", array(), true);
		$purchaseorderData = $db->fetch_array($result);

		foreach ($purchaseorderData as $key => $field) {
			$content = str_replace("#$key#", $field, $content);
		}

		if ($this->focus->column_fields['account_id']) {
			$result = $db->pquery("SELECT * FROM vtiger_accountscf 
									LEFT JOIN vtiger_account ON ( vtiger_account.accountid = vtiger_accountscf.accountid )
									WHERE vtiger_accountscf.accountid = " . $this->focus->column_fields['account_id'], Array(), true, true);
			$accountData = $db->fetch_array($result);

			foreach ($accountData as $key => $field) {
				$content = str_replace("#$key#", $field, $content);
			}
		}

		if ($this->focus->column_fields['vendor_id']) {
			$result = $db->pquery("SELECT * FROM vtiger_vendorcf 
									LEFT JOIN vtiger_vendor ON ( vtiger_vendor.vendorid = vtiger_vendor.vendorid )
									WHERE vtiger_vendorcf.vendorid = " . $this->focus->column_fields['vendor_id'], Array(), true, true);
			$vendorData = $db->fetch_array($result);

			foreach ($vendorData as $key => $field) {
				$content = str_replace("#$key#", $field, $content);
			}
		}

		if ($this->focus->column_fields['contact_id']) {
			$result = $db->pquery("SELECT * FROM vtiger_contactscf 
									LEFT JOIN vtiger_contactdetails ON ( vtiger_contactdetails.contactid = vtiger_contactscf.contactid )
									WHERE vtiger_contactscf.contactid = " . $this->focus->column_fields['contact_id'], Array(), true, true);
			$vendorData = $db->fetch_array($result);

			foreach ($vendorData as $key => $field) {
				$content = str_replace("#$key#", $field, $content);
			}
		}

		if ($this->focus->column_fields['leads_id']) {
			$result = $db->pquery("SELECT * FROM vtiger_leadscf 
									LEFT JOIN vtiger_leadaddress ON ( vtiger_leadaddress.leadaddressid = vtiger_leadscf.leadid )
									WHERE vtiger_leadscf.contactid = " . $this->focus->column_fields['leads_id'], Array(), true, true);
			$leadsData = $db->fetch_array($result);

			foreach ($leadsData as $key => $field) {
				$content = str_replace("#$key#", $field, $content);
			}
		}

		return $content;
	}

	function RunSpecialFunction($functionname, $content, $id, $module, $start, $dl, $templateid, $tcpdf)
	{
		$db = PearDatabase::getInstance();
		$sciezka = 'modules/OSSPdf/special_functions/' . $functionname . '.php';
		if (file_exists($sciezka)) {
			include_once( $sciezka );
			$tresc = $functionname($module, $id, $templateid, $content, $tcpdf);
			$content = substr_replace($content, $tresc, $start, 40 + $dl);
		} else {
			echo "<br/>Brak pliku z funkcją pod ścieżką: " . $sciezka;
		}

		return $content;
	}
	#################################################################################

	function ReportToPdf($reportid, $content, $ifonly, $recordid, $module, $start, $dl)
	{
		$db = PearDatabase::getInstance();
		require_once("modules/Reports/ReportRun.php");
		require_once( 'include/utils/utils.php' );
		$oReportRun = new ReportRun($reportid);
		$sql = $this->OSSGetSQLforReport($oReportRun, $ifonly, $module, $recordid, $reportid, '');

		$result = $db->query($sql, true);

		$modules_selected = array();
		$modules_selected[] = $oReportRun->primarymodule;
		if (!empty($oReportRun->secondarymodule)) {
			$sec_modules = split(":", $oReportRun->secondarymodule);
			for ($i = 0; $i < count($sec_modules); $i++) {
				$modules_selected[] = $sec_modules[$i];
			}
		}

		$y = $db->getFieldsCount($result);
		$report = '<table border="1" cellpadding="2"><tr width="500px" bgcolor="lightgrey">';
		$arrayHeaders = Array();

		for ($x = 0; $x < $y; $x++) {
			$fld = $db->columnMeta($result, $x);
			if (in_array($oReportRun->getLstringforReportHeaders($fld->name), $arrayHeaders)) {
				$headerLabel = str_replace("_", " ", $fld->name);
				$arrayHeaders[] = $headerLabel;
			} else {
				$headerLabel = str_replace($modules, " ", $oReportRun->getLstringforReportHeaders($fld->name));
				$headerLabel = str_replace("_", " ", $oReportRun->getLstringforReportHeaders($fld->name));
				$arrayHeaders[] = $headerLabel;
			}

			/* STRING TRANSLATION starts */
			$mod_name = split(' ', $headerLabel, 2);
			$moduleLabel = '';
			if (in_array($mod_name[0], $modules_selected)) {
				$moduleLabel = getTranslatedString($mod_name[0], $mod_name[0]);
			}

			if (!empty($oReportRun->secondarymodule)) {
				if ($moduleLabel != '') {
					$headerLabel_tmp = getTranslatedString($mod_name[1], $mod_name[0]);
				} else {
					$headerLabel_tmp = getTranslatedString($mod_name[0] . " " . $mod_name[1]);
				}
			} else {
				if ($moduleLabel != '') {
					$headerLabel_tmp = getTranslatedString($mod_name[1], $mod_name[0]);
				} else {
					$headerLabel_tmp = getTranslatedString($mod_name[0] . " " . $mod_name[1]);
				}
			}

			if ($headerLabel == $headerLabel_tmp)
				$headerLabel = getTranslatedString($headerLabel_tmp);
			else
				$headerLabel = $headerLabel_tmp;
			/* STRING TRANSLATION ends */

			$report .= "<td class='rptCellLabel'><small><b>" . $headerLabel . "</b></small></td>";
			// END
		}
		$report .= '</tr>';
		// END

		$noofrows = $db->num_rows($result);
		$custom_field_values = $db->fetch_array($result);
		$groupslist = $oReportRun->getGroupingList($oReportRun->reportid);

		$column_definitions = $db->getFieldsDefinition($result);
		do {
			$arraylists = Array();
			if (count($groupslist) == 1) {
				$newvalue = $custom_field_values[0];
			} else if (count($groupslist) == 2) {
				$newvalue = $custom_field_values[0];
				$snewvalue = $custom_field_values[1];
			} else if (count($groupslist) == 3) {
				$newvalue = $custom_field_values[0];
				$snewvalue = $custom_field_values[1];
				$tnewvalue = $custom_field_values[2];
			}

			if ($newvalue == "")
				$newvalue = "-";

			if ($snewvalue == "")
				$snewvalue = "-";

			if ($tnewvalue == "")
				$tnewvalue = "-";

			$report .= '<tr width="500px">';

			// Performance Optimization        
			// END

			for ($i = 0; $i < $y; $i++) {
				$fld = $db->columnMeta($result, $i);
				$fld_type = $column_definitions[$i]->type;
				$fieldvalue = getReportFieldValue($oReportRun, $picklistarray, $fld, $custom_field_values, $i);

				//check for Roll based pick list
				$temp_val = $fld->name;

				if ($fieldvalue == "") {
					$fieldvalue = "-";
				} else if ($fld->name == 'LBL_ACTION') {
					
				}

				if (($lastvalue == $fieldvalue) && $oReportRun->reporttype == "summary") {
					if ($report->reporttype == "summary") {
						$valtemplate .= "<td class='rptEmptyGrp'>&nbsp;</td>";
					} else {
						$report .= "<td class='rptData'>" . $fieldvalue . "</td>";
					}
				} else if (($secondvalue === $fieldvalue) && $oReportRun->reporttype == "summary") {
					if ($lastvalue === $newvalue) {
						$report .= "<td class='rptEmptyGrp'>&nbsp;</td>";
					} else {
						$report .= "<td class='rptGrpHead'>" . $fieldvalue . "</td>";
					}
				} else if (($thirdvalue === $fieldvalue) && $oReportRun->reporttype == "summary") {
					if ($secondvalue === $snewvalue) {
						$report .= "<td class='rptEmptyGrp'>&nbsp;</td>";
					} else {
						$report .= "<td class='rptGrpHead'>" . $fieldvalue . "</td>";
					}
				} else {
					if ($oReportRun->reporttype == "tabular") {
						$report .= "<td class='rptData'><small>" . $fieldvalue . "</small></td>";
					} else {
						$report .= "<td class='rptGrpHead'><small>" . $fieldvalue . "</small></td>";
					}
				}

				// Performance Optimization: If direct output is required
				// END
			}

			$report .= "</tr>";

			// Performance Optimization: If direct output is required
			//	echo $valtemplate;
			// END

			$lastvalue = $newvalue;
			$secondvalue = $snewvalue;
			$thirdvalue = $tnewvalue;
			$arr_val[] = $arraylists;
			set_time_limit($php_max_execution_time);
		} while ($custom_field_values = $db->fetch_array($result));

		$report .= "</table></br>";

		if ($ifonly == 1) {
			$content = substr_replace($content, $report, $start, 26 + $dl);
		} else {
			$content = substr_replace($content, $report, $start, 20 + $dl);
		}
		return $content;
	}
	#################################################################################
	## Funkcja pomocnicza mająca na celu pobieranie zapytania SQL z raportem
	## Główną przyczyną jej skopiowania jest chęć swobodnej manipulacji warunkiem WHERE

	function OSSGetSQLforReport(&$ReportRun, $ifonly, $module, $recordid, $reportid, $filtersql, $type = '', $chartReport = false)
	{
		include_once( 'include/utils/CommonUtils.php' );
		$columnlist = $ReportRun->getQueryColumnsList($reportid, $type);
		$groupslist = $ReportRun->getGroupingList($reportid);
		$groupTimeList = $ReportRun->getGroupByTimeList($reportid);
		$stdfilterlist = $ReportRun->getStdFilterList($reportid);
		$columnstotallist = $ReportRun->getColumnsTotal($reportid);
		$advfiltersql = $ReportRun->getAdvFilterSql($reportid);

		$ReportRun->totallist = $columnstotallist;
		$tab_id = getTabid($ReportRun->primarymodule);
		//Fix for ticket #4915.
		$selectlist = $columnlist;
		//columns list
		if (isset($selectlist)) {
			$selectedcolumns = implode(", ", $selectlist);
			if ($chartReport == true) {
				$selectedcolumns .= ", count(*) AS 'groupby_count'";
			}
		}
		//groups list
		if (isset($groupslist)) {
			$groupsquery = implode(", ", $groupslist);
		}
		if (isset($groupTimeList)) {
			$groupTimeQuery = implode(", ", $groupTimeList);
		}

		//standard list
		if (isset($stdfilterlist)) {
			$stdfiltersql = implode(", ", $stdfilterlist);
		}
		//columns to total list
		if (isset($columnstotallist)) {
			$columnstotalsql = implode(", ", $columnstotallist);
		}
		if ($stdfiltersql != "") {
			$wheresql = " and " . $stdfiltersql;
		}

		if (isset($filtersql) && $filtersql !== false) {
			$advfiltersql = $filtersql;
		}
		if ($advfiltersql != "") {
			$wheresql .= " and " . $advfiltersql;
		}

		$reportquery = $ReportRun->getReportsQuery($ReportRun->primarymodule, $type);

		// If we don't have access to any columns, let us select one column and limit result to shown we have not results
		// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad
		$allColumnsRestricted = false;

		if ($type == 'COLUMNSTOTOTAL') {
			if ($columnstotalsql != '') {
				$reportquery = "select " . $columnstotalsql . " " . $reportquery . " " . $wheresql;
			}
		} else {
			if ($selectedcolumns == '') {
				// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad
				$selectedcolumns = "''"; // "''" to get blank column name
				$allColumnsRestricted = true;
			}

			if (in_array($ReportRun->primarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder'))) {
				$selectedcolumns = ' distinct ' . $selectedcolumns;
			}
			$reportquery = "select DISTINCT " . $selectedcolumns . " " . $reportquery . " " . $wheresql;
		}

		if ($ifonly == 1) {
			require_once( 'modules/' . $module . '/' . $module . '.php' );
			$obiekt = new $module();
			$table = $obiekt->table_name;
			$index = $obiekt->table_index;
			$reportquery .= " AND $table.$index = '$recordid'";
		}

		$reportquery = listQueryNonAdminChange($reportquery, $ReportRun->primarymodule);

		if (trim($groupsquery) != "" && $type !== 'COLUMNSTOTOTAL') {
			if ($chartReport == true) {
				$reportquery .= "group by " . $ReportRun->GetFirstSortByField($reportid);
			} else {
				$reportquery .= " order by " . $groupsquery;
			}
		}

		// Prasad: No columns selected so limit the number of rows directly.
		if ($allColumnsRestricted) {
			$reportquery .= " limit 0";
		}

		preg_match('/&amp;/', $reportquery, $matches);
		if (!empty($matches)) {
			$report = str_replace('&amp;', '&', $reportquery);
			$reportquery = $ReportRun->replaceSpecialChar($report);
		}

		return $reportquery;
	}
	#################################################################################
	#################################################################################

	function replaceCompanyInformations($content)
	{
		$db = PearDatabase::getInstance();
		$add_query = "select * from vtiger_organizationdetails";
		$result = $db->pquery($add_query, array(), true);
		$fields = $db->fetch_array($result);
		foreach ($fields as $key => $field) {
			$content = str_replace("#company_$key#", $field, $content);
		}
		return $content;
	}
	#####################################################################

	function replaceReport($content, $reportid)
	{
		session_start();
		require_once("modules/Reports/ReportRun.php");
		require_once("modules/Reports/Reports.php");
		require_once( 'include/utils/utils.php' );
		$current_language = Users_Record_Model::getCurrentUserModel()->get('language');
		include("modules/OSSPdf/language/" . $current_language . ".lang.php");
		$language = $_SESSION['authenticated_user_language'] . '.lang.php';
		require_once("include/language/$language");
		$db = PearDatabase::getInstance();
		$oReport = new Reports($reportid);
		//Code given by Csar Rodrguez for Rwport Filter
		$filtercolumn = $_REQUEST["stdDateFilterField"];
		$filter = $_REQUEST["stdDateFilter"];
		$oReportRun = new ReportRun($reportid);

		$startdate = ($_REQUEST['startdate']);
		$enddate = ($_REQUEST['enddate']);
		if (!empty($startdate) && !empty($enddate) && $startdate != "0000-00-00" && $enddate != "0000-00-00") {
			$date = new DateTimeField($_REQUEST['startdate']);
			$endDate = new DateTimeField($_REQUEST['enddate']);
			$startdate = $date->getDBInsertDateValue(); //Convert the user date format to DB date format
			$enddate = $endDate->getDBInsertDateValue(); //Convert the user date format to DB date format
		}

		$filterlist = $oReportRun->RunTimeFilter($filtercolumn, $filter, $startdate, $enddate);

		############///////////////////////////////#########################
		$sql = $oReportRun->sGetSQLforReport($reportid, $filterlist);
		$result = $db->query($sql, true);

		$modules_selected = array();
		$modules_selected[] = $oReportRun->primarymodule;
		if (!empty($oReportRun->secondarymodule)) {
			$sec_modules = split(":", $oReportRun->secondarymodule);
			for ($i = 0; $i < count($sec_modules); $i++) {
				$modules_selected[] = $sec_modules[$i];
			}
		}

		$y = $db->getFieldsCount($result);
		$report = '<table border="1" cellpadding="2"><tr width="500px" bgcolor="lightgrey">';
		$arrayHeaders = Array();
		for ($x = 0; $x < $y; $x++) {
			$fld = $db->columnMeta($result, $x);
			if (in_array($oReportRun->getLstringforReportHeaders($fld->name), $arrayHeaders)) {
				$headerLabel = str_replace("_", " ", $fld->name);
				$arrayHeaders[] = $headerLabel;
			} else {
				$headerLabel = str_replace($modules, " ", $oReportRun->getLstringforReportHeaders($fld->name));
				$headerLabel = str_replace("_", " ", $oReportRun->getLstringforReportHeaders($fld->name));
				$arrayHeaders[] = $headerLabel;
			}
			/* STRING TRANSLATION starts */
			$mod_name = split(' ', $headerLabel, 2);
			$moduleLabel = '';
			if (in_array($mod_name[0], $modules_selected)) {
				$moduleLabel = getTranslatedString($mod_name[0], $mod_name[0]);
			}

			if (!empty($oReportRun->secondarymodule)) {
				if ($moduleLabel != '') {
					$headerLabel_tmp = getTranslatedString($mod_name[1], $mod_name[0]);
				} else {
					$headerLabel_tmp = getTranslatedString($mod_name[0] . " " . $mod_name[1]);
				}
			} else {
				if ($moduleLabel != '') {
					$headerLabel_tmp = getTranslatedString($mod_name[1], $mod_name[0]);
				} else {
					$headerLabel_tmp = getTranslatedString($mod_name[0] . " " . $mod_name[1]);
				}
			}

			if ($headerLabel == $headerLabel_tmp)
				$headerLabel = getTranslatedString($headerLabel_tmp);
			else
				$headerLabel = $headerLabel_tmp;

			/* STRING TRANSLATION ends */
			$report .= "<td class='rptCellLabel'><small><b>" . $headerLabel . "</b></small></td>";
			// Performance Optimization: If direct output is required                
			// END
		}

		$report .= '</tr>';

		// END

		$noofrows = $db->num_rows($result);
		$custom_field_values = $db->fetch_array($result);
		$groupslist = $oReportRun->getGroupingList($oReportRun->reportid);

		$column_definitions = $db->getFieldsDefinition($result);
		do {
			$arraylists = Array();
			if (count($groupslist) == 1) {
				$newvalue = $custom_field_values[0];
			} elseif (count($groupslist) == 2) {
				$newvalue = $custom_field_values[0];
				$snewvalue = $custom_field_values[1];
			} elseif (count($groupslist) == 3) {
				$newvalue = $custom_field_values[0];
				$snewvalue = $custom_field_values[1];
				$tnewvalue = $custom_field_values[2];
			}

			if ($newvalue == "")
				$newvalue = "-";

			if ($snewvalue == "")
				$snewvalue = "-";

			if ($tnewvalue == "")
				$tnewvalue = "-";

			$report .= '<tr width="500px">';
			// Performance Optimization        
			// END

			for ($i = 0; $i < $y; $i++) {
				$fld = $db->columnMeta($result, $i);
				$fld_type = $column_definitions[$i]->type;
				$fieldvalue = getReportFieldValue($oReportRun, $picklistarray, $fld, $custom_field_values, $i);

				//check for Roll based pick list
				$temp_val = $fld->name;

				if ($fieldvalue == "") {
					$fieldvalue = "-";
				} else if ($fld->name == 'LBL_ACTION') {
					
				}

				if (($lastvalue == $fieldvalue) && $oReportRun->reporttype == "summary") {
					if ($report->reporttype == "summary") {
						$valtemplate .= "<td class='rptEmptyGrp'>&nbsp;</td>";
					} else {
						$report .= "<td class='rptData'>" . $fieldvalue . "</td>";
					}
				} else if (($secondvalue === $fieldvalue) && $oReportRun->reporttype == "summary") {
					if ($lastvalue === $newvalue) {
						$report .= "<td class='rptEmptyGrp'>&nbsp;</td>";
					} else {
						$report .= "<td class='rptGrpHead'>" . $fieldvalue . "</td>";
					}
				} else if (($thirdvalue === $fieldvalue) && $oReportRun->reporttype == "summary") {
					if ($secondvalue === $snewvalue) {
						$report .= "<td class='rptEmptyGrp'>&nbsp;</td>";
					} else {
						$report .= "<td class='rptGrpHead'>" . $fieldvalue . "</td>";
					}
				} else {
					if ($oReportRun->reporttype == "tabular") {
						$report .= "<td class='rptData'><small>" . $fieldvalue . "</small></td>";
					} else {
						$report .= "<td class='rptGrpHead'><small>" . $fieldvalue . "</small></td>";
					}
				}
				// Performance Optimization: If direct output is required                
				// END
			}

			$report .= "</tr>";

			// Performance Optimization: If direct output is required
			//	echo $valtemplate;            
			// END

			$lastvalue = $newvalue;
			$secondvalue = $snewvalue;
			$thirdvalue = $tnewvalue;
			$arr_val[] = $arraylists;
			set_time_limit($php_max_execution_time);
		} while ($custom_field_values = $db->fetch_array($result));
		$report .= "</table></br>";

		$content = str_replace("#report_tag#", $report, $content);
		############///////////////////////////////#########################
		return $content;
	}
	######################################################################

	function replaceProductTable($content, $pdftype, $id)
	{
		$current_language = Users_Record_Model::getCurrentUserModel()->get('language');
		require_once( 'include/utils/utils.php' );
		include("modules/OSSPdf/language/" . $current_language . ".lang.php");
		include_once( "include/language/" . $current_language . ".lang.php");
		require_once( 'include/utils/CommonUtils.php' );
		require_once( 'include/fields/CurrencyField.php' );
		require_once( 'modules/' . $pdftype . '/' . $pdftype . '.php' );
		$db = PearDatabase::getInstance();

		$focus = new $pdftype();
		$focus->retrieve_entity_info($id, $pdftype);

		$currency_id = $focus->column_fields['currency_id'];
		$pobierz = $db->query("select currency_symbol from vtiger_currency_info where id = '$currency_id'", true);
		$symbol_waluty = $db->query_result($pobierz, 0, "currency_symbol");

		$focus->id = $focus->column_fields["record_id"];
		$associated_products = getAssociatedProducts($pdftype, $focus);
		$num_products = count($associated_products);

		$vat = array();
		$sales = array();
		$service = array();

		/////////////////////////////
		/// Tworzenie tabelki podsumowania VAT
		if ($focus->column_fields['hdnTaxType'] == 'group') {
			
		} else {
			for ($i = 1; $i <= count($associated_products); $i++) {
				$TotalAfterDiscount = $associated_products[$i]['totalAfterDiscount' . $i];
				foreach ($associated_products[$i]['taxes'] as $podatek) {
					if ($podatek['taxlabel'] == 'VAT') {
						$vat[$podatek['percentage']] += $TotalAfterDiscount * ($podatek['percentage'] / 100.0);
					}
					if ($podatek['taxlabel'] == 'Sales') {
						$sales[$podatek['percentage']] += $TotalAfterDiscount * ($podatek['percentage'] / 100.0);
					}
					if ($podatek['taxlabel'] == 'Service') {
						$service[$podatek['percentage']] += $TotalAfterDiscount * ($podatek['percentage'] / 100.0);
					}
				}
			}
		}
		///////////////////////////////////////////////////////////
		//This $final_details array will contain the final total, discount, Group Tax, S&H charge, S&H taxes
		$final_details = $associated_products[1]['final_details'];

		//getting the Net Total
		$price_subtotal = number_format($final_details["hdnSubTotal"], 2, '.', ',');

		//Final discount amount/percentage
		$discount_amount = $final_details["discount_amount_final"];
		$discount_percent = $final_details["discount_percentage_final"];

		if ($discount_amount != "") {
			$price_discount = number_format($discount_amount, 2, '.', ',');
			$price_disc = $discount_amount;
		} else if ($discount_percent != "") {
			//This will be displayed near Discount label - used in include/fpdf/templates/body.php
			$final_price_discount_percent = "(" . number_format($discount_percent, 2, '.', ',') . " %)";
			$price_discount = number_format((($discount_percent * $final_details["hdnSubTotal"]) / 100), 2, '.', ',');
			$price_disc = $discount_percent * $final_details["hdnSubTotal"];
		} else
			$price_discount = "0.00";

		//Grand Total
		$price_total = number_format($final_details["grandTotal"], 2, '.', ',');


		//To calculate the group tax amount
		if ($final_details['taxtype'] == 'group') {
			$group_tax_total = $final_details['tax_totalamount'];
			$price_salestax = number_format($group_tax_total, 2, '.', ',');

			$group_total_tax_percent = '0.00';
			$group_tax_details = $final_details['taxes'];
			for ($i = 0; $i < count($group_tax_details); $i++) {
				$group_total_tax_percent = $group_total_tax_percent + $group_tax_details[$i]['percentage'];
			}
		}
		$podatek_grupowy = ( $final_details["hdnSubTotal"] - $price_disc ) * ( $group_total_tax_percent / 100.0 );

		$prod_line = array();
		$lines = 0;

		//This is to get all prodcut details as row basis
		for ($i = 1, $j = $i - 1; $i <= $num_products; $i++, $j++) {
			$product_name[$i] = $associated_products[$i]['productName' . $i];
			$subproduct_name[$i] = split("<br>", $associated_products[$i]['subprod_names' . $i]);
			$comment[$i] = $associated_products[$i]['comment' . $i];
			$product_id[$i] = $associated_products[$i]['hdnProductId' . $i];
			$qty[$i] = $associated_products[$i]['qty' . $i];
			$unit_price[$i] = number_format($associated_products[$i]['unitPrice' . $i], 2, '.', ',');
			$list_price[$i] = $associated_products[$i]['listPrice' . $i]; // number_format($associated_products[$i]['listPrice'.$i],2,'.',',');
			$list_pricet[$i] = $associated_products[$i]['listPrice' . $i];
			$discount_total[$i] = $associated_products[$i]['discountTotal' . $i];
			//aded for 5.0.3 pdf changes
			$product_code[$i] = $associated_products[$i]['hdnProductcode' . $i];
			$taxable_total = $qty[$i] * $list_pricet[$i] - $discount_total[$i];
			$producttotal = $taxable_total;
			$total_taxes = '0.00';
			if ($focus->column_fields["hdnTaxType"] == "individual") {
				$total_tax_percent = '0.00';

				//This loop is to get all tax percentage and then calculate the total of all taxes
				for ($tax_count = 0; $tax_count < count($associated_products[$i]['taxes']); $tax_count++) {
					$tax_percent = $associated_products[$i]['taxes'][$tax_count]['percentage'];
					$total_tax_percent = $total_tax_percent + $tax_percent;
					$tax_amount = (($taxable_total * $tax_percent) / 100);
					$total_taxes = $total_taxes + $tax_amount;
				}

				$producttotal = $taxable_total + $total_taxes;
				$product_line[$j]["tax_percentage"] = $total_tax_percent;
				$product_line[$j]["Tax"] = $total_taxes;
				$price_salestax += $total_taxes;
			}

			$prod_total[$i] = $producttotal;
			$product_line[$j]["Product Code"] = $product_code[$i];
			$product_line[$j]["Qty"] = $qty[$i];
			$product_line[$j]["Price"] = $list_price[$i];
			$product_line[$j]["Discount"] = $discount_total[$i];
			$product_line[$j]["Total"] = $prod_total[$i];

			$lines++;

			$product_line[$j]["Product Name"] = decode_html($product_name[$i]);

			$prod_line[$j] = 1;
			for ($count = 0; $count < count($subproduct_name[$i]); $count++) {
				if ($lines % 12 != 0) {
					$product_line[$j]["Product Name"] .= "\n" . decode_html($subproduct_name[$i][$count]);
					$prod_line[$j] ++;
				} else {
					$j++;
					$product_line[$j]["Product Name"] = decode_html($product_name[$i]);
					$product_line[$j]["Product Name"] .= "\n" . decode_html($subproduct_name[$i][$count]);
					$prod_line[$j] = 2;
					$lines++;
				}
				$lines++;
			}
			if ($comment[$i] != '') {
				$product_line[$j]["Product Name"] .= "\n" . decode_html($comment[$i]);
				$prod_line[$j] ++;
				$lines++;
			}
		}

		$price_salestax = number_format($price_salestax, 2, '.', ',');
		if ($final_details['taxtype'] == 'group') {
			$header = Array($mod_strings['LBL_nr'], $mod_strings['LBL_productname'], $mod_strings['LBL_Quantity'], $mod_strings['LBL_price'], $mod_strings['LBL_netto'], $mod_strings['LBL_rabat'], $mod_strings['LBL_brutto']);
		} else {
			$header = Array($mod_strings['LBL_nr'], $mod_strings['LBL_productname'], $mod_strings['LBL_Quantity'], $mod_strings['LBL_price'], $mod_strings['LBL_netto'], $mod_strings['LBL_rabat'], $mod_strings['LBL_vat'], $mod_strings['LBL_vat_waluta'] . " (" . $symbol_waluty . ")", $mod_strings['LBL_brutto']);
		}

		$data = Array();
		$i = 0;
		foreach ($product_line as $item) {
			$currfield = new CurrencyField($item["tax_percentage"]);
			$tax_percentage = $currfield->getDisplayValue();
			//echo number_format( (float)$item["tax_percentage"], 2, ",", " " );//$item["tax_percentage"];
			if ($final_details['taxtype'] == 'group') {
				$data[$i++] = Array($i, $item['Product Name'], $item['Qty'], $item['Price'], $item['Price'] * $item['Qty'], $item['Discount'], $item['Total']);
			} else {
				$data[$i++] = Array($i, $item['Product Name'], $item['Qty'], $item['Price'], $item['Price'] * $item['Qty'], $item['Discount'], $tax_percentage, $item['Tax'], $item['Total']);
			}
		}

		if ($final_details['taxtype'] == 'group') {
			$width = array(30, 290, 35, 45, 45, 45, 45,);
			$align = array("center", "center", "center", "center", "center", "center", "center");
			$format = array(0, "s", 0, 2, 2, 2, 2);
		} else {
			$width = array(30, 200, 35, 45, 45, 45, 45, 45, 45);
			$align = array("center", "center", "center", "center", "center", "center", "center", "center", "center");
			$format = array(0, "s", 0, 2, 2, 2, 2, 2, 2);
		}

		$product_table = '<table border="1" cellpadding="2">';
		$product_table .= '<tr valign="middle">';
		for ($i = 0; $i < count($header); $i++) {
			$product_table .= '<td width="' . $width[$i] . '" height="20" align="' . $align[$i] . '"><b><small>' . $header[$i] . '</small></b></td>';
		}
		$product_table .= '</tr>';

		$align = array("center", "left", "center", "center", "center", "center", "center", "center", "center");
		//Data		

		foreach ($data as $row) {
			$product_table .= '<tr>';
			$i = 0;
			foreach ($row as $item) {
				$sum[$i] += (float) $item;
				if ($format[$i] != 's') {
					$currfield = new CurrencyField($item);
					$item = $currfield->getDisplayValue();
					//$item = //number_format( (float)$item, (int)$format[$i], ",", " " );
				} else {
					$itarr = explode("\n\n", $item);
					$item = '<b>' . $itarr[0] . '</b><br/><small>' . $itarr[1] . '</small>';
				}
				$product_table .= '<td width="' . $width[$i] . '" align="' . $align[$i++] . '"><small>' . $item . '</small></td>';
			}
			$product_table .= '</tr>';
		}
		$product_table .= "</table>";
		$i = 0;

		if (count($vat) > 0 || count($sales) > 0 || count($service) > 0) {
			$product_table .= '<table width="535px" border="1" cellpadding="2">';
			$product_table .= '<tr height="10"><td align="right" valign="middle"><small><b>' . getTranslatedString('VAT_SUMMARY', 'OSSPdf') . '</b></small></td></tr>';
			$product_table .= '</table>';

			if (count($vat) > 0) {
				$product_table .= '<table width="535px" border="1" cellpadding="2">';
				foreach ($vat as $podatek => $suma) {
					$currfield = new CurrencyField($suma);
					$suma = $currfield->getDisplayValue();
					$product_table .= '<tr height="10"><td align="right" valign="middle"><small><b>' . $app_strings['LBL_VAT'] . '(' . number_format($podatek, 2, '.', ',') . '%) :</b> ' . $suma . ' ' . $symbol_waluty . '</small></td></tr>';
				}
				$product_table .= '</table>';
			}

			if (count($sales) > 0) {
				$product_table .= '<table width="535px" border="1" cellpadding="2">';
				foreach ($sales as $podatek => $suma) {
					$currfield = new CurrencyField($suma);
					$suma = $currfield->getDisplayValue();
					$product_table .= '<tr height="10"><td align="right" valign="middle"><small><b>' . $app_strings['LBL_SALES'] . '(' . number_format($podatek, 2, '.', ',') . '%) :</b> ' . $suma . ' ' . $symbol_waluty . '</small></td></tr>';
				}
				$product_table .= '</table>';
			}

			if (count($service) > 0) {
				$product_table .= '<table width="535px" border="1" cellpadding="2">';
				foreach ($service as $podatek => $suma) {
					$currfield = new CurrencyField($suma);
					$suma = $currfield->getDisplayValue();
					$product_table .= '<tr height="10"><td align="right" valign="middle"><small><b>' . $app_strings['LBL_SERVICE'] . '(' . number_format($podatek, 2, '.', ',') . '%) :</b> ' . $suma . ' ' . $symbol_waluty . '</small></td></tr>';
				}
				$product_table .= '</table>';
			}
		}

		$mod = strtolower($pdftype);
		if ($mod == 'quotes') {
			$idcol = "quoteid";
		} else {
			$idcol = $mod . "id";
		}

		$sql = "SELECT discount_percent, discount_amount, subtotal, total FROM vtiger_$mod WHERE $idcol = " . $this->id;
		$result = $db->query($sql, true);
		$grand_total = $db->query_result($result, 0, 'total');
		$subtotal = $db->query_result($result, 0, "subtotal");
		$discount_percent = $db->query_result($result, 0, 'discount_percent');
		$discount_amount = $db->query_result($result, 0, 'discount_amount');

		if ($discount_percent != 0) {
			$discount = $subtotal * ( $discount_percent / 100.0 );
		} else {
			$discount = $discount_amount;
		}

		$currfield = new CurrencyField($grand_total);
		$grand_total = $currfield->getDisplayValue();
		$currfield = new CurrencyField($subtotal);
		$subtotal = $currfield->getDisplayValue();
		$currfield = new CurrencyField($discount);
		$discount = $currfield->getDisplayValue();

		$product_table .= '<table width="535px" border="1" cellpadding="2">
		<tr height="10"><td align="right" valign="middle"><small><b>' . getTranslatedString('SUMMARY', 'OSSPdf') . '</b></small></td></tr></table>';
		if ($final_details['taxtype'] == 'group') {
			$product_table .= '<table width="535px" border="1" cellpadding="2">';
			$product_table .= '<tr height="10"><td align="right" valign="middle"><small><b>' . $app_strings['LBL_VAT'] . '(' . number_format($group_total_tax_percent, 2, '.', ',') . '%) :</b> ' . $podatek_grupowy . ' ' . $symbol_waluty . '</small></td></tr>';
			$product_table .= '</table>';
		}
		$product_table .= '<table width="535px" border="1" cellpadding="2">

        <tr height="10"><td align="right" valign="middle"><small><b>' . getTranslatedString('Net Total', "OSSPdf") . '</b> : ' . $subtotal . '</small></td></tr>
        <tr valign="middle"><td align="right"><small><b>' . getTranslatedString("Discount Amount", "OSSPdf") . '</b> : ' . $discount . '</small></td></tr>
        <tr valign="middle"><td align="right"><small><b>' . getTranslatedString("Grand Total", "OSSPdf") . '</b> : ' . $grand_total . ' (' . $symbol_waluty . ')</small></td></tr>
		</table><br/>';

		$content = str_replace("#product_table#", $product_table, $content);
		$currfield = new CurrencyField($grand_total);
		$grand_total = $currfield->getDBInsertedValue($grand_total);
		$kwota = $this->slownie($grand_total);

		$content = str_replace("#amount_words#", $kwota, $content);
		return $content;
	}
	##################################################################################

	function replaceProductTableNP($content, $pdftype, $id)
	{
		$current_language = Users_Record_Model::getCurrentUserModel()->get('language');
		require_once( 'include/utils/utils.php' );
		include("modules/OSSPdf/language/" . $current_language . ".lang.php");
		require_once( 'include/utils/CommonUtils.php' );
		require_once( 'include/fields/CurrencyField.php' );
		require_once( 'modules/' . $pdftype . '/' . $pdftype . '.php' );
		$db = PearDatabase::getInstance();
		$focus = new $pdftype();
		$focus->retrieve_entity_info($id, $pdftype);
		$focus->id = $focus->column_fields["record_id"];
		$associated_products = getAssociatedProducts($pdftype, $focus);

		$num_products = count($associated_products);

		$currency_id = $focus->column_fields['currency_id'];
		$pobierz = $db->query("select currency_symbol from vtiger_currency_info where id = '$currency_id'", true);
		$symbol_waluty = $db->query_result($pobierz, 0, "currency_symbol");

		//This $final_details array will contain the final total, discount, Group Tax, S&H charge, S&H taxes
		$final_details = $associated_products[1]['final_details'];

		//getting the Net Total
		$price_subtotal = number_format($final_details["hdnSubTotal"], 2, '.', ',');

		//Final discount amount/percentage
		$discount_amount = $final_details["discount_amount_final"];
		$discount_percent = $final_details["discount_percentage_final"];

		if ($discount_amount != "")
			$price_discount = number_format($discount_amount, 2, '.', ',');
		else if ($discount_percent != "") {
			//This will be displayed near Discount label - used in include/fpdf/templates/body.php
			$final_price_discount_percent = "(" . number_format($discount_percent, 2, '.', ',') . " %)";
			$price_discount = number_format((($discount_percent * $final_details["hdnSubTotal"]) / 100), 2, '.', ',');
		} else
			$price_discount = "0.00";

		//Grand Total
		$price_total = number_format($final_details["grandTotal"], 2, '.', ',');


		//To calculate the group tax amount
		if ($final_details['taxtype'] == 'group') {
			$group_tax_total = $final_details['tax_totalamount'];
			$price_salestax = number_format($group_tax_total, 2, '.', ',');

			$group_total_tax_percent = '0.00';
			$group_tax_details = $final_details['taxes'];
			for ($i = 0; $i < count($group_tax_details); $i++) {
				$group_total_tax_percent = $group_total_tax_percent + $group_tax_details[$i]['percentage'];
			}
		}

		$prod_line = array();
		$lines = 0;

		//This is to get all prodcut details as row basis
		for ($i = 1, $j = $i - 1; $i <= $num_products; $i++, $j++) {
			$product_name[$i] = $associated_products[$i]['productName' . $i];
			$subproduct_name[$i] = split("<br>", $associated_products[$i]['subprod_names' . $i]);
			//$prod_description[$i] = $associated_products[$i]['productDescription'.$i];
			$comment[$i] = $associated_products[$i]['comment' . $i];
			$product_id[$i] = $associated_products[$i]['hdnProductId' . $i];
			$qty[$i] = $associated_products[$i]['qty' . $i];
			$unit_price[$i] = number_format($associated_products[$i]['unitPrice' . $i], 2, '.', ',');
			$list_price[$i] = $associated_products[$i]['listPrice' . $i]; // number_format($associated_products[$i]['listPrice'.$i],2,'.',',');
			$list_pricet[$i] = $associated_products[$i]['listPrice' . $i];
			$discount_total[$i] = $associated_products[$i]['discountTotal' . $i];

			//aded for 5.0.3 pdf changes
			$product_code[$i] = $associated_products[$i]['hdnProductcode' . $i];

			$taxable_total = $qty[$i] * $list_pricet[$i] - $discount_total[$i];
			$producttotal = $taxable_total;
			$total_taxes = '0.00';
			if ($focus->column_fields["hdnTaxType"] == "individual") {
				$total_tax_percent = '0.00';
				//This loop is to get all tax percentage and then calculate the total of all taxes
				for ($tax_count = 0; $tax_count < count($associated_products[$i]['taxes']); $tax_count++) {
					$tax_percent = $associated_products[$i]['taxes'][$tax_count]['percentage'];
					$total_tax_percent = $total_tax_percent + $tax_percent;
					$tax_amount = (($taxable_total * $tax_percent) / 100);
					$total_taxes = $total_taxes + $tax_amount;
				}
				$producttotal = $taxable_total + $total_taxes;
				$product_line[$j]["tax_percentage"] = $total_tax_percent;
				$product_line[$j]["Tax"] = $total_taxes;
				$price_salestax += $total_taxes;
			}

			$prod_total[$i] = $producttotal;
			$product_line[$j]["Product Code"] = $product_code[$i];
			$product_line[$j]["Qty"] = $qty[$i];
			$product_line[$j]["Price"] = $list_price[$i];
			$product_line[$j]["Discount"] = $discount_total[$i];
			$product_line[$j]["Total"] = $prod_total[$i];

			$lines++;
			$product_line[$j]["Product Name"] = decode_html($product_name[$i]);

			$prod_line[$j] = 1;
			for ($count = 0; $count < count($subproduct_name[$i]); $count++) {
				if ($lines % 12 != 0) {
					$product_line[$j]["Product Name"] .= "\n" . decode_html($subproduct_name[$i][$count]);
					$prod_line[$j] ++;
				} else {
					$j++;
					$product_line[$j]["Product Name"] = decode_html($product_name[$i]);
					$product_line[$j]["Product Name"] .= "\n" . decode_html($subproduct_name[$i][$count]);
					$prod_line[$j] = 2;
					$lines++;
				}
				$lines++;
			}
			if ($comment[$i] != '') {
				$product_line[$j]["Product Name"] .= "\n" . decode_html($comment[$i]);
				$prod_line[$j] ++;
				$lines++;
			}
		}

		$price_salestax = number_format($price_salestax, 2, '.', ',');
		$header = Array($mod_strings['LBL_nr'], $mod_strings['LBL_productname'], $mod_strings['LBL_Quantity'], $mod_strings['LBL_price'], $mod_strings['LBL_netto'], $mod_strings['LBL_rabat'], $mod_strings['LBL_vat'], $mod_strings['LBL_brutto']);

		$data = Array();
		$i = 0;
		foreach ($product_line as $item) {
			$data[$i++] = Array($i, $item['Product Name'], $item['Qty'], $item['Price'], $item['Price'] * $item['Qty'], $item['Discount'], 'NP', $item['Total']);
		}

		$width = array(30, 245, 35, 45, 45, 45, 45, 45);
		$align = array("center", "center", "center", "center", "center", "center", "center", "center");
		$format = array(0, "s", 0, 2, 2, 2, "np", 2);

		$product_table = '<table border="1" cellpadding="2">';
		$product_table .= '<tr valign="middle">';
		for ($i = 0; $i < count($header); $i++) {
			$product_table .= '<td width="' . $width[$i] . '" height="20" align="' . $align[$i] . '"><b><small>' . $header[$i] . '</small></b></td>';
		}
		$product_table .= '</tr>';

		$align = array("center", "left", "center", "center", "center", "center", "center", "center", "center");

		//Data
		foreach ($data as $row) {
			$product_table .= '<tr>';
			$i = 0;
			foreach ($row as $item) {
				$sum[$i] += (float) $item;
				if ($format[$i] == 's') {
					$itarr = explode("\n\n", $item);
					$item = '<b>' . $itarr[0] . '</b><br/><small>' . $itarr[1] . '</small>';
				} elseif ($format[$i] == 'np') {
					
				} else {
					$currfield = new CurrencyField($item);
					$item = $currfield->getDisplayValue();
				}
				$product_table .= '<td width="' . $width[$i] . '" align="' . $align[$i++] . '"><small>' . $item . '</small></td>';
			}
			$product_table .= '</tr>';
		}
		$product_table .= "</table>";
		$i = 0;


		$mod = strtolower($pdftype);
		if ($mod == 'quotes') {
			$idcol = "quoteid";
		} else {
			$idcol = $mod . "id";
		}

		$sql = "SELECT discount_percent, discount_amount, subtotal, total FROM vtiger_$mod WHERE $idcol = " . $this->id;
		$result = $db->query($sql, true);
		$grand_total = $db->query_result($result, 0, 'total');
		$subtotal = $db->query_result($result, 0, "subtotal");
		$discount_percent = $db->query_result($result, 0, 'discount_percent');
		$discount_amount = $db->query_result($result, 0, 'discount_amount');

		if ($discount_percent != 0) {
			$discount = $subtotal * ( $discount_percent / 100.0 );
		} else {
			$discount = $discount_amount;
		}

		$currfield = new CurrencyField($grand_total);
		$grand_total = $currfield->getDisplayValue();
		$currfield = new CurrencyField($subtotal);
		$subtotal = $currfield->getDisplayValue();
		$currfield = new CurrencyField($discount);
		$discount = $currfield->getDisplayValue();

		$product_table .= '<table width="535px" border="1" cellpadding="2">
			<tr height="10"><td align="right" valign="middle"><small><b>' . getTranslatedString('Net Total', "OSSPdf") . '</b> : ' . $subtotal . '</small></td></tr>
			<tr valign="middle"><td align="right"><small><b>' . getTranslatedString("Discount Amount", "OSSPdf") . '</b> : ' . $discount . '</small></td></tr>
			<tr valign="middle"><td align="right"><small><b>' . getTranslatedString("Grand Total", "OSSPdf") . '</b> : ' . $grand_total . ' (' . $symbol_waluty . ')</small></td></tr>
		</table><br/>';

		$content = str_replace("#product_tableNP#", $product_table, $content);
		$currfield = new CurrencyField($grand_total);
		$grand_total = $currfield->getDBInsertedValue($grand_total);
		$kwota = $this->slownie($grand_total);

		$content = str_replace("#amount_words#", $kwota, $content);
		return $content;
	}
	##################################################################################		 

	function policz($l, $t1, $t2, $t3)
	{
		$j = array(
			vtranslate('LBL_empty', 'OSSPdf'), vtranslate('LBL_1', 'OSSPdf'), vtranslate('LBL_2', 'OSSPdf'),
			vtranslate('LBL_3', 'OSSPdf'), vtranslate('LBL_4', 'OSSPdf'), vtranslate('LBL_5', 'OSSPdf'),
			vtranslate('LBL_6', 'OSSPdf'), vtranslate('LBL_7', 'OSSPdf'), vtranslate('LBL_8', 'OSSPdf'),
			vtranslate('LBL_9', 'OSSPdf'), vtranslate('LBL_10', 'OSSPdf'), vtranslate('LBL_11', 'OSSPdf'),
			vtranslate('LBL_12', 'OSSPdf'), vtranslate('LBL_13', 'OSSPdf'), vtranslate('LBL_14', 'OSSPdf'),
			vtranslate('LBL_15', 'OSSPdf'), vtranslate('LBL_16', 'OSSPdf'), vtranslate('LBL_17', 'OSSPdf'),
			vtranslate('LBL_18', 'OSSPdf'), vtranslate('LBL_19', 'OSSPdf')
		);

		$d = array(
			vtranslate('LBL_empty', 'OSSPdf'), vtranslate('LBL_empty', 'OSSPdf'), vtranslate('LBL_20', 'OSSPdf'),
			vtranslate('LBL_30', 'OSSPdf'), vtranslate('LBL_40', 'OSSPdf'), vtranslate('LBL_50', 'OSSPdf'),
			vtranslate('LBL_60', 'OSSPdf'), vtranslate('LBL_70', 'OSSPdf'), vtranslate('LBL_80', 'OSSPdf'),
			vtranslate('LBL_90', 'OSSPdf')
		);

		$s = array(
			vtranslate('LBL_empty', 'OSSPdf'), vtranslate('LBL_100', 'OSSPdf'), vtranslate('LBL_200', 'OSSPdf'),
			vtranslate('LBL_300', 'OSSPdf'), vtranslate('LBL_400', 'OSSPdf'), vtranslate('LBL_500', 'OSSPdf'),
			vtranslate('LBL_600', 'OSSPdf'), vtranslate('LBL_700', 'OSSPdf'), vtranslate('LBL_800', 'OSSPdf'),
			vtranslate('LBL_900', 'OSSPdf')
		);

		$txt = $s[0 + substr($l, 0, 1)];

		if (substr($l, 1, 2) < 20)
			$txt .= $j[0 + substr($l, 1, 2)];
		else
			$txt .= $d[0 + substr($l, 1, 1)] . $j[0 + substr($l, 2, 1)];

		if ($l == 1)
			$txt .= "$t1 ";
		else {
			if ((substr($l, 2, 1) == 2 or substr($l, 2, 1) == 3 or substr($l, 2, 1) == 4)
				and ( substr($l, 1, 2) > 20 or substr($l, 1, 2) < 10))
				$txt .= "$t2 ";
			else
				$txt .= "$t3 ";
		}

		return $txt;
	}

	function slownie($liczba, $kod_waluty)
	{
		$liczba = str_replace(",", ".", $liczba);
		$liczba = number_format($liczba, 2, ",", "");
		$kwota = explode(",", $liczba);
		$kwotazl = sprintf("%012d", $kwota[0]);
		$kwotagr = sprintf("%03d", $kwota[1]);


		if ($kwotazl > 999999999)
			$txt .= $this->policz(substr($kwotazl, 0, 3), vtranslate('LBL_miliard_1', 'OSSPdf'), vtranslate('LBL_miliard_2', 'OSSPdf'), vtranslate('LBL_miliard_3', 'OSSPdf'));
		if ($kwotazl > 999999)
			$txt .= $this->policz(substr($kwotazl, 3, 3), vtranslate('LBL_milion_1', 'OSSPdf'), vtranslate('LBL_milion_2', 'OSSPdf'), vtranslate('LBL_milion_3', 'OSSPdf'));
		if ($kwotazl > 999)
			$txt .= $this->policz(substr($kwotazl, 6, 3), vtranslate('LBL_tysiac_1', 'OSSPdf'), vtranslate('LBL_tysiac_2', 'OSSPdf'), vtranslate('LBL_tysiac_3', 'OSSPdf'));

		switch ($kod_waluty) {
			case "USD":
				$liczenik1 = vtranslate('LBL_USD_1', 'OSSPdf');
				$liczebnik2 = vtranslate('LBL_USD_2', 'OSSPdf');
				$liczebnik3 = vtranslate('LBL_USD_3', 'OSSPdf');
				$liczebnik4 = vtranslate('LBL_USD_4', 'OSSPdf');
				$liczebnik5 = vtranslate('LBL_USD_5', 'OSSPdf');
				$liczebnik6 = vtranslate('LBL_USD_6', 'OSSPdf');
				$liczebnik7 = vtranslate('LBL_USD_7', 'OSSPdf');
				$liczebnik8 = vtranslate('LBL_USD_8', 'OSSPdf');
				break;

			case "PLN":
				$liczenik1 = vtranslate('LBL_zloty_1', 'OSSPdf');
				$liczebnik2 = vtranslate('LBL_zloty_2', 'OSSPdf');
				$liczebnik3 = vtranslate('LBL_zloty_3', 'OSSPdf');
				$liczebnik4 = vtranslate('LBL_grosz_1', 'OSSPdf');
				$liczebnik5 = vtranslate('LBL_grosz_2', 'OSSPdf');
				$liczebnik6 = vtranslate('LBL_grosz_3', 'OSSPdf');
				$liczebnik7 = vtranslate('LBL_zero_1', 'OSSPdf');
				$liczebnik8 = vtranslate('LBL_zero_2', 'OSSPdf');
				break;

			case "GBP":
				$liczenik1 = vtranslate('LBL_gbp_1', 'OSSPdf');
				$liczebnik2 = vtranslate('LBL_gbp_2', 'OSSPdf');
				$liczebnik3 = vtranslate('LBL_gbp_3', 'OSSPdf');
				$liczebnik4 = vtranslate('LBL_gbp_4', 'OSSPdf');
				$liczebnik5 = vtranslate('LBL_gbp_5', 'OSSPdf');
				$liczebnik6 = vtranslate('LBL_gbp_6', 'OSSPdf');
				$liczebnik7 = vtranslate('LBL_gbp_7', 'OSSPdf');
				$liczebnik8 = vtranslate('LBL_gbp_8', 'OSSPdf');
				break;

			case "EUR":
				$liczenik1 = vtranslate('LBL_EUR_1', 'OSSPdf');
				$liczebnik2 = vtranslate('LBL_EUR_2', 'OSSPdf');
				$liczebnik3 = vtranslate('LBL_EUR_3', 'OSSPdf');
				$liczebnik4 = vtranslate('LBL_EUR_4', 'OSSPdf');
				$liczebnik5 = vtranslate('LBL_EUR_5', 'OSSPdf');
				$liczebnik6 = vtranslate('LBL_EUR_6', 'OSSPdf');
				$liczebnik7 = vtranslate('LBL_EUR_7', 'OSSPdf');
				$liczebnik8 = vtranslate('LBL_EUR_8', 'OSSPdf');
				break;
		}

		if ($kwotazl > 0)
			$txt .= $this->policz(substr($kwotazl, 9, 3), $liczebnik1, $liczebnik2, $liczebnik3);

		if ($kwotazl == 0)
			$txt = $liczebnik7;

		$txt .= vtranslate('LBL_and', 'OSSPdf');
		if ($kwotagr > 0) {
			$txt .= $this->policz($kwotagr, $liczebnik4, $liczebnik5, $liczebnik6);
		}

		if ($kwotagr == 0)
			$txt .= $liczebnik8;

		return $txt;
	}

	function getVersion()
	{
		$db = PearDatabase::getInstance();
		$currentModule = vglobal('currentModule');
		$result = $db->pquery("SELECT version FROM vtiger_tab WHERE name = ?", Array($currentModule), true, true);
		return $db->query_result($result, 'version', 0);
	}
}
