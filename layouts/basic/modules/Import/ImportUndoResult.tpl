{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Import-ImportUndoResult -->
	<div class="widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="col-md-3 col-sm-2"></div>
	<div class="col-md-6 col-sm-8 col-12">
		<div class="js-toggle-panel c-panel" data-js="click">
			<div class="card-header">
				<h4 class="card-title">
					{\App\Language::translate('LBL_IMPORT', $MODULE_NAME)} {\App\Language::translate($FOR_MODULE, $FOR_MODULE)}
					- {\App\Language::translate('LBL_UNDO_RESULT', $MODULE_NAME)}
				</h4>
			</div>
			<div class="card-body form-horizontal font-larger">
				<input type="hidden" name="module" value="{$FOR_MODULE}" />
				{if !empty($ERROR_MESSAGE)}
					<div class="alert alert-warning">
						{$ERROR_MESSAGE}
					</div>
				{/if}
				<div class="form-group row">
					<div class="col-md-7 col-sm-6 col-8 textAlignRight fontBold">
						{\App\Language::translate('LBL_TOTAL_RECORDS', $MODULE_NAME)}:
					</div>
					<div class="col-md-5 col-sm-6 col-4">
						{$TOTAL_RECORDS}
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-7 col-sm-6 col-8 textAlignRight fontBold">
						{\App\Language::translate('LBL_NUMBER_OF_RECORDS_DELETED', $MODULE_NAME)}:
					</div>
					<div class="col-md-5 col-sm-6 col-4">
						{$DELETED_RECORDS_COUNT}
					</div>
				</div>
			</div>
			<div class="modal-footer">
				{include file=\App\Layout::getTemplatePath('Import_Done_Buttons.tpl', 'Import')}
			</div>
		</div>
	</div>
	<div class="col-md-3 col-sm-2"></div>
	<!-- /tpl-Import-ImportUndoResult -->
{/strip}
