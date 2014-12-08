{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
<div id="exportContainer" class='modelContainer'>
	<div class="modal-header">
		<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
		<h3 id="exportCalendarHeader">{vtranslate('LBL_EXPORT_RECORDS', $MODULE)}</h3>
	</div>
	<form id="exportForm" class="form-horizontal row-fluid" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="ExportData" />
		<input type="hidden" name="viewname" value="{$VIEWID}" />
		<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
		<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
		<input type="hidden" id="page" name="page" value="{$PAGE}" />
		<div name='exportCalendar'>
			<input type="hidden" value="export" name="view">
			<div class="modal-body tabbable">
				<div class="tab-content massEditContent">
					<table class="massEditTable table table-bordered">
						<tr>
							<td class="fieldLabel alignMiddle">
								<input type='radio' name='exportCalendar' value = 'iCal' checked /> {vtranslate('ICAL_FORMAT', $MODULE)}
							</td>
							<td class="fieldValue">
								<input type='text' name='filename' id='filename' size='25' value='vtiger.calendar'/>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-success" type="submit" name="saveButton" onclick="app.hideModalWindow();" ><strong>{vtranslate('LBL_EXPORT', $MODULE)}</strong></button>
	</div>
	</form>
</div>
{/strip}
