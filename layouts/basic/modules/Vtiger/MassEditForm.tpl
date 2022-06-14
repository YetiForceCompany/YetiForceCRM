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
	{if !empty($SCRIPTS)}
		{foreach key=index item=jsModel from=$SCRIPTS}
			<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
		{/foreach}
	{/if}
	<div id="massEditContainer" class="modal" tabindex="-1" role="dialog">

		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fa fa-edit"></i>
						{\App\Language::translate('LBL_MASS_EDITING', $MODULE)} {\App\Language::translate($MODULE, $MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<form id="massEdit" name="MassEdit" method="post" action="index.php">
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
					{/if}
					{if !empty($LIST_FILTER_FIELDS)}
						<input type="hidden" name="listFilterFields" value='{\App\Purifier::encodeHtml($LIST_FILTER_FIELDS)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="MassSave" />
					<input type="hidden" name="viewname" value="{$CVID}" />
					<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
					<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
					<input type="hidden" name="search_key" value="{$SEARCH_KEY}" />
					<input type="hidden" name="operator" value="{$OPERATOR}" />
					<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
					<input type="hidden" name="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}" />
					<input type="hidden" name="advancedConditions" value="{\App\Purifier::encodeHtml(\App\Json::encode($ADVANCED_CONDITIONS))}" />
					<div class="modal-body">
						<ul class="nav nav-tabs massEditTabs">
							{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
								{if $BLOCK_FIELDS|@count gt 0}
									<li class="nav-item col-6 col-sm-4 p-0 text-center">
										<a class="nav-link{if $smarty.foreach.blockIterator.iteration eq 1} active{/if}"
											href="#block_{$smarty.foreach.blockIterator.iteration}"
											data-toggle="tab"><strong>{\App\Language::translate($BLOCK_LABEL, $MODULE)}</strong></a>
									</li>
								{/if}
							{/foreach}
						</ul>
						<div class="tab-content">
							{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
								{if $BLOCK_FIELDS|@count gt 0}
									{assign var=BLOCK_INDEX value=$smarty.foreach.blockIterator.iteration}
									<div class="tab-pane fade{if $BLOCK_INDEX eq 1} show active{/if}"
										id="block_{$BLOCK_INDEX}" role="tabpanel">
										<div class="p-3">
											{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
												{if $FIELD_MODEL->getUIType() neq 104 && $FIELD_MODEL->isEditable()}
													<div class="form-row mb-2 js-form-row-container" data-js="container">
														<div class="col-sm-6 col-lg-4">
															<div class="d-flex flex-row">
																<div class="btn-group-toggle mt-1 w-100" data-toggle="buttons">
																	<label class="btn btn-sm btn-outline-secondary w-100 text-right" id="block-{$BLOCK_INDEX}-{$FIELD_MODEL->getName()}-label">
																		<input aria-pressed="false" autocomplete="off" type="checkbox" id="selectRow{$FIELD_MODEL->getName()}"
																			title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}" data-field-name="{$FIELD_MODEL->getName()}"
																			class="selectRow" {if $FIELD_MODEL->isEditableReadOnly()} disabled{/if}>&nbsp;
																		{if $FIELD_MODEL->isMandatory() eq true}
																			<span class="redColor">*</span>
																		{/if}
																		{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}:
																	</label>
																</div>
																{if method_exists($FIELD_MODEL->getUITypeModel(), 'setValueFromMassEdit')}
																	{assign var=MASS_EDIT_SPECIAL_FIELD_NAME value="overwritten_{$FIELD_MODEL->getName()}"}
																	<div class="btn-group-toggle mt-1 js-popover-tooltip" data-js="popover" data-toggle="buttons"
																		data-trigger="hover focus" data-content="{\App\Language::translate("LBL_MASS_EDIT_INCLUDE_INFO")}">
																		<label class="btn btn-outline-info btn-sm">
																			<span class="fas fa-plus"></span>
																			<input type="checkbox" value="{$MASS_EDIT_SPECIAL_FIELD_NAME}" name="{$MASS_EDIT_SPECIAL_FIELD_NAME}" id="{$MASS_EDIT_SPECIAL_FIELD_NAME}">
																		</label>
																	</div>
																{/if}
															</div>
														</div>
														<div class="col-sm-6 col-lg-8">
															<div class="fieldValue" id="block-{$BLOCK_INDEX}-{$FIELD_MODEL->getName()}-input">
																{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE) VIEW = 'MassEdit' RECORD=null}
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
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>

			</div>
		</div>
	</div>
{/strip}
