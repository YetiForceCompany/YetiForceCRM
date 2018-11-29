{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="d-flex flex-wrap flex-md-nowrap px-3 w-100">
		<div class="u-min-w-md-70 w-100">
			<div class="moduleIcon">
				<span class="o-detail__icon js-detail__icon userIcon-{$MODULE}"></span>
			</div>
			<div class="pl-1">
				<div class="d-flex flex-nowrap align-items-center js-popover-tooltip--ellipsis-icon" data-content="{\App\Purifier::encodeHtml($RECORD->getName())}" data-toggle="popover" data-js="popover | mouseenter">
					<h4 class="recordLabel h6 mb-0 js-popover-text" data-js="clone">
						<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
					</h4>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE !== 'Active'}
						{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
						<div class="badge badge-secondary ml-1" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};"{/if}>
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
				<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center" data-content="{\App\Purifier::encodeHtml($RECORD->getDisplayValue('assigned_user_id'))}" data-toggle="popover" data-js="popover | mouseenter">
					<span class="text-muted mr-1 u-white-space-nowrap">{\App\Language::translate('LBL_OWNER')}:</span>
					<span class="js-popover-text" data-js="clone">
					{$RECORD->getDisplayValue('assigned_user_id')}
					</span>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Detail/HeaderFields.tpl', $MODULE_NAME)}
	</div>
	{include file=\App\Layout::getTemplatePath('Detail/HeaderProgress.tpl', $MODULE_NAME)}
{/strip}
