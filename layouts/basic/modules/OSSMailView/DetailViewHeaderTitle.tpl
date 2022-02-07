{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="d-flex flex-wrap flex-md-nowrap px-md-3 px-1 w-100">
		<div class="u-min-w-md-70 w-100">
			<div class="moduleIcon">
				<span class="o-detail__icon js-detail__icon yfm-{$MODULE}"></span>
			</div>
			<div class="pl-1">
				<div class="d-flex flex-nowrap align-items-center js-popover-tooltip--ellipsis-icon" data-content="{\App\Purifier::encodeHtml($RECORD->getName())}" data-toggle="popover" data-js="popover | mouseenter">
					<h4 class="recordLabel h6 mb-0 js-popover-text" data-js="clone">
						<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
					</h4>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE && $RECORD_STATE !== 'Active'}
						{assign var=COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
						<div class="badge badge-secondary ml-1" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};" {/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</div>
					{/if}
				</div>
				<span class="text-muted u-white-space-nowrap">
					<small><em>{\App\Language::translate('Sent','OSSMailView')}</em></small>
					<span><small><em>&nbsp;{$RECORD->getDisplayValue('createdtime')}</em></small></span>
				</span>
				{include file=\App\Layout::getTemplatePath('Detail/HeaderValues.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="ml-md-2 pr-md-2 u-min-w-md-30 w-100">
			{include file=\App\Layout::getTemplatePath('Detail/HeaderButtons.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('Detail/HeaderHighlights.tpl', $MODULE_NAME)}
		</div>
	</div>
	{include file=\App\Layout::getTemplatePath('Detail/HeaderProgress.tpl', $MODULE_NAME)}
{/strip}
