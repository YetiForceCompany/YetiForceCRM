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
	<div id="convertLeadContainer" class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				{if !$CONVERT_LEAD_FIELDS['Accounts']}
					<input type="hidden" id="convertLeadErrorTitle" value="{App\Language::translate('LBL_CONVERT_LEAD_ERROR_TITLE',$MODULE)}" />
					<input id="convertLeadError" class="convertLeadError" type="hidden" value="{App\Language::translate('LBL_CONVERT_LEAD_ERROR',$MODULE)}" />
				{else}
					<div class="modal-header contentsBackground">
						<h5 class="modal-title">
							<span class="fas fa-exchange-alt mr-1"></span>
							{App\Language::translate('LBL_CONVERT_LEAD', $MODULE)}: {$RECORD->getName()}
						</h5>
						<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form class="form-horizontal" id="convertLeadForm" method="post" action="index.php">
						<input type="hidden" name="module" value="{$MODULE}" />
						<input type="hidden" name="view" value="SaveConvertLead" />
						<input type="hidden" name="record" value="{$RECORD->getId()}" />
						<input type="hidden" name="modules" value='' />
						<input type="hidden" name="create_account" value="{if $CONVERSION_CONFIG['create_always'] eq 'true'}1{/if}" />
						<div class="modal-body accordion" id="leadAccordion">
							{foreach item=MODULE_FIELD_MODEL key=MODULE_NAME from=$CONVERT_LEAD_FIELDS}
								<div class="accordion-group convertLeadModules">
									<div class="header accordion-heading">
										<div data-parent="#leadAccordion" data-toggle="collapse" class="card-header py-0 accordion-toggle table-bordered moduleSelection" href="#{$MODULE_NAME}_FieldInfo">
											<div class="form-control-plaintext checkbox">
												<label>
													<input id="{$MODULE_NAME}Module" class="convertLeadModuleSelection alignBottom{if $MODULE_NAME === 'Accounts'} d-none{/if}" data-module="{App\Language::translate($MODULE_NAME,$MODULE_NAME)}" value="{$MODULE_NAME}" type="checkbox" checked="" />
													{assign var=SINGLE_MODULE_NAME value="SINGLE_$MODULE_NAME"}
													<span class="card-title">&nbsp;{App\Language::translate('LBL_CREATING_NEW', $MODULE_NAME)}&nbsp;{App\Language::translate($SINGLE_MODULE_NAME, $MODULE_NAME)}</span>
												</label>
												<span class="float-right mr-2"><i class="iconArrow fas {if $CONVERT_LEAD_FIELDS['Accounts'] && $MODULE_NAME === "Accounts"}fa-chevron-up {else}fa-chevron-down {/if}alignBottom"></i></span>
											</div>
										</div>
									</div>
									<div id="{$MODULE_NAME}_FieldInfo" class="{$MODULE_NAME}_FieldInfo accordion-body collapse fieldInfo{if $MODULE_NAME eq 'Accounts'} in{/if}">
										<table class="table table-bordered moduleBlock">
											{foreach item=FIELD_MODEL from=$MODULE_FIELD_MODEL}
												<tr>
													<td class="fieldLabel col-5">
														<label class="muted float-right">
															{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
															{App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
														</label>
													</td>
													<td class="fieldValue col-7">
														{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName())}
													</td>
												</tr>
											{/foreach}
										</table>
									</div>
								</div>
							{/foreach}
							<div class="overflowVisible">
								<table class="table table-bordered">
									{assign var=FIELD_MODEL value=$ASSIGN_TO}
									<tr>
										<td class="fieldLabel col-5">
											<label class="muted float-right">
												<span class="redColor">*</span> {App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
												{if $FIELD_MODEL->isMandatory() eq true} {/if}
											</label>
										</td>
										<td class="fieldValue col-7">
											{if $FIELD_MODEL->getUIType() eq '53'}
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
											{/if}
										</td>
									</tr>
								</table>
							</div>
						</div>
						{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
					</form>
				{/if}
			</div>
		</div>
	</div>
{/strip}
