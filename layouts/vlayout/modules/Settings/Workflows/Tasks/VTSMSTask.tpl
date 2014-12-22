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
	<div class="row-fluid">
		<div class="span2">{vtranslate('LBL_RECEPIENTS',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
		<div class="span8 row-fluid">
			<input type="text" class="span5 fields" data-validation-engine='validate[required]' name="sms_recepient" value="{$TASK_OBJECT->sms_recepient}" />
			<span class="span6">
				<select class="chzn-select task-fields">
                    {foreach from=$RECORD_STRUCTURE_MODEL->getFieldsByType('phone') item=FIELD key=FIELD_VALUE}
						<option value=",${$FIELD_VALUE}">({vtranslate($FIELD->getModule()->get('name'),$FIELD->getModule()->get('name'))})  {vtranslate($FIELD->get('label'),$FIELD->getModule()->get('name'))}</option>
					{/foreach}
				</select>	
			</span>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span2">{vtranslate('LBL_ADD_FIELDS',$QUALIFIED_MODULE)}</div>
		<div class="span10">
			<select class="chzn-select task-fields">
				{$ALL_FIELD_OPTIONS}
			</select>	
		</div>

		<div class="row-fluid">
			<div class="span2">{vtranslate('LBL_SMS_TEXT',$QUALIFIED_MODULE)}</div>
			<textarea name="content" class="span8 fields">{$TASK_OBJECT->content}</textarea>
		</div>
	</div>
{/strip}	