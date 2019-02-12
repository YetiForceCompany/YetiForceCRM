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
<!-- tpl-Calendar-ImportFinalResult -->
<div>{*Closing of the DIV in  tpl-IndexPostProcess *}
	<input type="hidden" name="module" value="{$MODULE_NAME}"/>
	<table class="col-12 paddingLRZero no-margin searchUIBasic well">
		<tr>
			<td class="font-x-large" align="left" colspan="2">
				<strong>{\App\Language::translate('LBL_IMPORT', $MODULE_NAME)} {\App\Language::translate($MODULE_NAME, $MODULE_NAME)}
					- {\App\Language::translate('LBL_RESULT', $MODULE_NAME)}</strong>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<table cellpadding="5" cellspacing="0" align="center" width="100%"
					   class="dvtSelectedCell thickBorder importContents">
					<tr>
						<td>{\App\Language::translate('LBL_LAST_IMPORT_UNDONE', $MODULE_NAME)}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="right" colspan="2">
				<a href="index.php?module={$MODULE_NAME}&view=List" button name="next"
				   class="create btn btn-success">
					<strong>{\App\Language::translate('LBL_FINISH', $MODULE_NAME)}</strong>
				</a>
			</td>
		</tr>
	</table>
	{*</div>Closing of the DIV in  tpl-IndexPostProcess *}
	<!-- /tpl-Calendar-ImportFinalResult -->
	{/strip}
