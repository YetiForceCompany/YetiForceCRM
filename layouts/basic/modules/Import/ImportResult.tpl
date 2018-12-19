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
	<!-- tpl-Import-ImportResult -->
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div>
		<input type="hidden" name="module" value="{$FOR_MODULE}"/>
		<table class="searchUIBasic well col-12 m-0">
			<tr>
				<td class="font-x-large text-center">
					<h3>
						<strong>
							{\App\Language::translate('LBL_IMPORT', $MODULE_NAME)} {\App\Language::translate($FOR_MODULE, $MODULE_NAME)}
							- {\App\Language::translate('LBL_RESULT', $MODULE_NAME)}
						</strong>
					</h3>
				</td>
			</tr>
			{if !empty($ERROR_MESSAGE)}
				<tr>
					<td class="text-center">
						{$ERROR_MESSAGE}
					</td>
				</tr>
			{/if}
			<tr>
				<td>
					{include file=\App\Layout::getTemplatePath('Import_Result_Details.tpl', 'Import')}
				</td>
			</tr>
			<tr>
				<td class="float-right">
					{include file=\App\Layout::getTemplatePath('Import_Finish_Buttons.tpl', 'Import')}
				</td>
			</tr>
		</table>
	</div>
	<!-- /tpl-Import-ImportResult -->
{/strip}
