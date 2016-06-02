{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div  class="modal fade modalNotificationConfig">
		<div class="modal-dialog modal-full">
			<div class="modal-content">
				<div class="modal-header row no-margin">
					<div class="col-xs-12 paddingLRZero">
						<div class="col-xs-8 paddingLRZero">
							<h4>{vtranslate('LBL_WATCHING_MODULES', $MODULE)}</h4>
						</div>
						<div class="pull-right">
							<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
						</div>
					</div>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-xs-12">
							
							{foreach from=$MODULE_LIST key=MODULE_ID item=MODULE_INFO}
								<div class="col-md-3 col-sm-3 col-xs-4">
									<div class="checkbox">
										<label>
											<input type="checkbox" {if in_array($MODULE_ID, $WATCHING_MODULES)}checked {/if} class="watchingModule" data-name-Module="{$MODULE_INFO['name']}"> {vtranslate($MODULE_INFO['name'], $MODULE_INFO['name'])}
										</label>
									</div>
								</div>
							{/foreach}
						</div>
					</div>
					{assign var="CRON_ACTIVE" value=$CRON_INFO->getStatus()}
					{if $CRON_ACTIVE && Users_Privileges_Model::isPermitted('Dashboard', 'ReceivingMailNotifications')}
						<hr>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-6 col-sm-6">
									<label class="pull-left control-label form-control-static">{vtranslate('LBL_RECEIVING_MAIL_NOTIFICATIONS', $MODULE)}: </label>
									<div class="col-md-3 col-sm-3 col-xs-12 controls">
										<input name="sendNotifications" class="switchBtn sendNotificationsSwitch" type="checkbox" {if $FREQUENCY}checked{/if} data-size="small" data-label-width="5" data-on-text="{vtranslate('LBL_YES', $MODULE)}" data-off-text="{vtranslate('LBL_NO', $MODULE)}" value="1">
									</div>
								</div>
								<div class="col-md-6 col-sm-6 schedule{if !$FREQUENCY} hide{/if}">
									{assign var="POPOVER_CONTENT" value=vtranslate('LBL_CRON_LAUNCHING_FREQUENCY', $MODULE)|cat:': '|cat:$CRON_INFO->getFrequency()/60|cat:vtranslate('LBL_MINUTES')}
									<label class="pull-left control-label form-control-static">{vtranslate('LBL_SCHEDULE', $MODULE)}&nbsp;
										<a href="#" class="infoPopover" title="" data-placement="top" data-original-title="{vtranslate('LBL_CRON', $MODULE)}" data-content="{Vtiger_Util_Helper::toSafeHTML($POPOVER_CONTENT)}"><i class="glyphicon glyphicon-info-sign"></i></a>: 
									</label>
									<div class="col-md-6 col-sm-7 col-xs-12 controls">
										<select class="select2" name="frequency" title="{vtranslate('LBL_SCHEDULE', $MODULE)}">
											<option value="5" {if $FREQUENCY eq 5} selected{/if}>{vtranslate('PLL_5_MIN',$MODULE)}</option>
											<OPTION VALUE="15" {if $FREQUENCY EQ '15'} SELECTED{/if}>{VTRANSLATE('PLL_15_MIN',$MODULE)}</OPTION>
											<option value="30" {if $FREQUENCY eq '30'} selected{/if}>{vtranslate('PLL_30_MIN',$MODULE)}</option>
											<option value="60" {if $FREQUENCY eq '60'} selected{/if}>{vtranslate('PLL_60_MIN',$MODULE)}</option>
											<option value="180" {if $FREQUENCY eq '180'} selected{/if}>{vtranslate('PLL_3_H',$MODULE)}</option>
											<option value="720" {if $FREQUENCY eq '720'} selected{/if}>{vtranslate('PLL_12_H',$MODULE)}</option>
											<option value="1440" {if $FREQUENCY eq '1440'} selected{/if}>{vtranslate('PLL_24_H',$MODULE)}</option>
										</select>
									</div>
								</div>
								<div class="col-md-6 col-sm-6">
									<div class="checkbox">
										<label>
											<input type="checkbox" {if $SELECT_ALL_MODULES} checked {/if} class="selectAllModules"> {vtranslate('LBL_SELECT_ALL')}
										</label>
									</div>
								</div>
							</div>
						</div>
					{/if}
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
	</div>

{/strip}
