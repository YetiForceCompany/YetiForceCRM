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
	<!-- tpl-Base-DetailViewHeaderTitle -->
	<div class="d-flex flex-wrap flex-md-nowrap px-md-3 px-1 w-100">
		<div class="u-min-w-md-70 w-100">
			{assign var=COUNT_IN_HIERARCHY value=App\Config::module($MODULE_NAME, 'COUNT_IN_HIERARCHY')}
			<div class="moduleIcon">
				<span class="o-detail__icon js-detail__icon yfm-{$MODULE_NAME}{if $COUNT_IN_HIERARCHY} u-cursor-pointer js-detail-hierarchy position-relative{/if}"></span>
				{if $COUNT_IN_HIERARCHY}
					<span class="hierarchy">
						<span class="badge {if $RECORD->get('active')} bgGreen {else} bgOrange {/if}"></span>
					</span>
				{/if}
			</div>
			<div class="pl-1">
				<div class="d-flex flex-nowrap align-items-center js-popover-tooltip--ellipsis-icon"
					data-content="{\App\Purifier::encodeHtml($RECORD->getName())}" data-toggle="popover" data-js="popover | mouseenter">
					<h4 class="recordLabel h6 mb-0 js-popover-text" data-js="clone">
						<span class="modCT_{$MODULE_NAME}">
							{\App\Utils\Completions::decode(Vtiger_Util_Helper::toVtiger6SafeHTML(\App\Purifier::decodeHtml($RECORD->getName())))}
						</span>
					</h4>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE && $RECORD_STATE !== 'Active'}
						{assign var=COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
						<span class="badge badge-secondary ml-1" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};" {/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								<span class="fas fa-trash-alt mr-2"></span>
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								<span class="fas fa-archive mr-2"></span>
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</span>
					{/if}
				</div>
				{include file=\App\Layout::getTemplatePath('Detail/HeaderValues.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="ml-md-2 pr-md-2 u-min-w-md-30 w-100">
			{include file=\App\Layout::getTemplatePath('Detail/HeaderButtons.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('Detail/HeaderHighlights.tpl', $MODULE_NAME)}
		</div>
	</div>
	{include file=\App\Layout::getTemplatePath('Detail/HeaderProgress.tpl', $MODULE_NAME)}
	<!-- /tpl-Base-DetailViewHeaderTitle -->
{/strip}
