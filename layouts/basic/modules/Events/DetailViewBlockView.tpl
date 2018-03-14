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
		<div class="card row no-margin" data-label="{$BLOCK_LABEL}">
			<div class="row blockHeader card-header m-0">
					<span class="cursorPointer blockToggle fas fa-angle-right {if !($IS_HIDDEN)}d-none{/if}" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id='INVITE_USER_BLOCK_ID'></span>
					<span class="cursorPointer blockToggle fas fa-angle-down {if $IS_HIDDEN}d-none{/if}" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id='INVITE_USER_BLOCK_ID'></span>
					<h4>{\App\Language::translate('LBL_INVITE_RECORDS',$MODULE_NAME)}</h4>
			</div>
			<div class="col-12 card-body p-0 blockContent {if $IS_HIDDEN} d-none{/if}">
				<div class="w-100">
					<div class="col-md-12 col-12 form-row m-0 fieldsLabelValue px-0">
						<div class="fieldLabel col-sm-5 col-md-3 col-12 {$WIDTHTYPE}">
							<label class="muted float-right">{\App\Language::translate('LBL_INVITE_RECORDS',$MODULE_NAME)}</label></td>
						</div>
						<div class="fieldValue ccol-sm-12 col-md-9 col-12 {$WIDTHTYPE}">
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
										<span class="fas fa-check-circle popoverTooltip" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE_NAME)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
									{elseif $INVITIE['status'] == '2'}
										<span class="fas fa-minus-circle popoverTooltip" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE_NAME)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
									{else}
										<span class="fas fa-question-circle popoverTooltip" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE_NAME)}"></span>
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
