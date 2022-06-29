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
	{if !empty($TASK_OBJECT->mappingPanel)}
		{assign var=MAPPING_PANEL value=$TASK_OBJECT->mappingPanel}
	{else}
		{assign var=MAPPING_PANEL value=""}
	{/if}
	<input type="hidden" id="taskFields"
		value="{\App\Purifier::encodeHtml(\App\Json::encode($TASK_OBJECT->getFieldNames()))}" />
	<input type="hidden" id="mappingPanel" value="{$MAPPING_PANEL}" />
	<div class="form-group text-left">
		<input type="hidden" name="verifyIfExists" value="0">
		<input type="checkbox" name="verifyIfExists" id="verifyIfExists" value="1" class="alignTop" {if !empty($TASK_OBJECT->verifyIfExists)} checked {/if} />
		<label class="form-check-label ml-1" for="verifyIfExists">
			{\App\Language::translate('LBL_VERIFY_IF_EXISTS', $QUALIFIED_MODULE)}
		</label>
	</div>
	<div class="form-group text-left">
		<div class="radio-inline">
			<label>
				<input type="radio" name="mappingPanel" data-hide="getFromPanelMapp" data-show="createOwnMapp"
					id="optionsRadios1" value="0" {if !$MAPPING_PANEL}checked{/if}>
				<span class="ml-1">{\App\Language::translate('LBL_CREATE_CUSTOM_MAPPING', $QUALIFIED_MODULE)}</span>
			</label>
		</div>
		<div class="radio-inline"></div>
		<div class="radio-inline">
			<label>
				<input type="radio" name="mappingPanel" data-hide="createOwnMapp" data-show="getFromPanelMapp"
					id="optionsRadios2" value="1" {if $MAPPING_PANEL} checked{/if}>
				<span class="ml-1">{\App\Language::translate('LBL_GET_FROM_PANEL_MAPPING', $QUALIFIED_MODULE)}</span>
			</label>
		</div>
	</div>
	<hr>
	<br />
	<div class="createOwnMapp{if $MAPPING_PANEL} d-none{/if}">
		<div class="row">
			<label class="col-md-4 col-form-label">
				<strong>{\App\Language::translate('LBL_MODULES_TO_CREATE_RECORD',$QUALIFIED_MODULE)}
					<span class="redColor">*</span>
				</strong>
			</label>
			<div class="col-md-6">
				{if !empty($TASK_OBJECT->entity_type)}
					{assign var=RELATED_MODULE_MODEL_NAME value=$TASK_OBJECT->entity_type}
				{else}
					{assign var=RELATED_MODULE_MODEL_NAME value=""}
				{/if}
				<select class="select2 createEntityModule" id="createEntityModule" name="entity_type"
					data-validation-engine='validate[required]' {if $MAPPING_PANEL} disabled{/if}
					data-select="allowClear"
					data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
					<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
					{foreach from=$WORKFLOW_MODEL->getModuleRelationsByType() key=RELATION_NAME item=GROUPED_RELATIONS}
						<optgroup label="{\App\Language::translate($RELATION_NAME, $QUALIFIED_MODULE)}">
							{foreach from=$GROUPED_RELATIONS item=MODULE_INFO}
								{if (false !== stripos($MODULE_INFO['actions'], 'ADD') || $WORKFLOW_MODEL->getModule()->getName() eq $MODULE_INFO['related_modulename']) &&  \App\Privilege::isPermitted($MODULE_INFO['related_modulename'], 'EditView')}
									<option data-relation-id="{$MODULE_INFO['relation_id']}" {if $RELATED_MODULE_MODEL_NAME eq $MODULE_INFO['related_modulename']} selected="" {/if} value="{$MODULE_INFO['related_modulename']}">
										{\App\Language::translate($MODULE_INFO['label'], $MODULE_INFO['related_modulename'])}
									</option>
								{/if}
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
		<br />
	</div>
	<div class="getFromPanelMapp{if !$MAPPING_PANEL} d-none{/if}">
		<div class="row">
			<label class="col-md-4 col-form-label">
				<strong>{\App\Language::translate('LBL_SELECT_TEMPLATE_FOR_MODULE',$QUALIFIED_MODULE)}
					<span class="redColor">*</span>
				</strong>
			</label>
			<div class="col-md-6">
				<select class="select2 createEntityModule" id="templatesMapp" name="entity_type"
					data-validation-engine='validate[required]' {if !$MAPPING_PANEL} disabled{/if}
					data-select="allowClear"
					data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
					<optgroup class="p-0">
						<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
					</optgroup>
					{foreach from=$TEMPLATES_MAPPING key=ID item=TEMPLATE}
						{assign var=TEMPLATE_RELATED_MODULE_NAME value=$TEMPLATE->getRelatedName()}
						<option {if isset($TASK_OBJECT->entity_type) && ($TASK_OBJECT->entity_type eq $TEMPLATE_RELATED_MODULE_NAME)} selected="" {/if}
							value="{$TEMPLATE_RELATED_MODULE_NAME}">{\App\Language::translate($TEMPLATE_RELATED_MODULE_NAME, $TEMPLATE_RELATED_MODULE_NAME)}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<br />
	</div>
	<div id="addCreateEntityContainer">
		{include file=\App\Layout::getTemplatePath('CreateEntity.tpl', $QUALIFIED_MODULE)}
	</div>
{/strip}
