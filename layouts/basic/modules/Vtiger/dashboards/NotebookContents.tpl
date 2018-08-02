{************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<div style='padding:5px'>
		<div class="">
			<div class="dashboard_notebookWidget_view row">
				<div class="">
					<span class="col-md-10 muted">
						<i>{\App\Language::translate('LBL_LAST_SAVED_ON', $MODULE)}</i> {\App\Fields\DateTime::formatToDay($WIDGET->getLastSavedDate())}
					</span>
					<span class="col-md-2">
						<span class="float-right">
							<button class="btn btn-sm btn-light float-right dashboard_notebookWidget_edit">
								<strong>{\App\Language::translate('LBL_EDIT', $MODULE)}</strong>
							</button>
						</span>
					</span>
				</div>
				<div class="col-md-12 pushDown2per">
					<div class="dashboard_notebookWidget_viewarea boxSizingBorderBox" style="background-color:white;border: 1px solid #CCC">
						{$WIDGET->getContent()|nl2br}
					</div>
				</div>
			</div>
			<div class="dashboard_notebookWidget_text" style="display:none;">
				<div class="row">
					<span class="col-md-10 muted">
						<i>{\App\Language::translate('LBL_LAST_SAVED_ON', $MODULE)}</i> {\App\Fields\DateTime::formatToDay($WIDGET->getLastSavedDate())}
					</span>
					<span class="col-md-2">
						<span class="float-right">
							<button class="btn btn-sm btn-success float-right dashboard_notebookWidget_save">
								<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
							</button>
						</span>
					</span>
				</div>
				<div class="row pushDown2per">
					<span class="col-md-12">
						<textarea class="dashboard_notebookWidget_textarea form-control boxSizingBorderBox" style="background-color: #ffffdd;resize: none;" data-note-book-id="{$WIDGET->get('id')}">
							{$WIDGET->getContent()}
						</textarea>
					</span>
				</div>
			</div>
		</div>
	</div>
{/strip}
