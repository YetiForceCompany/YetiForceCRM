{************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
*************************************************************************************}
{strip}
	<div class="tpl-Base-dashboards-NotebookContents">
		<div class="dashboard_notebookWidget_view row">
			<div class="d-flex justify-content-between w-100 mb-1">
				<div class="muted align-self-center">
					<i>{\App\Language::translate('LBL_LAST_SAVED_ON', $MODULE_NAME)}</i> {\App\Fields\DateTime::formatToDay($WIDGET->getLastSavedDate())}
				</div>
				<button class="btn btn-sm btn-light dashboard_notebookWidget_edit">
					<span class="yfi yfi-full-editing-view mr-1"></span>{\App\Language::translate('LBL_EDIT', $MODULE_NAME)}
				</button>
			</div>
			<div class="w-100 dashboard_notebookWidget_viewarea boxSizingBorderBox border rounded p-1">
				{$WIDGET->getContent()|nl2br}
			</div>
		</div>
		<div class="dashboard_notebookWidget_text" style="display:none;">
			<div class="d-flex justify-content-between w-100 mb-1">
				<div class="muted align-self-center">
					<i>{\App\Language::translate('LBL_LAST_SAVED_ON', $MODULE_NAME)}</i> {\App\Fields\DateTime::formatToDay($WIDGET->getLastSavedDate())}
				</div>
				<button class="btn btn-sm btn-success float-right dashboard_notebookWidget_save">
					<span class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
				</button>
			</div>
			<textarea class="w-100 dashboard_notebookWidget_textarea form-control boxSizingBorderBox border rounded p-1"
				data-note-book-id="{$WIDGET->get('id')}">
								{$WIDGET->getContent()}
							</textarea>
		</div>
	</div>
{/strip}
