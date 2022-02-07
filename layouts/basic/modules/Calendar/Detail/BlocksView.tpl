{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Detail-BlocksView -->
	{include file=\App\Layout::getTemplatePath('Detail/BlocksView.tpl', 'Vtiger') RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
	{assign var="IS_HIDDEN" value=false}
	<div class="detailViewTable">
		<div class="js-toggle-panel c-panel" data-js="click" data-label="LBL_INVITE_RECORDS">
			<div class="blockHeader c-panel__header">
				<span class="js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id='inviteParticipantBlockId'></span>
				<span class="js-block-toggle fas fa-angle-down m-2 {if $IS_HIDDEN}d-none{/if}" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id='inviteParticipantBlockId'></span>
				<h5>{\App\Language::translate('LBL_INVITE_RECORDS',$MODULE_NAME)}</h5>
			</div>
			<div class="blockContent c-panel__body {if $IS_HIDDEN} d-none{/if}">
				<div class="w-100">
					<div class="form-row border-right">
						<div class="fieldLabel u-border-bottom-label-md u-border-right-0-md c-panel__label col-lg-3 {$WIDTHTYPE} text-right">
							<label class="u-text-small-bold">{\App\Language::translate('LBL_INVITE_RECORDS',$MODULE_NAME)}</label></td>
						</div>
						<div class="fieldValue col-sm-12 col-lg-9 {$WIDTHTYPE} d-flex flex-wrap flex-row justify-content-start align-items-left">
							{foreach key=KEY item=INVITIE from=$INVITIES_SELECTED}
								{include file=\App\Layout::getTemplatePath('InviteRow.tpl', $MODULE_NAME) IS_VIEW=true}
							{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Calendar-Detail-BlocksView -->
{/strip}
