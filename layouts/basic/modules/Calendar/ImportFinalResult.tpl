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
	<div>
		<input type="hidden" name="module" value="{$MODULE}" />
		<table class="col-12 paddingLRZero no-margin searchUIBasic well">
			<tr>
				<td class="font-x-large" align="left" colspan="2">
					<strong>{\App\Language::translate('LBL_IMPORT', $MODULE)} {\App\Language::translate($FOR_MODULE, $MODULE)} - {\App\Language::translate('LBL_RESULT', $MODULE)}</strong>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="5" cellspacing="0" align="center" width="100%" class="dvtSelectedCell thickBorder importContents">
						<tr>
							<td>{\App\Language::translate('LBL_LAST_IMPORT_UNDONE', $MODULE)}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align="right" colspan="2">
					<a href="index.php?module={$MODULE}&view=List" button name="next" class="create btn btn-success">
						<strong>{\App\Language::translate('LBL_FINISH', $MODULE)}</strong>
					</a>
				</td>
			</tr>
		</table>
	{/strip}
