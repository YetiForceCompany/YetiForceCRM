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
					<input type="hidden" id="convertLeadErrorTitle" value="{App\Language::translate('LBL_CONVERT_LEAD_ERROR_TITLE',$MODULE)}"/>
					<input id="convertLeadError" class="convertLeadError" type="hidden" value="{App\Language::translate('LBL_CONVERT_LEAD_ERROR',$MODULE)}"/>
				{else}
					<div class="modal-header contentsBackground">
						<button data-dismiss="modal" class="close" title="{App\Language::translate('LBL_CLOSE')}">&times;</button>
						<h3 class="modal-title">{App\Language::translate('LBL_CONVERT_LEAD', $MODULE)}: {$RECORD->getName()}</h3>
					</div>
					<form class="form-horizontal" id="convertLeadForm" method="post" action="index.php">
						<input type="hidden" name="module" value="{$MODULE}"/>
						<input type="hidden" name="view" value="SaveConvertLead"/>
						<input type="hidden" name="record" value="{$RECORD->getId()}"/>
						<input type="hidden" name="modules" value=''/>
						<input type="hidden" name="create_account" value="{if $CONVERSION_CONFIG['create_always'] eq 'true'}1{/if}" />
						<div class="modal-body accordion" id="leadAccordion">
							{foreach item=MODULE_FIELD_MODEL key=MODULE_NAME from=$CONVERT_LEAD_FIELDS}
								<div class="accordion-group convertLeadModules">
									<div class="header accordion-heading">
										<div data-parent="#leadAccordion" data-toggle="collapse" class="panel-heading paddingTBZero accordion-toggle table-bordered moduleSelection" href="#{$MODULE_NAME}_FieldInfo">
											<div class="form-control-static checkbox">
												<label>
													<input id="{$MODULE_NAME}Module" class="convertLeadModuleSelection alignBottom{if $MODULE_NAME == 'Accounts'} hide{/if}" data-module="{App\Language::translate($MODULE_NAME,$MODULE_NAME)}" value="{$MODULE_NAME}" type="checkbox" checked="" />
													{assign var=SINGLE_MODULE_NAME value="SINGLE_$MODULE_NAME"}
													<span class="panel-title">&nbsp;{App\Language::translate('LBL_CREATING_NEW', $MODULE_NAME)}&nbsp;{App\Language::translate($SINGLE_MODULE_NAME, $MODULE_NAME)}</span>
												</label>
												<span class="pull-right"><i class="iconArrow{if $CONVERT_LEAD_FIELDS['Accounts'] && $MODULE_NAME == "Accounts"} glyphicon glyphicon-chevron-up {else} glyphicon glyphicon-chevron-down {/if}alignBottom"></i></span>
											</div>	
										</div>
									</div>
									<div id="{$MODULE_NAME}_FieldInfo" class="{$MODULE_NAME}_FieldInfo accordion-body collapse fieldInfo{if $MODULE_NAME eq 'Accounts'} in{/if}">
										<table class="table table-bordered moduleBlock">
											{foreach item=FIELD_MODEL from=$MODULE_FIELD_MODEL}
												<tr>
													<td class="fieldLabel col-xs-5">
														<label class='muted pull-right marginRight10px'>
															{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if} 
															{App\Language::translate($FIELD_MODEL->get('label'), $MODULE_NAME)}

														</label>
													</td>
													<td class="fieldValue col-xs-7">
														{include file=$FIELD_MODEL->getUITypeModel()->getTemplateName()|@vtemplate_path}
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
										<td class="fieldLabel col-xs-5">
											<label class='muted pull-right'>
												<span class="redColor">*</span> {App\Language::translate($FIELD_MODEL->get('label'), $MODULE_NAME)}
												{if $FIELD_MODEL->isMandatory() eq true} {/if}
											</label>
										</td>
										<td class="fieldValue col-xs-7">
											{if $FIELD_MODEL->get('uitype') eq '53'}
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME)}
											{/if}
										</td>
									</tr>
								</table>
							</div>
						</div>
						{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
					</form>
				{/if}
			</div>
		</div>
	</div>
{/strip}
