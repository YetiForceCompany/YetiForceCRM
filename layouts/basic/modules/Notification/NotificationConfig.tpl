{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="CRON_ACTIVE" value=$CRON_INFO->getStatus()}
	{assign var="IS_PERMITTED" value=\App\Privilege::isPermitted($MODULE, 'ReceivingMailNotifications')}
	<div class="modal-header row no-margin">
		<div class="col-12 paddingLRZero">
			<div class="col-8 paddingLRZero">
				<h4>{\App\Language::translate('LBL_WATCHING_MODULES', $MODULE)}</h4>
			</div>
			<div class="float-right">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
			</div>
		</div>
	</div>
	<div class="modal-body paddingBottomZero">
		<form id="sortingCustomView">
			<div class="row">
				<div class="table-responsive padding10">
					<div class="col-12">
						<table class="table table-bordered table-sm modalDataTable">
							<thead>
								<tr>
									<th>
										<strong>{\App\Language::translate('LBL_MODULES', $MODULE)}</strong>
										<div class="float-right">
											{if $CRON_ACTIVE && $IS_PERMITTED}
												<span title="{\App\Language::translate('LBL_SELECT_ALL')}" class="fa {if $IS_ALL_EMAIL_NOTICE}fa-envelope sandNoticeOn{else}fa-envelope-o sandNoticeOff{/if} fa-lg marginTB3 cursorPointer sentNotice"></span>
											{/if}
											<span class="float-right marginIcon">
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
										<td><strong>{\App\Language::translate($MODULE_INFO->getName(), $MODULE_INFO->getName())}</strong>
											<span class="float-right marginIcon">
												<input type="checkbox" {if in_array($MODULE_ID, $WATCHING_MODULES)}checked {/if} name="modules" class="watchingModule" {if $WATCHING_MODEL->isLock($MODULE_ID)}disabled{/if} value="{$MODULE_ID}" />
											</span>
											{if $CRON_ACTIVE && $IS_PERMITTED}
												<span title="{\App\Language::translate('LBL_SENT_NOTIFICATIONS', $MODULE)}" class="fa {if in_array($MODULE_ID, $SCHEDULE_DATA.modules)}fa-envelope sandNoticeOn{else}fa-envelope-o sandNoticeOff{/if} fa-lg float-right marginTB3 cursorPointer" data-val=""></span>
											{/if}
										</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="modal-footer">
		{if $CRON_ACTIVE && \App\Privilege::isPermitted($MODULE, 'ReceivingMailNotifications')}
			<div class="col-md-3 col-sm-4 schedule float-left paddingRightZero">
				{assign var="POPOVER_CONTENT" value=\App\Language::translate('LBL_CRON_LAUNCHING_FREQUENCY', $MODULE)|cat:': '|cat:$CRON_INFO->getFrequency()/60|cat:\App\Language::translate('LBL_MINUTES')}
				<select class="select2 form-control" name="frequency" title="{\App\Language::translate('LBL_SCHEDULE', $MODULE)}">
					<option value="5" {if $FREQUENCY eq 5} selected{/if}>{\App\Language::translate('PLL_5_MIN',$MODULE)}</option>
					<OPTION VALUE="15" {if $FREQUENCY EQ '15'} selected{/if}>{\App\Language::translate('PLL_15_MIN',$MODULE)}</OPTION>
					<option value="30" {if $FREQUENCY eq '30'} selected{/if}>{\App\Language::translate('PLL_30_MIN',$MODULE)}</option>
					<option value="60" {if $FREQUENCY eq '60'} selected{/if}>{\App\Language::translate('PLL_60_MIN',$MODULE)}</option>
					<option value="180" {if $FREQUENCY eq '180'} selected{/if}>{\App\Language::translate('PLL_3_H',$MODULE)}</option>
					<option value="720" {if $FREQUENCY eq '720'} selected{/if}>{\App\Language::translate('PLL_12_H',$MODULE)}</option>
					<option value="1440" {if $FREQUENCY eq '1440'} selected{/if}>{\App\Language::translate('PLL_24_H',$MODULE)}</option>
				</select>
			</div>
			<div class="float-left col-1 paddingLRZero">
				<a href="#" class="infoPopover float-left" title="" data-placement="top" data-original-title="{\App\Language::translate('LBL_RECEIVING_MAIL_NOTIFICATIONS', $MODULE)}" data-content="{\App\Purifier::encodeHtml($POPOVER_CONTENT)}">&nbsp;<span class="fas fa-info-circle"></span></a>
			</div>
		{/if}
		<div class="col-md-6 col-sm-6 float-right">
			<button type="button" name="saveButton" class="btn btn-success">{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}</button>
			<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</button>
		</div>
	</div>
{/strip}
