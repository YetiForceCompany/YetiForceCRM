{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<div id="massEditContainer" class='modelContainer modal fade' tabindex="-1">

		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button type="button" class="close " data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_MASS_EDITING', $MODULE)} {vtranslate($MODULE, $MODULE)}</h3>
				</div>
				<form class="form-horizontal" id="massEdit" name="MassEdit" method="post" action="index.php">
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
					{/if}
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="MassSave" />
					<input type="hidden" name="viewname" value="{$CVID}" />
					<input type="hidden" name="selected_ids" value={\App\Json::encode($SELECTED_IDS)}>
					<input type="hidden" name="excluded_ids" value={\App\Json::encode($EXCLUDED_IDS)}>
					<input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
					<input type="hidden" name="operator" value="{$OPERATOR}" />
					<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
					<input type="hidden" name="search_params" value='{\App\Json::encode($SEARCH_PARAMS)}' />
					<input type="hidden" id="massEditFieldsNameList" data-value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($MASS_EDIT_FIELD_DETAILS))}' />
					<div name="massEditContent">
						<div class="modal-body tabbable">
							<ul class="nav nav-tabs massEditTabs">
								{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
									{if $BLOCK_FIELDS|@count gt 0}
										<li {if $smarty.foreach.blockIterator.iteration eq 1}class="active"{/if}>
											<a href="#block_{$smarty.foreach.blockIterator.iteration}" data-toggle="tab"><strong>{vtranslate($BLOCK_LABEL, $MODULE)}</strong></a>
										</li>
									{/if}
								{/foreach}
							</ul>
							<div class="tab-content massEditContent">
								{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
									{if $BLOCK_FIELDS|@count gt 0}
										{assign var=BLOCK_INDEX value=$smarty.foreach.blockIterator.iteration}
										<div class="tab-pane {if $BLOCK_INDEX eq 1}active{/if}" id="block_{$BLOCK_INDEX}">
											<div class="massEditTable paddingTop20">
												{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
													{if $FIELD_MODEL->get('uitype') neq 104 && $FIELD_MODEL->isEditable()}
														<div class="form-group">
															<div class="col-md-offset-1 rowElements">
																<label class="marginLeft15 control-label col-md-4 fieldLabel btn btn-sm btn-default">
																	<span class="pull-left">
																		<input data-toggle="button" aria-pressed="false" autocomplete="off" type="checkbox" id="selectRow{$FIELD_MODEL->getName()}" title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}" data-field-name="{$FIELD_MODEL->getName()}" class="selectRow" {if $FIELD_MODEL->isEditableReadOnly()} disabled{/if}>&nbsp;
																	</span>
																	{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
																	{vtranslate($FIELD_MODEL->get('label'), $MODULE)}:
																</label>
																<div class="fieldValue col-md-6">
																	{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) VIEW = 'MassEdit'}
																</div>
															</div>
														</div>
													{/if}
												{/foreach}
											</div>
										</div>
									{/if}
								{/foreach}
							</div>
						</div>
					</div>
					{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
				</form>
			</div>
		</div>
	</div>
{/strip}
