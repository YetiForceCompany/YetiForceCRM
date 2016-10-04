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
{assign var=TEMPLATELIST value=OSSMailTemplates_Record_Model::getTempleteList($SOURCE_MODULE)}
<div class="well" id="VtVTEmailTemplateTaskContainer">
	<div class="">
		<div class="row padding-bottom1per">
			<span class="col-md-4">{vtranslate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select class="chzn-select form-control" name="template" data-validation-engine='validate[required]'>
				<option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
				{foreach from=$TEMPLATELIST key=key item=item}
					<option {if $TASK_OBJECT->template eq $key}selected=""{/if} value="{$key}">{vtranslate($item.name, $QUALIFIED_MODULE)}</option>
				{/foreach}	
			</select>
			</div>
		</div>
		<!--
		<div class="row padding-bottom1per">
			<span class="col-md-4">{vtranslate('Do you send all attachments', $QUALIFIED_MODULE)}</span>
			<input type="checkbox" name="attachments" class="col-md-4" value="true" {if $TASK_OBJECT->attachments eq 'true'}checked{/if}>
		</div>
		-->
		<div class="row padding-bottom1per">
			<span class="col-md-4">{vtranslate('Select e-mail address', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select class="chzn-select form-control" name="email" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" data-validation-engine='validate[required]'>
					<option value="none"></option>
					{assign var=RECORD_STRUCTURE_TYPE value=$RECORD_STRUCTURE_MODEL->getStructure('email')}
					{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE_TYPE }
						<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
								{assign var=KEYVAL value=$FIELD_MODEL->get(selectOption)}
								<option value="{$KEYVAL}" {if $TASK_OBJECT->email eq $KEYVAL}selected=""{/if}>
									{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')} 
										({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
									{else}
										{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
									{/if}
								</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-4">{vtranslate('Send a copy to email', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<input class="form-control" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="copy_email" value="{$TASK_OBJECT->copy_email}">
			</div>
		</div>
	</div>
</div>	
{/strip}	
