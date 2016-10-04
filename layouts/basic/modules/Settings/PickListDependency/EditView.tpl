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
	<div class="">
		<div class='widget_header row '>
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
		<div class="contents">
			<br>
			<form id="pickListDependencyForm" class="form-horizontal" method="POST">
				{if !empty($MAPPED_VALUES)}
					<input type="hidden" class="editDependency" value="true"/>
					<input type="hidden" name="sourceModule" value="{$SELECTED_MODULE}"/>
					<input type="hidden" name="sourceField" value="{$RECORD_MODEL->get('sourcefield')}"/>
					<input type="hidden" name="targetField" value="{$RECORD_MODEL->get('targetfield')}"/>
				{/if}
				<div class="row">
					<div class="col-md-12">
						<div class="row col-md-6 ">
							<label class="col-md-4 muted control-label">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</label>
							<div class="col-md-8 controls">
								<select name="sourceModule" title="{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}" class="select2 form-control marginLeftZero">
									{foreach item=MODULE_MODEL from=$PICKLIST_MODULES_LIST}
										{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
										<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE} selected {/if}>
											{if $MODULE_MODEL->get('label') eq 'Calendar'}
												{vtranslate('LBL_TASK', $MODULE_MODEL->get('label'))}
											{else}
												{vtranslate($MODULE_MODEL->get('label'), $MODULE_NAME)}
											{/if}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="col-md-6">
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-12">
						<div class="row col-md-6 ">
							<label class="col-md-4 muted control-label">{vtranslate('LBL_SOURCE_FIELD', $QUALIFIED_MODULE)}</label>
							<div class="col-md-8 controls">
								<select id="sourceField" name="sourceField" class="select2 form-control" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}"  title="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
									<option value=''></option>
									{foreach key=FIELD_NAME item=FIELD_LABEL from=$PICKLIST_FIELDS}
										<option value="{$FIELD_NAME}" {if $RECORD_MODEL->get('sourcefield') eq $FIELD_NAME} selected {/if}>{vtranslate($FIELD_LABEL, $SELECTED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="row col-md-6">
							<label class="col-md-4 muted control-label">{vtranslate('LBL_TARGET_FIELD', $QUALIFIED_MODULE)}</label>
							<div class="col-md-8 controls">
								<select id="targetField" name="targetField" class="select2 form-control" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}" title="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
									<option value=''></option>
									{foreach key=FIELD_NAME item=FIELD_LABEL from=$PICKLIST_FIELDS}
										<option value="{$FIELD_NAME}" {if $RECORD_MODEL->get('targetfield') eq $FIELD_NAME} selected {/if}>{vtranslate($FIELD_LABEL, $SELECTED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class=" hide errorMessage">
					<div class="alert alert-warning">
						<strong>{vtranslate('LBL_ERR_CYCLIC_DEPENDENCY', $QUALIFIED_MODULE)}</strong>  
					</div>
				</div>
				<div id="dependencyGraph">
					{if $DEPENDENCY_GRAPH}
						{include file='DependencyGraph.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
					{/if}
				</div>
			</form>
		</div>
	</div>
</div>
{/strip}
