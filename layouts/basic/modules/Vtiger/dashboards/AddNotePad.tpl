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
	<div id="addNotePadWidgetContainer" class='modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE')}">&times;</button>
					<h3 id="massEditHeader" class="modal-title">{\App\Language::translate('LBL_ADD', $MODULE)} {\App\Language::translate('LBL_NOTEPAD', $MODULE)}</h3>
				</div>
				<form class="form-horizontal" method="POST">
					 <div class="form-group margin0px padding1per">
						<label class="col-sm-3 control-label">{\App\Language::translate('LBL_NOTEPAD_NAME', $MODULE)}<span class="redColor">*</span> </label>
						<div class="col-sm-8 controls">
							<input type="text" name="notePadName" class="form-control" data-validation-engine="validate[required]" />
						</div>
					</div>
					<div class="form-group margin0px padding1per">
						<label class="col-sm-3 control-label">{\App\Language::translate('LBL_NOTEPAD_CONTENT', $MODULE)}</label>
						<div class="col-sm-8 controls">
							<textarea type="text" name="notePadContent" class="form-control" style="resize: none;"/>
						</div>
					</div>
						{include file='ModalFooter.tpl'|@\App\Layout::getTemplatePath:$MODULE}
				</form>
			</div>
		</div>
	</div>
{/strip}
