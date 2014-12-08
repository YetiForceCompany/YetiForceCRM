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
	<div id="addNotePadWidgetContainer" class='modal'>
		<div class="modal-header contentsBackground">
            <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
			<h3 id="massEditHeader">{vtranslate('LBL_ADD', $MODULE)} {vtranslate('LBL_NOTEPAD', $MODULE)}</h3>
		</div>
		<form class="form-horizontal" method="POST">
			 <div class="control-group margin0px padding1per">
				<label class="control-label">{vtranslate('LBL_NOTEPAD_NAME', $MODULE)}<span class="redColor">*</span> </label>
				<div class="controls">
					<input type="text" name="notePadName" class="input-large" data-validation-engine="validate[required]" />
				</div>
			</div>
			<div class="control-group margin0px padding1per">
				<label class="control-label">{vtranslate('LBL_NOTEPAD_CONTENT', $MODULE)}</label>
				<div class="controls">
					<textarea type="text" name="notePadContent" style="min-height: 100px;resize: none;"/>
				</div>
			</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>
	</div>
{/strip}