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
<div id="convertLeadContainer" class='modelContainer'>
	{if !$CONVERT_LEAD_FIELDS['Accounts'] && !$CONVERT_LEAD_FIELDS['Contacts']}
		<input type="hidden" id="convertLeadErrorTitle" value="{vtranslate('LBL_CONVERT_LEAD_ERROR_TITLE',$MODULE)}"/>
		<input id="convertLeadError" class="convertLeadError" type="hidden" value="{vtranslate('LBL_CONVERT_LEAD_ERROR',$MODULE)}"/>
	{else}
		<div class="modal-header contentsBackground">
            <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
            <h3>{vtranslate('LBL_CONVERT_LEAD', $MODULE)} : {$RECORD->getName()}</h3>
			</div>
		</div>
		<form class="form-horizontal" id="convertLeadForm" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}"/>
			<input type="hidden" name="view" value="SaveConvertLead"/>
			<input type="hidden" name="record" value="{$RECORD->getId()}"/>
			<input type="hidden" name="modules" value=''/>
			<div class="modal-body accordion" id="leadAccordion">
				{foreach item=MODULE_FIELD_MODEL key=MODULE_NAME from=$CONVERT_LEAD_FIELDS}
					<div class="accordion-group convertLeadModules">
						<div class="header accordion-heading">
							<div data-parent="#leadAccordion" data-toggle="collapse" class="accordion-toggle table-bordered moduleSelection" href="#{$MODULE_NAME}_FieldInfo">
								{if $ACCOUNT_FIELD_MODEL->isMandatory()}
									<input type="hidden" id="oppAccMandatory" value={$ACCOUNT_FIELD_MODEL->isMandatory()} />
								{/if}
								{if $CONTACT_FIELD_MODEL->isMandatory()}
									<input type="hidden" id="oppConMandatory" value={$CONTACT_FIELD_MODEL->isMandatory()} />
								{/if}
								{if $CONTACT_ACCOUNT_FIELD_MODEL->isMandatory()}
									<input type="hidden" id="conAccMandatory" value={$CONTACT_ACCOUNT_FIELD_MODEL->isMandatory()} />
								{/if}
								<input id="{$MODULE_NAME}Module" class="convertLeadModuleSelection alignBottom" data-module="{vtranslate($MODULE_NAME,$MODULE_NAME)}" value="{$MODULE_NAME}" type="checkbox" {if $MODULE_NAME == 'Accounts' && $CONTACT_ACCOUNT_FIELD_MODEL && $CONTACT_ACCOUNT_FIELD_MODEL->isMandatory()} disabled="disabled" {/if} checked="" />
									{assign var=SINGLE_MODULE_NAME value="SINGLE_$MODULE_NAME"}
									<span style="position:relative;top:2px;">&nbsp;&nbsp;&nbsp;{vtranslate('LBL_CREATE', $MODULE)}&nbsp;{vtranslate($SINGLE_MODULE_NAME, $MODULE_NAME)}</span>
									<span class="pull-right"><i class="iconArrow{if $CONVERT_LEAD_FIELDS['Accounts'] && $MODULE_NAME == "Accounts"} icon-inverted icon-chevron-up {elseif !$CONVERT_LEAD_FIELDS['Accounts'] && $MODULE_NAME == "Contacts"} icon-inverted icon-chevron-up {else} icon-inverted icon-chevron-down {/if}alignBottom"></i></span>
							</div>
						</div>
						<div id="{$MODULE_NAME}_FieldInfo" class="{$MODULE_NAME}_FieldInfo accordion-body collapse fieldInfo in">
							<table class="table table-bordered moduleBlock">
								{foreach item=FIELD_MODEL from=$MODULE_FIELD_MODEL}
								<tr>
									<td class="fieldLabel">
										<label class='muted pull-right marginRight10px'>
											{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if} 
                                            {vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}
											 
										</label>
									</td>
									<td class="fieldValue">
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
							<td class="fieldLabel">
								<label class='muted pull-right'>
									<span class="redColor">*</span> {vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}
									{if $FIELD_MODEL->isMandatory() eq true} {/if}
								</label>
							</td>
							<td class="fieldValue">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
							</td>
						</tr>
						<!--
						<tr>
							<td class="fieldLabel">
                                <label class='muted pull-right'>
                                    {vtranslate('LBL_TRANSFER_RELATED_RECORD', $MODULE)}
                                </label>
                            </td>
							<td class="fieldValue">
								{foreach item=MODULE_FIELD_MODEL key=MODULE_NAME from=$CONVERT_LEAD_FIELDS}
									{if $MODULE_NAME != 'Potentials'}
										<input type="radio" id="transfer{$MODULE_NAME}" class="transferModule alignBottom" name="transferModule" value="{$MODULE_NAME}"
										{if $CONVERT_LEAD_FIELDS['Contacts'] && $MODULE_NAME=="Contacts"} checked="" {elseif !$CONVERT_LEAD_FIELDS['Contacts'] && $MODULE_NAME=="Accounts"} checked="" {/if}/>
										{if $MODULE_NAME eq 'Contacts'}
											&nbsp; {vtranslate('SINGLE_Contacts',$MODULE_NAME)} &nbsp;&nbsp;
										{else}
											&nbsp; {vtranslate('SINGLE_Accounts',$MODULE_NAME)} &nbsp;&nbsp;
										{/if}
									{/if}
								{/foreach}
							</td>
						</tr>
						-->
					</table>
				</div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>
	{/if}
</div>
{/strip}