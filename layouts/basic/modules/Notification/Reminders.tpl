{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Notification-Reminders -->
	<style>
		{foreach item=VALUE key=NAME from=$COLORS}
			.headingColor{$NAME} {
			background-color: {$VALUE} !important;
			border-color: {$VALUE};
			background: linear-gradient(-10deg, #fff, transparent 70%)
			}

		{/foreach}
	</style>
	<div class="remindersContent pb-5">
		{foreach item=RECORD from=$RECORDS}
			<div class="js-toggle-panel card ml-0 mr-3 mt-2 headingColor{$RECORD->get('notification_type')} js-notification-panel"
				data-js="click" data-record="{$RECORD->getId()}">
				<div class="card-body row p-0">
					<div class="col-2 notificationIcon pl-3">
						<span class="fas {if $RECORD->get('notification_type') eq 'PLL_SYSTEM'}fa-hdd{else}fa-user{/if}"
							aria-hidden="true"></span>
					</div>
					<div class="col-10 notiContent small pb-1">
						<div class="d-flex justify-content-between py-1 pb-2">
							<div class="paddingLRZero font-small">
								<strong>{\App\Language::translate($RECORD->get('notification_type'),$MODULE_NAME)}</strong>
							</div>
							<div class="paddingLRZero font-small">
								<strong>{$RECORD->getDisplayValue('createdtime')}</strong>
							</div>
						</div>
						<div>
							<div class="font-weight-normal">
								{$RECORD->getTitle()}
							</div>
							<div class="font-weight-light font-italic">
								{$RECORD->getMessage()}
							</div>
						</div>
						<div class="d-flex flex-column">
							{foreach from=['link','linkextend','process','subprocess','subprocess_sl'] item=FIELD_NAME}
								{if $RECORD->get($FIELD_NAME) && \App\Record::isExists($RECORD->get($FIELD_NAME))}
									{\App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get($FIELD_NAME)))}:&nbsp;{$RECORD->getDisplayValue($FIELD_NAME)}
								{/if}
							{/foreach}
						</div>
						<div class="d-flex justify-content-between">
							<div>
								<strong class="">{\App\Language::translate('Created By',$MODULE_NAME)}
									: {$RECORD->getCreatorUser()}</strong>
							</div>
							<div>
								<button type="button" class="btn btn-success btn-sm js-set-marked" data-js="click"
									title="{\App\Language::translate('LBL_MARK_AS_READ',$MODULE_NAME)}">
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
		<div class="tpl-remiders-bottom-buttons btn-group btn-toolbar mr-md-2 flex-md-nowrap">
			<a class="btn btn-light" role="button" data-content="" href="index.php?module=Notification&amp;view=List">
				<span class="fas fa-list"></span>
			</a>
			<button type="button" class="btn btn-light js-popover-tooltip showModal" data-js="popover"
				data-placement="top"
				data-content="{\App\Language::translate('LBL_NOTIFICATION_SETTINGS',$MODULE_NAME)}"
				data-target="focus hover" data-url="index.php?module=Notification&amp;view=NotificationConfig">
				<span class="fas fa-cog"></span>
			</button>
		</div>
	</div>
	<!-- /tpl-Notification-Reminders -->
{/strip}
