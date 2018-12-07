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
	<div class="tpl-Settings-PickListDependency-EditView">
		<div class="widget_header row mb-3">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents">
			<form id="pickListDependencyForm" class="form-row" method="POST">
				{if !empty($MAPPED_VALUES)}
					<input type="hidden" class="editDependency" value="true"/>
					<input type="hidden" name="sourceModule" value="{$SELECTED_MODULE}"/>
					<input type="hidden" name="sourceField" value="{$RECORD_MODEL->get('sourcefield')}"/>
					<input type="hidden" name="targetField" value="{$RECORD_MODEL->get('targetfield')}"/>
				{/if}
				<div class="col-md-4 d-flex mb-2 mb-md-0">
					<label class="muted u-text-small-bold u-white-space-nowrap mr-2 my-auto">{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</label>
					<div class="w-100">
						<select name="sourceModule"
								title="{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}"
								class="select2 form-control ml-0">
							{foreach item=MODULE_MODEL from=$PICKLIST_MODULES_LIST}
								{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
								<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE} selected {/if}>
									{\App\Language::translate($MODULE_MODEL->get('label'), $MODULE_NAME)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="col-md-4 d-flex mb-2 mb-md-0">
					<label class="muted u-text-small-bold u-white-space-nowrap mr-2 my-auto">{\App\Language::translate('LBL_SOURCE_FIELD', $QUALIFIED_MODULE)}</label>
					<div class="w-100">
						<select id="sourceField" name="sourceField" class="select2 form-control"
								data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}"
								title="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
							<option value=''></option>
							{foreach key=FIELD_NAME item=FIELD_LABEL from=$PICKLIST_FIELDS}
								<option value="{$FIELD_NAME}" {if $RECORD_MODEL->get('sourcefield') eq $FIELD_NAME} selected {/if}>{\App\Language::translate($FIELD_LABEL, $SELECTED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="col-md-4 d-flex mb-2 mb-md-0">
					<label class="muted u-text-small-bold u-white-space-nowrap mr-2 my-auto">{\App\Language::translate('LBL_TARGET_FIELD', $QUALIFIED_MODULE)}</label>
					<div class="w-100">
						<select id="targetField" name="targetField" class="select2 form-control"
								data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}"
								title="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
							<option value=''></option>
							{foreach key=FIELD_NAME item=FIELD_LABEL from=$PICKLIST_FIELDS}
								<option value="{$FIELD_NAME}" {if $RECORD_MODEL->get('targetfield') eq $FIELD_NAME} selected {/if}>{\App\Language::translate($FIELD_LABEL, $SELECTED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="d-none errorMessage my-3">
					<div class="alert alert-warning">
						<strong>{\App\Language::translate('LBL_ERR_CYCLIC_DEPENDENCY', $QUALIFIED_MODULE)}</strong>
					</div>
				</div>
				<div id="dependencyGraph" class="my-3 w-100">
					{if $DEPENDENCY_GRAPH}
						{include file=\App\Layout::getTemplatePath('DependencyGraph.tpl', $QUALIFIED_MODULE)}
					{/if}
				</div>
			</form>
		</div>
	</div>
{/strip}
