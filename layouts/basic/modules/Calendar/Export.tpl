{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div id="exportContainer" class='modelContainer modal fade '>
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h3 id="exportCalendarHeader" class="modal-title">{\App\Language::translate('LBL_EXPORT_RECORDS', $MODULE)}</h3>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="exportForm" class="form-horizontal" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
					<input type="hidden" name="action" value="ExportData" />
					<input type="hidden" name="viewname" value="{$VIEWID}" />
					<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
					<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
					<input type="hidden" id="page" name="page" value="{$PAGE}" />
					<div name='exportCalendar'>
						<input type="hidden" value="export" name="view" />
						<div class="modal-body tabbable">
							<div class="tab-content massEditContent">
								<table class="massEditTable table table-bordered">
									<tr>
										<td class="fieldLabel alignMiddle">
											<input type="radio" name="exportCalendar" value = "iCal" checked /> {\App\Language::translate('ICAL_FORMAT', $MODULE)}
										</td>
										<td class="fieldValue">
											<input type="text" name="filename" class="form-control" id="filename" value='yetiforce.calendar' />
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success" type="submit" name="saveButton" onclick="app.hideModalWindow();" ><strong>{\App\Language::translate('LBL_EXPORT', $MODULE)}</strong></button>
						<button type="reset" class="btn btn-warning cancelLink" type="reset" data-dismiss="modal">{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
