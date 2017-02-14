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
					<option value="none"></option>
					{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable('phone')}
						<optgroup label="{\App\Language::translate($BLOCK_NAME, $SOURCE_MODULE)}">
							{foreach item=ITEM from=$FIELDS}
								<option value=",{$ITEM['var_value']}" data-label="{$ITEM['var_label']}" {if $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
									{\App\Language::translate($ITEM['label'], $SOURCE_MODULE)}
								</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>	
			</div>			
		</div>			
	</div>
	<hr/>
	<div class="row">
		{include file='VariablePanel.tpl'|@vtemplate_path SELECTED_MODULE=$SOURCE_MODULE PARSER_TYPE='mail' GRAY=true}
	</div>
	<hr/>
	<div class="row">
		<div class="form-group">
			<label class="col-md-2 control-label">{vtranslate('LBL_SMS_TEXT',$QUALIFIED_MODULE)}</label>
			<div class="col-md-8">
				<textarea name="content" class="form-control fields">{$TASK_OBJECT->content}</textarea>
			</div>
		</div>	
	</div>
{/strip}	
