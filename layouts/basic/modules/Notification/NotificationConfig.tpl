{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Notification-NotificationConfig -->
	{assign var="CRON_ACTIVE" value=$CRON_INFO->getStatus()}
	{assign var="IS_PERMITTED" value=\App\Privilege::isPermitted($MODULE, 'ReceivingMailNotifications')}
	<div class="modal-header row">
		<div class="col-12 px-0 d-flex align-items-center">
			<span class="fas fa-paper-plane mr-2"></span>
			<h5 class="modal-title">{\App\Language::translate('LBL_WATCHING_MODULES', $MODULE)}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
			</button>
		</div>
		<div class="alert alert-info col-12 mb-0">
			{\App\Language::translate('LBL_CHANGE_SAVE', $MODULE)}
		</div>
	</div>
	<div class="modal-body table-responsive">
		<form id="sortingCustomView">
			<table class="table table-bordered table-sm js-watching-data-table" id="js-watching-data-table" data-js="dataTables">
				<thead>
					<tr>
						<th>
							<strong>{\App\Language::translate('LBL_MODULES', $MODULE)}</strong>
							<div class="d-flex align-items-center u-mt-2px float-right">
								{if $CRON_ACTIVE && $IS_PERMITTED}
									<span class="sentNoticeAll u-cursor-pointer d-flex">
										<span title="{\App\Language::translate('LBL_SELECT_ALL')}"
											class="fas {if $IS_ALL_EMAIL_NOTICE}fa-bell sandNoticeOn{else}fa-bell-slash sandNoticeOff{/if} fa-lg marginTB3 cursorPointer"></span>
									</span>
								{/if}
								<span class="d-flex ml-1">
									<input type="checkbox" {if $SELECT_ALL_MODULES} checked {/if} class="selectAllModules" title="{\App\Language::translate('LBL_SELECT_ALL')}" />
								</span>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$MODULE_LIST key=MODULE_ID item=MODULE_INFO name="modules"}
						{assign var="INDEX" value=$smarty.foreach.modules.iteration}
						<tr data-id="{$MODULE_ID}">
							<td>
								<strong>{\App\Language::translate($MODULE_INFO->getName(), $MODULE_INFO->getName())}</strong>
								<div class="d-flex align-items-center u-mt-2px float-right">
									{if $CRON_ACTIVE && $IS_PERMITTED}
										<span class="sentNotice d-flex u-cursor-pointer">
											<span title="{\App\Language::translate('LBL_SENT_NOTIFICATIONS', $MODULE)}"
												class="fas {if $SCHEDULE_DATA && in_array($MODULE_ID, $SCHEDULE_DATA.modules)}fa-bell sandNoticeOn{else}fa-bell-slash sandNoticeOff{/if} fa-lg cursorPointer"
												data-val=""></span></span>
									{/if}
									<span class="d-flex ml-1">
										<input type="checkbox" {if in_array($MODULE_ID, $WATCHING_MODULES)}checked {/if}
											name="modules"
											class="watchingModule" {if $WATCHING_MODEL->isLock($MODULE_ID)}disabled{/if}
											value="{$MODULE_ID}" />
									</span>
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</form>
	</div>
	<div class="modal-footer row">
		{if $CRON_ACTIVE && \App\Privilege::isPermitted($MODULE, 'ReceivingMailNotifications')}
			<div class="col-md-6 schedule d-flex flex-nowrap m-0">
				{assign var="POPOVER_CONTENT" value=\App\Language::translate('LBL_CRON_LAUNCHING_FREQUENCY', $MODULE)|cat:': '|cat:$CRON_INFO->getFrequency()/60|cat:\App\Language::translate('LBL_MINUTES')}
				<select class="select2 form-control" name="frequency"
					title="{\App\Language::translate('LBL_SCHEDULE', $MODULE)}">
					<option value="5" {if $FREQUENCY eq 5} selected{/if}>{\App\Language::translate('PLL_5_MIN',$MODULE)}</option>
					<OPTION VALUE="15" {if $FREQUENCY EQ '15'} selected{/if}>{\App\Language::translate('PLL_15_MIN',$MODULE)}</OPTION>
					<option value="30" {if $FREQUENCY eq '30'} selected{/if}>{\App\Language::translate('PLL_30_MIN',$MODULE)}</option>
					<option value="60" {if $FREQUENCY eq '60'} selected{/if}>{\App\Language::translate('PLL_60_MIN',$MODULE)}</option>
					<option value="180" {if $FREQUENCY eq '180'} selected{/if}>{\App\Language::translate('PLL_3_H',$MODULE)}</option>
					<option value="720" {if $FREQUENCY eq '720'} selected{/if}>{\App\Language::translate('PLL_12_H',$MODULE)}</option>
					<option value="1440" {if $FREQUENCY eq '1440'} selected{/if}>{\App\Language::translate('PLL_24_H',$MODULE)}</option>
				</select>
				<a href="#" class="infoPopover align-self-center ml-1" title="" data-placement="top"
					data-original-title="{\App\Language::translate('LBL_RECEIVING_MAIL_NOTIFICATIONS', $MODULE)}"
					data-content="{\App\Purifier::encodeHtml($POPOVER_CONTENT)}">
					<span class="fas fa-info-circle"></span>
				</a>
			</div>
		{/if}
		<div class="col-md-6 d-flex justify-content-end mt-1 m-sm-0">
			<button type="button" name="saveButton" class="btn btn-success mr-1">
				<span class="fas fa-check mr-1"></span>
				{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
			</button>
			<button type="button" class="btn btn-danger" data-dismiss="modal">
				<span class="fas fa-times mr-1"></span>
				{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}
			</button>
		</div>
	</div>
	<!-- /tpl-Notification-NotificationConfig -->
{/strip}
