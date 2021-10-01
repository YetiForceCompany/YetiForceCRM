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
	<!-- tpl-Import-Import_Finish_Buttons -->
	<button class="create btn btn-success btn-sm mr-2 mb-sm-3 mb-md-0" name="next"
			onclick="location.href = 'index.php?module={$FOR_MODULE}&view=Import&return_module={$FOR_MODULE}&return_action=index'">
		<strong><span class="fas fa-file-import mr-2"></span>{\App\Language::translate('LBL_IMPORT_MORE', $MODULE_NAME)}</strong>
	</button>
	<button class="cancel btn btn-primary btn-sm mr-2 mb-sm-3 mb-md-0 js-show-modal" name="next"
			data-url="index.php?module={$MODULE_NAME}&forModule={$FOR_MODULE}&view=List&start=1&foruser={$OWNER_ID}">
		<strong><span class="fas fa-list mr-2"></span>{\App\Language::translate('LBL_VIEW_LAST_IMPORTED_RECORDS', $MODULE_NAME)}</strong>
	</button>
	{if $MERGE_ENABLED eq '0'}
		<button class="delete btn btn-danger btn-sm mr-2 mb-sm-3 mb-md-0" name="next"
				onclick="location.href = 'index.php?module={$FOR_MODULE}&view=Import&mode=undoImport&foruser={$OWNER_ID}'">
			<strong><span class="fas fa-history mr-2"></span>{\App\Language::translate('LBL_UNDO_LAST_IMPORT', $MODULE_NAME)}</strong>
		</button>
	{/if}
	<button class="edit btn btn-success btn-sm mb-sm-3 mb-md-0" name="cancel"
			onclick="location.href = '{$MODULE_MODEL->getUrl()}'"
		<strong><span class="fas fa-angle-double-right mr-2"></span>{\App\Language::translate('LBL_FINISH_BUTTON_LABEL', $MODULE_NAME)}</strong>
	</button>
	<!-- /tpl-Import-Import_Finish_Buttons -->
{/strip}
