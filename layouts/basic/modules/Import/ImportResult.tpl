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
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
		</div>
	</div>
	<div>
		<input type="hidden" name="module" value="{$FOR_MODULE}" />
		<table class="searchUIBasic well col-12 paddingLRZero no-margin">
			<tr>
				<td class="font-x-large" align="left" colspan="2">
					<strong>{\App\Language::translate('LBL_IMPORT', $MODULE)} {\App\Language::translate($FOR_MODULE, $MODULE)} - {\App\Language::translate('LBL_RESULT', $MODULE)}</strong>
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
				<td valign="top">
					{include file=\App\Layout::getTemplatePath('Import_Result_Details.tpl', 'Import')}
				</td>
			</tr>
			<tr>
				<td align="right" colspan="2">
					{include file=\App\Layout::getTemplatePath('Import_Finish_Buttons.tpl', 'Import')}
				</td>
			</tr>
		</table>
	</div>
{/strip}
