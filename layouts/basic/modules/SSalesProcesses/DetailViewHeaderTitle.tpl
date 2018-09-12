{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="d-flex flex-wrap flex-md-nowrap px-3 w-100">
		<div class="u-min-w-md-70 w-100">
			<div class="moduleIcon">
				<span class="o-detail__icon js-detail__icon u-cursor-pointer userIcon-{$MODULE}"></span>
				{if AppConfig::module($MODULE_NAME, 'COUNT_IN_HIERARCHY')}
					<span class="hierarchy">
						<span class="badge {if $RECORD->get('active')} bgGreen {else} bgOrange {/if}"></span>
					</span>
				{/if}
			</div>
			<div class="pl-1">
				<div class="d-flex flex-nowrap align-items-center js-popover-tooltip" data-ellipsis="true" data-content="{$RECORD->getName()}" data-toggle="popover" data-js="tooltip">
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
				{assign var=RELATED_TO value=$RECORD->get('related_to')}
				{if !empty($RELATED_TO)}
					<div class="js-popover-tooltip d-flex flex-nowrap align-items-center" data-ellipsis="true" data-content="{$RECORD->getDisplayValue('related_to')}" data-toggle="popover" data-js="tooltip">
						<span class="mr-1 text-muted u-white-space-nowrap">{\App\Language::translate('SINGLE_Accounts',$MODULE_NAME)}
							:</span>
						<span class="js-popover-text" data-js="clone">{$RECORD->getDisplayValue('related_to')}</span>
					</div>
				{/if}
				<div class="js-popover-tooltip d-flex flex-nowrap align-items-center" data-ellipsis="true" data-content="{$RECORD->getDisplayValue('assigned_user_id')}" data-toggle="popover" data-js="tooltip">
					<span class="mr-1 text-muted u-white-space-nowrap">
						{\App\Language::translate('Assigned To',$MODULE_NAME)}:
					</span>
					<span class="js-popover-text" data-js="clone">{$RECORD->getDisplayValue('assigned_user_id')}</span>
					<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
				</div>
				{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
				{if $SHOWNERS != ''}
					<div class="js-popover-tooltip d-flex flex-nowrap align-items-center" data-ellipsis="true" data-content='{$SHOWNERS}' data-toggle="popover" data-js="tooltip">
						<span class="mr-1 text-muted u-white-space-nowrap">
							{\App\Language::translate('Share with users',$MODULE_NAME)}:
						</span>
						<span class="js-popover-text" data-js="clone">{$SHOWNERS}</span>
						<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
					</div>
				{/if}
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Detail/HeaderFields.tpl', $MODULE_NAME) HEADER_TYPE=value}
	</div>
	{include file=\App\Layout::getTemplatePath('Detail/HeaderFields.tpl', $MODULE_NAME) HEADER_TYPE=process}
{/strip}
