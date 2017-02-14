{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}	
<div class="modal addKeyContainer fade" tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
		<div class="modal-header contentsBackground">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 class="modal-title">{vtranslate('LBL_CREATING_MODULE', $QUALIFIED_MODULE)}</h3>
		</div>
		<div class="modal-body">
			<form class="form-horizontal">
				<div class="form-group">
					<label class="col-sm-3 control-label"><span class="redColor">*</span>{vtranslate('LBL_ENTER_MODULE_NAME', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-6 controls">
						<input type="text" class="module_name form-control" data-validation-engine="validate[required, funcCall[Settings_Module_Manager_Js.validateField]]" name="module_name" placeholder="HelpDesk" required="true" >
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label"><span class="redColor">*</span>{vtranslate('LBL_ENTER_MODULE_LABEL', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-6 controls">
						<input type="text" class="module_name form-control" data-validation-engine="validate[required, funcCall[Settings_Module_Manager_Js.validateField]]" name="module_label" placeholder="Help Desk" required="true">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label"><span class="redColor">*</span>{vtranslate('LBL_ENTITY_FIELDNAME', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-6 controls">
						<input type="text" class="entityfieldname form-control" data-validation-engine="validate[required, funcCall[Settings_Module_Manager_Js.validateField]]" name="entityfieldname" placeholder="title" required="true">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label"><span class="redColor">*</span>{vtranslate('LBL_ENTITY_FIELDLABEL', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-6 controls">
						<input type="text" class="entityfieldlabel form-control" data-validation-engine="validate[required, funcCall[Settings_Module_Manager_Js.validateField]]"name="entityfieldlabel" placeholder="Title" required="true">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{vtranslate('LBL_MODULE_TYPE', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" title="{vtranslate('LBL_MODULE_TYPE', $QUALIFIED_MODULE)}" name="entitytype">
							<option value="0" selected>{vtranslate('LBL_BASE_MODULE', $QUALIFIED_MODULE)}</option>
							<option value="1">{vtranslate('LBL_INVENTORY_MODULE', $QUALIFIED_MODULE)}</option>
						</select>
					</div>
				</div>
			</form>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
</div>
{/strip}
