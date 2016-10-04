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
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
    {include file='DetailViewBlockView.tpl'|@vtemplate_path:'Vtiger' RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}

    {assign var="IS_HIDDEN" value=false}
	<div class="detailViewTable">
		<div class="panel panel-default row no-margin" data-label="{$BLOCK_LABEL}">
			<div class="row blockHeader panel-heading no-margin">
				<div class="iconCollapse">
					<span class="cursorPointer blockToggle glyphicon glyphicon-menu-right {if !($IS_HIDDEN)}hide{/if}" alt="{vtranslate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id='INVITE_USER_BLOCK_ID'></span>
					<span class="cursorPointer blockToggle glyphicon glyphicon glyphicon-menu-down {if $IS_HIDDEN}hide{/if}" alt="{vtranslate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id='INVITE_USER_BLOCK_ID'></span>
					<h4>{vtranslate('LBL_INVITE_RECORDS',$MODULE_NAME)}</h4>
				</div>
			</div>
			<div class="col-xs-12 noSpaces panel-body blockContent {if $IS_HIDDEN} hide{/if}">
				<div class="col-xs-12 paddingLRZero fieldRow">
					<div class="col-md-6 col-xs-12 fieldsLabelValue paddingLRZero">
						<div class="fieldLabel col-sm-5 col-xs-12 {$WIDTHTYPE}">
							<label class="muted pull-right marginRight10px">{vtranslate('LBL_INVITE_RECORDS',$MODULE_NAME)}</label></td>
						</div>
						<div class="fieldValue col-sm-7 col-xs-12 {$WIDTHTYPE}">
							{foreach key=KEY item=INVITIE from=$INVITIES_SELECTED}
								{assign var=LABEL value=''}
								{assign var=TITLE value=''}
								{if $INVITIE['crmid']}
									{assign var=INVITIE_RECORD value=vtlib\Functions::getCRMRecordMetadata($INVITIE['crmid'])}
									{assign var=LABEL value=$INVITIE_RECORD['label']}
									{assign var=TITLE value=Vtiger_Language_Handler::getTranslateSingularModuleName($INVITIE_RECORD['setype'])|cat:': '|cat:$LABEL|cat:' - '|cat:$INVITIE['email']}
									{assign var=ICON value='<span class="userIcon-'|cat:$INVITIE_RECORD['setype']|cat:'" aria-hidden="true"></span>'}
								{else}
									{assign var=LABEL value=$INVITIE['email']}
									{assign var=ICON value='<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>'}
								{/if}
								<div>
									{assign var=STATUS_LABEL value=Events_Record_Model::getInvitionStatus($INVITIE['status'])}
									{if $INVITIE['status'] == '1'}
										<span class="glyphicon glyphicon-ok-sign popoverTooltip" data-placement="top" data-content="{vtranslate($STATUS_LABEL,$MODULE_NAME)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}" aria-hidden="true"></span>
									{elseif $INVITIE['status'] == '2'}
										<span class="glyphicon glyphicon-minus-sign popoverTooltip" data-placement="top" data-content="{vtranslate($STATUS_LABEL,$MODULE_NAME)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}" aria-hidden="true"></span>
									{else}
										<span class="glyphicon glyphicon-question-sign popoverTooltip" data-placement="top" data-content="{vtranslate($STATUS_LABEL,$MODULE_NAME)}" aria-hidden="true"></span>
									{/if}&nbsp;
									<span class="inviteName {if $TITLE}popoverTooltip{/if}" data-content="{htmlentities($ICON)}&nbsp;{$TITLE}">{$LABEL}</span>
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
