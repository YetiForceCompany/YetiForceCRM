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
			<td class="pl-3">{\App\Language::translate('LBL_TOTAL_RECORDS_IMPORTED', $MODULE)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">{$IMPORT_RESULT.IMPORTED} / {$IMPORT_RESULT.TOTAL}</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_CREATED', $MODULE)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">{$IMPORT_RESULT.CREATED}</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_UPDATED', $MODULE)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">{$IMPORT_RESULT.UPDATED}</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_SKIPPED', $MODULE)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per"><span
						class="{if $IMPORT_RESULT['FAILED'] neq '0'} mr-2 {/if}">{$IMPORT_RESULT.SKIPPED}</span>
				{if $IMPORT_RESULT['SKIPPED'] neq '0'}
					<a class="u-cursor-pointer js-openListInModal" data-js="openListInModal" data-moduleName="{$MODULE}"
					   data-type="skipped" data-forUser="{$OWNER_ID}"
					   data-forModule="{$FOR_MODULE}"> {\App\Language::translate('LBL_DETAILS', $MODULE)}</a>
				{/if}
			</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_MERGED', $MODULE)}</td>
			<td class="u-w-10per">:</td>
			<td class="pr-3 u-w-30per">{$IMPORT_RESULT.MERGED}</td>
		</tr>
		<tr>
			<td class="pl-3">{\App\Language::translate('LBL_TOTAL_RECORDS_FAILED', $MODULE)}</td>
			<td>:</td>
			<td class="pr-3 u-w-30per">
				<span class="{if $IMPORT_RESULT['FAILED'] neq '0'} mr-2 {/if}">
					{$IMPORT_RESULT.FAILED}/ {$IMPORT_RESULT.TOTAL}
				</span>
				{if $IMPORT_RESULT['FAILED'] neq '0'}
					<a class="u-cursor-pointer js-openListInModal" data-js="openListInModal" data-moduleName="{$MODULE}"
					   data-type="failed" data-forUser="{$OWNER_ID}"
					   data-forModule="{$FOR_MODULE}"> {\App\Language::translate('LBL_DETAILS', $MODULE)}</a>
				{/if}
			</td>
		</tr>
	</table>
{/strip}