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
	<div id="convertLeadContainer" class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				{if !$CONVERT_LEAD_FIELDS['Accounts']}
					<input type="hidden" id="convertLeadErrorTitle"
						value="{App\Language::translate('LBL_CONVERT_LEAD_ERROR_TITLE',$MODULE)}" />
					<input id="convertLeadError" class="convertLeadError" type="hidden"
						value="{App\Language::translate('LBL_CONVERT_LEAD_ERROR',$MODULE)}" />
				{else}
					<div class="modal-header">
						<h5 class="modal-title">
							<span class="fas fa-exchange-alt mr-1"></span>
							{App\Language::translate('LBL_CONVERT_LEAD', $MODULE)}: {$RECORD->getName()}
						</h5>
						<button type="button" class="close" data-dismiss="modal"
							title="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form class="form-horizontal" id="convertLeadForm" method="post" action="index.php">
						<input type="hidden" name="module" value="{$MODULE}" />
						<input type="hidden" name="view" value="SaveConvertLead" />
						<input type="hidden" name="record" value="{$RECORD->getId()}" />
						<input type="hidden" name="modules" value='Accounts' />
						<input type="hidden" name="create_account"
							value="{if $CONVERSION_CONFIG['create_always'] eq 'true'}1{/if}" />
						<div class="quickCreateContent" id="leadAccordion">
							<div class="modal-body m-0">
								<div class="convertLeadModules">
									{foreach key=MODULE_NAME item=BLOCK_FIELDS from=$CONVERT_LEAD_FIELDS}
										{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
										<div class="js-toggle-panel c-panel c-panel--edit mb-3" data-label="{$MODULE_NAME}">
											{assign var=ACCOUNTS value=$CONVERT_LEAD_FIELDS['Accounts'] && $MODULE_NAME === 'Accounts'}
											<input id="{$MODULE_NAME}Module"
												class="convertLeadModuleSelection {if $MODULE_NAME === 'Accounts'}d-none{/if}"
												data-module="{App\Language::translate($MODULE_NAME,$MODULE_NAME)}"
												value="{$MODULE_NAME}" type="checkbox" checked="" />
											<div class="blockHeader c-panel__header align-items-center" data-toggle="collapse" data-target="#{$MODULE_NAME}_FieldInfo"
												aria-expanded="false" aria-controls="{$MODULE_NAME}_FieldInfo">
												<span class="col-3 text-right mr-2"><span class="fas {if $ACCOUNTS}fa-chevron-up{else}fa-chevron-down{/if}"></span></span>
												<label class="col-9 m-1">
													<h5 class="ml-2">{\App\Language::translate(\App\Language::getSingularModuleName($MODULE_NAME), $MODULE_NAME)}</h5>
												</label>
											</div>
											<div class="c-panel__body c-panel__body--edit blockContent js-block-content {$MODULE_NAME}_FieldInfo collapse js-collapse {if $ACCOUNTS}show{/if}"
												data-js="display" id="{$MODULE_NAME}_FieldInfo" data-parent="#leadAccordion">
												<div class="row">
													{assign var=COUNTER value=0}
													{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
														{if $FIELD_MODEL->getUIType() eq '20' || $FIELD_MODEL->getUIType() eq '300'}
															{if $COUNTER eq '1'}
															</div>
															<div class="row">
																{assign var=COUNTER value=0}
															{/if}
														{/if}
														{if $COUNTER eq 2}
														</div>
														<div class="row">
															{assign var=COUNTER value=1}
														{else}
															{assign var=COUNTER value=$COUNTER+1}
														{/if}
														<div class="{if $FIELD_MODEL->getUIType() neq "300"}col-sm-6{else} col-md-12 m-auto{/if}  row form-group align-items-center my-1 js-field-block-column{if $FIELD_MODEL->get('hideField')} d-none{/if}" data-field="{$FIELD_MODEL->getFieldName()}" data-js="container">
															{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL, 'Edit')}
															<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left {if $FIELD_MODEL->getUIType() neq "300"} text-xl-right {/if} u-text-small-bold">
																{if $FIELD_MODEL->isMandatory() eq true}
																	<span class="redColor">*</span>
																{/if}
																{if $HELPINFO_LABEL}
																	<a href="#" class="js-help-info float-right u-cursor-pointer"
																		title=""
																		data-placement="top"
																		data-content="{$HELPINFO_LABEL}"
																		data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}">
																		<span class="fas fa-info-circle"></span>
																	</a>
																{/if}
																{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
															</label>
															<div class="{$WIDTHTYPE} w-100 {if $FIELD_MODEL->getUIType() neq "300"} col-lg-12 col-xl-9 {/if} fieldValue" {if $FIELD_MODEL->getUIType() eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1}{elseif $FIELD_MODEL->getUIType() eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>
																{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) RECORD=null}
															</div>
														</div>
													{/foreach}
												</div>
											</div>
										</div>
									{/foreach}
								</div>
								<div class="card p-1">
									<div class="card-body p-1">
										{assign var=FIELD_MODEL value=$ASSIGN_TO}
										<div class="col-md-12 m-auto">
											<div class="row">
												<label class="my-0 col-xl-2 pr-0 fieldLabel text-lg-left text-xl-right u-text-small-bold align-self-center">
													{if $FIELD_MODEL->isMandatory() eq true}
														<span class="redColor">*</span>
													{/if}
													{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
												</label>
												<div class="w-100 col-xl-10 fieldValue">
													{if $FIELD_MODEL->getUIType() eq '53'}
														{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
													{/if}
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>
							{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
					</form>
				{/if}
			</div>
		</div>
	</div>
{/strip}
