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

<table cellpadding="5" cellspacing="0" align="center" width="100%" class="dvtSelectedCell thickBorder importContents">
	<tr>
		<td>{\App\Language::translate('LBL_TOTAL_RECORDS_IMPORTED', $MODULE)}</td>
		<td width="10%">:</td>
		<td width="30%">{$IMPORT_RESULT.IMPORTED} / {$IMPORT_RESULT.TOTAL}</td>
	</tr>
	<tr>
		<td>{\App\Language::translate('LBL_NUMBER_OF_RECORDS_CREATED', $MODULE)}</td>
		<td width="10%">:</td>
		<td width="30%">{$IMPORT_RESULT.CREATED}</td>
	</tr>
	<tr>
		<td>{\App\Language::translate('LBL_NUMBER_OF_RECORDS_UPDATED', $MODULE)}</td>
		<td width="10%">:</td>
		<td width="30%">{$IMPORT_RESULT.UPDATED}</td>
	</tr>
	<tr>
		<td>{\App\Language::translate('LBL_NUMBER_OF_RECORDS_SKIPPED', $MODULE)}</td>
		<td width="10%">:</td>
		<td width="30%">{$IMPORT_RESULT.SKIPPED}
			{if $IMPORT_RESULT['SKIPPED'] neq '0'}
				&nbsp;&nbsp;<a class="u-cursor-pointer" 
							   onclick="return window.open('index.php?module={$MODULE}&view=List&mode=getImportDetails&type=skipped&start=1&foruser={$OWNER_ID}&forModule={$FOR_MODULE}', 'skipped', 'width=700,height=650,resizable=no,scrollbars=yes,top=150,left=200');">
					{\App\Language::translate('LBL_DETAILS', $MODULE)}</a>
				{/if}
		</td>
	</tr>
	<tr>
		<td>{\App\Language::translate('LBL_NUMBER_OF_RECORDS_MERGED', $MODULE)}</td>
		<td width="10%">:</td>
		<td width="10%">{$IMPORT_RESULT.MERGED}</td>
	</tr>
	<tr>
		<td>{\App\Language::translate('LBL_TOTAL_RECORDS_FAILED', $MODULE)}</td>
		<td width="10%">:</td>
		<td width="30%">{$IMPORT_RESULT.FAILED} / {$IMPORT_RESULT.TOTAL}
			{if $IMPORT_RESULT['FAILED'] neq '0'}
				&nbsp;&nbsp;<a class="u-cursor-pointer" onclick="return window.open('index.php?module={$MODULE}&view=List&mode=getImportDetails&type=failed&start=1&foruser={$OWNER_ID}&forModule={$FOR_MODULE}', 'failed', 'width=700,height=650,resizable=no,scrollbars=yes,top=150,left=200');">{\App\Language::translate('LBL_DETAILS', $MODULE)}</a>
			{/if}
		</td>
	</tr>
</table>
