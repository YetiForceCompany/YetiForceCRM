{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Calendar-ImportResult -->
	<div>
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<table class="col-12 paddingLRZero no-margin searchUIBasic well">
			<tr>
				<td class="font-x-large" align="left" colspan="2">
					<strong>
						{\App\Language::translate('LBL_IMPORT', $MODULE_NAME)} {\App\Language::translate($MODULE_NAME, $MODULE_NAME)}
						&nbsp;- {\App\Language::translate('LBL_RESULT', $MODULE_NAME)}
					</strong>
				</td>
			</tr>
			{if !empty($ERROR_MESSAGE)}
				<tr>
					<td class="style1" align="left" colspan="2">
						{$ERROR_MESSAGE}
					</td>
				</tr>
			{else}
				<tr>
					<td valign="top">
						<table cellpadding="5" cellspacing="0" align="center" width="100%"
							class="dvtSelectedCell thickBorder importContents">
							<tr>
								<td>{\App\Language::translate('LBL_TOTAL_EVENTS_IMPORTED', $MODULE_NAME)}</td>
								<td width="10%">:</td>
								<td width="30%">{$SUCCESS_EVENTS}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_TOTAL_EVENTS_SKIPPED', $MODULE_NAME)}</td>
								<td width="10%">:</td>
								<td width="30%">{$SKIPPED_EVENTS}</td>
							</tr>

							<tr>
								<td>{\App\Language::translate('LBL_TOTAL_TASKS_IMPORTED', $MODULE_NAME)}</td>
								<td width="10%">:</td>
								<td width="30%">{$SUCCESS_TASKS}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_TOTAL_TASKS_SKIPPED', $MODULE_NAME)}</td>
								<td width="10%">:</td>
								<td width="30%">{$SKIPPED_TASKS}</td>
							</tr>
						</table>
					</td>
				</tr>
			{/if}
			<tr>
				<td align="right" colspan="1">
					<div class="float-right">
						<a href="index.php?module={$MODULE_NAME}&view=Import&mode=undoImport&type=ics" name="next"
							class="marginLeft10 delete btn btn-danger">
							<strong>{\App\Language::translate('LBL_UNDO_LAST_IMPORT', $MODULE_NAME)}</strong>
							<a />
							<a href="{$MODULE_MODEL->getListViewUrl()}" name="next"
								class="marginLeft10 create btn btn-success">
								<strong>{\App\Language::translate('LBL_FINISH', $MODULE_NAME)}</strong>
							</a>
					</div>
				</td>
			</tr>
		</table>
		<!-- /tpl-Calendar-ImportResult -->
{/strip}
