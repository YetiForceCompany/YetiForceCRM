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
	<div style="padding-left: 15px;">
		<table style=" width:90%;margin-left: 5% " cellpadding="10" class="searchUIBasic well">
			<tr>
				<td class="font-x-large" align="left" colspan="2">
					<strong>{\App\Language::translate('LBL_IMPORT_SCHEDULED', $MODULE)}</strong>
				</td>
			</tr>
			{if $ERROR_MESSAGE neq ''}
				<tr>
					<td class="style1" align="left" colspan="2">
						{$ERROR_MESSAGE}
					</td>
				</tr>
			{/if}
			<tr>
				<td colspan="2" valign="top">
					<table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents">
						<tr>
							<td>{\App\Language::translate('LBL_SCHEDULED_IMPORT_DETAILS', $MODULE)}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align="right" colspan="2">
					<a type="button" name="cancel" value="{\App\Language::translate('LBL_CANCEL_IMPORT', $MODULE)}" class="crmButton small delete"
					   onclick="location.href = 'index.php?module={$FOR_MODULE}&view=Import&mode=cancelImport&import_id={$IMPORT_ID}'">{\App\Language::translate('LBL_CANCEL_IMPORT', $MODULE)}</a>
					{include file=\App\Layout::getTemplatePath('Import_Done_Buttons.tpl', 'Import')}
				</td>
			</tr>
		</table>
	</div>
{/strip}
