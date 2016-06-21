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
	<div class="row">
		<div class="form-group">
			<label class="col-md-2 control-label">{vtranslate('LBL_RECEPIENTS',$QUALIFIED_MODULE)}<span class="redColor">*</span></label>
			<div class="col-md-4">
				<input type="text" class="fields form-control" data-validation-engine='validate[required]' name="sms_recepient" value="{$TASK_OBJECT->sms_recepient}" />
			</div>
			<div class="col-md-4">
				<select class="chzn-select task-fields form-control">
					{foreach from=$RECORD_STRUCTURE_MODEL->getFieldsByType('phone') item=FIELD key=FIELD_VALUE}
						<option value=",${$FIELD_VALUE}">({vtranslate($FIELD->getModule()->get('name'),$FIELD->getModule()->get('name'))})  {vtranslate($FIELD->get('label'),$FIELD->getModule()->get('name'))}</option>
					{/foreach}
				</select>	
			</div>			
		</div>			
	</div>
	<div class="row">
		<div class="form-group">
			<label class="col-md-2 control-label">{vtranslate('LBL_ADD_FIELDS',$QUALIFIED_MODULE)}</label>
			<div class="col-md-4">
				<select class="chzn-select task-fields form-control">
					{$ALL_FIELD_OPTIONS}
				</select>	
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-2 control-label">{vtranslate('LBL_SMS_TEXT',$QUALIFIED_MODULE)}</label>
			<div class="col-md-8">
				<textarea name="content" class="form-control fields">{$TASK_OBJECT->content}</textarea>
			</div>
		</div>	
	</div>
	
{/strip}	
