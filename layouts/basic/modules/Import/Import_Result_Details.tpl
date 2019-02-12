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
	<table class="tpl-Import-Import_Result_Details w-100 dvtSelectedCell thickBorder importContents">
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_TOTAL_RECORDS_IMPORTED', $MODULE_NAME)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">{$IMPORT_RESULT.IMPORTED} / {$IMPORT_RESULT.TOTAL}</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_CREATED', $MODULE_NAME)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">{$IMPORT_RESULT.CREATED}</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_UPDATED', $MODULE_NAME)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">{$IMPORT_RESULT.UPDATED}</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_SKIPPED', $MODULE_NAME)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">
				<span class="{if $IMPORT_RESULT['FAILED'] neq '0'} mr-2 {/if}">{$IMPORT_RESULT.SKIPPED}</span>
				{if $IMPORT_RESULT['SKIPPED'] neq '0'}
					<a class="u-cursor-pointer js-open-list-in-modal" data-js="click" data-module-name="{$MODULE_NAME}"
					   data-type="skipped" data-for-user="{$OWNER_ID}"
					   data-for-module="{$FOR_MODULE}"> {\App\Language::translate('LBL_DETAILS', $MODULE_NAME)}</a>
				{/if}
			</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_MERGED', $MODULE_NAME)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">{$IMPORT_RESULT.MERGED}</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_TOTAL_RECORDS_FAILED', $MODULE_NAME)}</td>
			<td>:</td>
			<td class="pr-3 u-w-30per">
				<span class="{if $IMPORT_RESULT['FAILED'] neq '0'} mr-2 {/if}">
					{$IMPORT_RESULT.FAILED}/ {$IMPORT_RESULT.TOTAL}
				</span>
				{if $IMPORT_RESULT['FAILED'] neq '0'}
					<a class="u-cursor-pointer js-open-list-in-modal" data-js="click" data-module-name="{$MODULE_NAME}"
					   data-type="failed" data-for-user="{$OWNER_ID}"
					   data-for-module="{$FOR_MODULE}"> {\App\Language::translate('LBL_DETAILS', $MODULE_NAME)}</a>
				{/if}
			</td>
		</tr>
	</table>
{/strip}