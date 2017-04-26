{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
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
			<div class="panel headingColor{$RECORD->get('notification_type')}" data-record="{$RECORD->getId()}">
				<div class="panel-body padding0">
					<div class="col-xs-2 notificationIcon">
						<span class="glyphicon {if $RECORD->get('notification_type') eq 'PLL_SYSTEM'}glyphicon-hdd{else}glyphicon-user{/if}" aria-hidden="true"></span>
					</div>
					<div class="col-xs-10 paddingLR5 notiContent">
						<div class="col-xs-6 paddingLRZero marginTB3 font-larger">
							<strong class="pull-left">{\App\Language::translate($RECORD->get('notification_type'),$MODULE_NAME)}</strong>
						</div>
						<div class="col-xs-6 paddingLRZero marginTB3 font-larger">
							<strong class="pull-right">{$RECORD->getDisplayValue('createdtime')}</strong>
						</div>
						<div class="col-xs-12 paddingLRZero marginBottom5">
							{$RECORD->getTitle()}
							<div class="moreContent">
								{assign var=FULL_TEXT value=$RECORD->getMessage()}
								<span class="teaserContent">
									{Vtiger_Util_Helper::toSafeHTML($FULL_TEXT)|substr:0:100}
								</span>
								{if $FULL_TEXT|strlen > 100}
									<span class="fullContent hide">
										{$FULL_TEXT}
									</span>
									<button type="button" class="btn btn-info btn-xs moreBtn" data-on="{vtranslate('LBL_MORE_BTN')}" data-off="{vtranslate('LBL_HIDE_BTN')}">{vtranslate('LBL_MORE_BTN')}</button>
								{/if}
							</div>
						</div>
						<div class="col-xs-12 paddingLRZero marginBottom5 ">
							<div class="col-xs-10 paddingLRZero textOverflowEllipsis">
								<strong class="">{\App\Language::translate($RECORD->getModule()->getField('smcreatorid')->get('label'),$MODULE_NAME)}: {$RECORD->getCreatorUser()}</strong>
							</div>
							<div class="col-xs-2 paddingLRZero">
								<button type="button" class="btn btn-success btn-xs pull-right setAsMarked" title="{\App\Language::translate('LBL_MARK_AS_READ',$MODULE_NAME)}">
									<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		{foreachelse}
			<div class="alert alert-info">
				{vtranslate('LBL_NO_UNREAD_NOTIFICATIONS',$MODULE_NAME)}
			</div>
		{/foreach}
	</div>
{/strip}
