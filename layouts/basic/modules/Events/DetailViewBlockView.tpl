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
	{include file=\App\Layout::getTemplatePath('DetailViewBlockView.tpl', 'Vtiger') RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
    {assign var="IS_HIDDEN" value=false}
	<div class="detailViewTable">
		<div class="js-toggle-panel c-panel" data-js="click" data-label="{$BLOCK_LABEL}">
			<div class="blockHeader c-panel__header">
				<span class="js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}" data-js="click"alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id='INVITE_USER_BLOCK_ID'></span>
				<span class="js-block-toggle fas fa-angle-down m-2 {if $IS_HIDDEN}d-none{/if}" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id='INVITE_USER_BLOCK_ID'></span>
				<h5>{\App\Language::translate('LBL_INVITE_RECORDS',$MODULE_NAME)}</h5>
			</div>
			<div class="blockContent c-panel__body {if $IS_HIDDEN} d-none{/if}">
				<div class="w-100">
					<div class="form-row border-right">
						<div class="fieldLabel u-border-bottom-label-md u-border-right-0-md c-panel__label col-lg-3 {$WIDTHTYPE} text-right">
							<label class="u-text-small-bold">{\App\Language::translate('LBL_INVITE_RECORDS',$MODULE_NAME)}</label></td>
						</div>
						<div class="fieldValue col-sm-12 col-lg-9 {$WIDTHTYPE}">
							{foreach key=KEY item=INVITIE from=$INVITIES_SELECTED}
								{assign var=LABEL value=''}
								{assign var=TITLE value=''}
								{if $INVITIE['crmid']}
									{assign var=INVITIE_RECORD value=vtlib\Functions::getCRMRecordMetadata($INVITIE['crmid'])}
									{assign var=LABEL value=$INVITIE_RECORD['label']}
									{assign var=TITLE value=\App\Language::translateSingularModuleName($INVITIE_RECORD['setype'])|cat:': '|cat:$LABEL|cat:' - '|cat:$INVITIE['email']}
									{assign var=ICON value='<span class="userIcon-'|cat:$INVITIE_RECORD['setype']|cat:'"></span>'}
								{else}
									{assign var=LABEL value=$INVITIE['email']}
									{assign var=ICON value='<span class="fas fa-envelope"></span>'}
								{/if}
								<div>
									{assign var=STATUS_LABEL value=Events_Record_Model::getInvitionStatus($INVITIE['status'])}
									{if $INVITIE['status'] == '1'}
										<span class="fas fa-check-circle js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE_NAME)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
									{elseif $INVITIE['status'] == '2'}
										<span class="fas fa-minus-circle js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE_NAME)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
									{else}
										<span class="fas fa-question-circle js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE_NAME)}"></span>
									{/if}&nbsp;
									<span class="inviteName {if $TITLE}js-popover-tooltip{/if}" data-js="popover" data-content="{htmlentities($ICON)}&nbsp;{$TITLE}">{$LABEL}</span>
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
