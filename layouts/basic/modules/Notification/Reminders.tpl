{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<style>
		{foreach item=VALUE key=NAME from=$COLORS}
			.headingColor{$NAME}{
				background-color: {$VALUE} !important;
				border-color: {$VALUE};
				background: linear-gradient(-10deg, #fff, transparent 70%)
			}
		{/foreach}
	</style>
	<div class="remindersContent">
		{foreach item=RECORD from=$RECORDS}
			<div class="js-toggle-panel card ml-0 mr-3 mt-2 headingColor{$RECORD->get('notification_type')} js-notification-panel" data-js="click" data-record="{$RECORD->getId()}">
				<div class="card-body row p-0">
					<div class="col-2 notificationIcon pl-3">
						<span class="fas {if $RECORD->get('notification_type') eq 'PLL_SYSTEM'}fa-hdd{else}fa-user{/if}" aria-hidden="true"></span>
					</div>
					<div class="col-10 notiContent pb-1">
						<div class="d-flex justify-content-between py-1">
							<div class="paddingLRZero font-larger">
								<strong>{\App\Language::translate($RECORD->get('notification_type'),$MODULE_NAME)}</strong>
							</div>
							<div class="paddingLRZero font-larger">
								<strong>{$RECORD->getDisplayValue('createdtime')}</strong>
							</div>
						</div>
						<div class="d-flex">
							{$RECORD->getTitle()}
							<div class="moreContent">
								{assign var=FULL_TEXT value=$RECORD->getMessage()}
								<span class="teaserContent">
									{if strip_tags($FULL_TEXT)|strlen <= 200}
										{$FULL_TEXT}
										{assign var=SHOW_BUTTON value=false}
									{else}
										{\App\TextParser::htmlTruncate($FULL_TEXT,200)}
										{assign var=SHOW_BUTTON value=true}
									{/if}
								</span>
								{if $SHOW_BUTTON}
									<span class="fullContent d-none">
										{$FULL_TEXT}
									</span>
									&nbsp;<button type="button" class="btn btn-info btn-sm moreBtn" data-on="{\App\Language::translate('LBL_MORE_BTN')}" data-off="{\App\Language::translate('LBL_HIDE_BTN')}">{\App\Language::translate('LBL_MORE_BTN')}</button>
								{/if}
							</div>
						</div>
						<div class="d-flex">
							{if $RECORD->get('link')}
								{\App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get('link')))}:&nbsp;{$RECORD->getDisplayValue('link')}<br />
							{/if}
							{if $RECORD->get('linkextend')}
								{\App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get('linkextend')))}:&nbsp;{$RECORD->getDisplayValue('linkextend')}<br />
							{/if}
							{if $RECORD->get('process')}
								{\App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get('process')))}:&nbsp;{$RECORD->getDisplayValue('process')}<br />
							{/if}
							{if $RECORD->get('subprocess')}
								{\App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get('subprocess')))}:&nbsp;{$RECORD->getDisplayValue('subprocess')}
							{/if}
						</div>
						<div class="d-flex justify-content-between">
							<div>
								<strong class="">{\App\Language::translate('Created By',$MODULE_NAME)}: {$RECORD->getCreatorUser()}</strong>
							</div>
							<div>
								<button type="button" class="btn btn-success btn-sm js-set-marked" data-js="click" title="{\App\Language::translate('LBL_MARK_AS_READ',$MODULE_NAME)}">
									<span class="fas fa-check" aria-hidden="true"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		{foreachelse}
			<div class="alert alert-info">
				{\App\Language::translate('LBL_NO_UNREAD_NOTIFICATIONS',$MODULE_NAME)}
			</div>
		{/foreach}
	</div>
{/strip}
