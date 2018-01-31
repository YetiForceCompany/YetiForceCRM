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
	{include file=\App\Layout::getTemplatePath('EditViewBlocks.tpl', 'Vtiger')}
    <input type="hidden" name="userChangedEndDateTime" value="{$USER_CHANGED_END_DATE_TIME}" />
	<div class="panel panel-default row marginLeftZero marginRightZero blockContainer" data-label="{$BLOCK_LABEL}">
		<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
			<div class="col-md-8 fieldRow paddingLRZero">
				<div class="iconCollapse">
					<span class="cursorPointer blockToggle fas fa-angle-right {if !($IS_HIDDEN)}hide{/if}" data-mode="hide"></span>
					<span class="cursorPointer blockToggle glyphicon glyphicon glyphicon-menu-down {if ($IS_HIDDEN)}hide{/if}" data-mode="show"></span>
					<h4>{\App\Language::translate('LBL_INVITE_RECORDS', $MODULE)}</h4>
				</div>
			</div>
			<div class="col-md-4 fieldRow">
				<input type="text" title="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE)}" placeholder="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE)}" class="form-control inviteesSearch" />
			</div>
		</div>
		<div class="col-md-12 paddingLRZero panel-body blockContent {if $IS_HIDDEN}hide{/if}">
			<div class="inviteesContent">
				<div class="hide">
					{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE)}
				</div>
				{foreach key=KEY item=INVITIE from=$INVITIES_SELECTED}
					{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE)}
				{/foreach}
			</div>
		</div>
	</div>
	<br />
{/strip}
