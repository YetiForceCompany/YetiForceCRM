{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-EditViewBlocks -->
	<input type="hidden" id="extendModules" value="Calendar">
	{include file=\App\Layout::getTemplatePath('EditViewBlocks.tpl', 'Vtiger')}
	<input type="hidden" name="userChangedEndDateTime" value="{$USER_CHANGED_END_DATE_TIME}"/>
	{assign var=IS_HIDDEN value=false}
	{assign var=INVITIES_SELECTED value=$RECORD->getInvities()}
	<div class="js-toggle-panel c-panel c-panel--edit row mx-1 mb-3" data-js="click" data-label="LBL_INVITE_RECORDS">
		<div class="blockHeader c-panel__header align-items-center">
			<div class="col-md-8 form-row pl-1">
				<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}" data-js="click" data-mode="hide"></span>
				<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 {if ($IS_HIDDEN)}d-none{/if}" data-js="click" data-mode="show"></span>
				<h5>{\App\Language::translate('LBL_INVITE_RECORDS', $MODULE)}</h5>
			</div>
			<div class="col-md-4 fieldRow">
				<input type="text" title="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE)}" placeholder="{\App\Language::translate('LBL_SELECT_INVITE', $MODULE)}" class="form-control form-control-sm inviteesSearch"/>
			</div>
		</div>
		<div class="c-panel__body c-panel__body--edit blockContent js-block-content {if $IS_HIDDEN}d-none{/if}" data-js="display">
			<div class="inviteesContent">
				<div class="d-none">
					{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE)}
				</div>
				{if !empty($INVITIES_SELECTED)}
					{foreach key=KEY item=INVITIE from=$INVITIES_SELECTED}
						{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE)}
					{/foreach}
				{/if}
			</div>
		</div>
	</div>
	<br/>
	<!-- /tpl-Calendar-EditViewBlocks -->
{/strip}
