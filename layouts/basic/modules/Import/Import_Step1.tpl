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
	<table width="100%" cellspacing="0" cellpadding="2">
		<tr>
			<td><strong>{\App\Language::translate('LBL_IMPORT_STEP_1', $MODULE)}:</strong></td>
			<td>{\App\Language::translate('LBL_IMPORT_STEP_1_DESCRIPTION', $MODULE)}</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan='2' data-import-upload-size="{$IMPORT_UPLOAD_SIZE}" data-import-upload-size-mb="{$IMPORT_UPLOAD_SIZE_MB}">
				<input type="hidden" name="type" value="csv" />
				<input type="hidden" name="is_scheduled" value="1" />
				<input type="file" name="import_file" id="import_file" title="{\App\Language::translate('LBL_SELECT_FILE', $MODULE)}" accept="{$SUPPORTED_FILE_TYPES_TEXT}" onchange="ImportJs.checkFileType()" />
				<!-- input type="hidden" name="userfile_hidden" value=""/ -->
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan='2'>{\App\Language::translate('LBL_IMPORT_SUPPORTED_FILE_TYPES', $MODULE)}: {$SUPPORTED_FILE_TYPES_TEXT}</td>
		</tr>
	</table>
{/strip}
