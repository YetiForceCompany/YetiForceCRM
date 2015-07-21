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
	{assign var=PDF_TPL value=OSSPdf_Module_Model::getListTpl($SOURCE_MODULE)}
	{assign var=MAIL_TPL value=OSSMailTemplates_Record_Model::getTempleteList($SOURCE_MODULE)}

	<div class="well" id="VtVTEmailTemplateTaskContainer">
		<div class="row">
			<span class="col-md-4">{vtranslate('LBL_TEMPLATES', 'OSSPdf')}</span>
			<div class="col-md-6 padding-bottom1per">
				<select class="chzn-select form-control" name="pdf_tpl" data-validation-engine="validate[required]">
					<option value=""></option>
					{foreach from=$PDF_TPL key=key item=item}
						<option {if $TASK_OBJECT->pdf_tpl eq $item.id}selected{/if} value="{$item.id}">{$item.name}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row">
			<span class="col-md-4">{vtranslate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
			<div class="col-md-6 padding-bottom1per">
				<select class="chzn-select form-control" name="email_tpl"  data-validation-engine="validate[required]">
					<option value=""></option>
					{foreach from=$MAIL_TPL key=key item=item}
						<option {if $TASK_OBJECT->email_tpl eq $item.ossmailtemplatesid}selected{/if} value="{$item.ossmailtemplatesid}">{$item.name}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-4">{vtranslate('Select e-mail address', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select class="chzn-select form-control" name="email_fld" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" data-validation-engine="validate[required]">
					<option value=""></option>
					{assign var=RECORD_STRUCTURE_TYPE value=$RECORD_STRUCTURE_MODEL->getStructure('email')}
					{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE_TYPE }
						<optgroup label='{vtranslate($BLOCK_LABEL, $SELECTED_MODULE_NAME)}'>
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
								{assign var=KEYVAL value=$FIELD_MODEL->get(selectOption)}
								{if $KEYVAL neq ''} 
									<option value="{$KEYVAL}" {if $TASK_OBJECT->email_fld eq $KEYVAL}selected=""{/if}>
										{if $SELECTED_MODULE_NAME neq $MODULE_MODEL->get('name')} 
											({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
										{else}
											{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}
										{/if}
									</option>
								{/if}
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
	</div>

{/strip}
