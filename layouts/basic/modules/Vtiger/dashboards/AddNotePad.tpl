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
	<div id="addNotePadWidgetContainer" class='modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-plus mr-1"></span>
						{\App\Language::translate('LBL_ADD', $MODULE)} {\App\Language::translate('LBL_NOTEPAD', $MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal" method="POST">
					<div class="form-group row align-items-center margin0px padding1per">
						<label class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_NOTEPAD_NAME', $MODULE)}<span class="redColor">*</span> </label>
						<div class="col-sm-8 controls">
							<input type="text" name="notePadName" class="form-control" data-validation-engine="validate[required]" />
						</div>
					</div>
					<div class="form-group row align-items-center margin0px padding1per">
						<label class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_NOTEPAD_CONTENT', $MODULE)}</label>
						<div class="col-sm-8 controls">
							<textarea type="text" name="notePadContent" class="form-control" style="resize: none;"></textarea>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
