{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-InterestsConflict-Index -->
	<div>
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Config'}active{/if}" href="#Config" data-toggle="tab" data-name="Config">
						<span class="fas fa-sliders-h mr-2"></span>{\App\Language::translate('LBL_MAIN_CONFIG', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Modules'}active{/if}" href="#Modules" data-toggle="tab" data-name="Modules">
						<span class="fas fa-boxes mr-2"></span>{\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Confirmations'}active{/if}" href="#Confirmations" data-toggle="tab" data-name="Confirmations">
						<span class="fas fa-history mr-2"></span>{\App\Language::translate('LBL_CONFIRMATIONS', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Unlock'}active{/if}" href="#Unlock" data-toggle="tab" data-name="Unlock">
						<span class="fas fa-unlock mr-2"></span>{\App\Language::translate('LBL_UNLOCK_REQUESTS', $QUALIFIED_MODULE)}
					</a>
				</li>
			</ul>
		</div>
		<div id="my-tab-content" class="tab-content">
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'Config'}active{/if}" id="Config" data-name="Config" data-js="data">
				<form class="js-filter-form">
					<div class="js-config-table table-responsive" data-js="container">
						<table class="table table-bordered table-sm">
							<thead>
								<tr class="blockHeader">
									<th colspan="2" class="mediumWidthType">
										<span class="fas fa-sliders-h mr-2"></span>
										{\App\Language::translate('LBL_MAIN_CONFIG', $QUALIFIED_MODULE)}
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="u-w-37per px-2">
										<label class="muted float-right col-form-label u-text-small-bold">
											{\App\Language::translate('LBL_IS_ACTIVE', $QUALIFIED_MODULE)}
										</label>
									</td>
									<td class="border-left-0 px-3 align-middle">
										<input name="isActive" type="checkbox" class="form-control" data-js="is" {if !empty($CONFIG_DATA['isActive'])}checked{/if} value="true">
									</td>
								</tr>
								<tr>
									<td class="u-w-37per px-2">
										<label class="muted float-right col-form-label u-text-small-bold">
											{\App\Language::translate('LBL_INTERVAL_TIME', $QUALIFIED_MODULE)}
											<span class="fas fa-info-circle text-primary js-popover-tooltip ml-2" data-content="{\App\Language::translate('LBL_INTERVAL_TIME_DESC', $QUALIFIED_MODULE)}"></span>
										</label>
									</td>
									<td class="border-left-0 px-3">
										<div class="input-group">
											<input name="confirmationTimeInterval" type="number" class="form-control" value="{$CONFIG_DATA['confirmationTimeInterval']}" {if $CONFIG_DATA['confirmationTimeIntervalList'] === '-'}disabled="disabled" {/if} data-validation-engine="validate[required, custom[number],min[1]]" />
											<div class="input-group-append col-md-4 p-0">
												<select class="form-control select2" name="confirmationTimeIntervalList">
													<option value="-" {if $CONFIG_DATA['confirmationTimeIntervalList'] === '-'}selected{/if}>
														{\App\Language::translate('LBL_INDEFINITELY',$QUALIFIED_MODULE)}
													</option>
													<option value="days" {if $CONFIG_DATA['confirmationTimeIntervalList'] === 'days'}selected{/if}>
														{\App\Language::translate('LBL_DAYS')}
													</option>
													<option value="month" {if $CONFIG_DATA['confirmationTimeIntervalList'] === 'month'}selected{/if}>
														{\App\Language::translate('LBL_MONTHS')}
													</option>
													<option value="years" {if $CONFIG_DATA['confirmationTimeIntervalList'] === 'years'}selected{/if}>
														{\App\Language::translate('LBL_YEARS')}
													</option>
												</select>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td class="u-w-37per px-2">
										<label class="muted float-right col-form-label u-text-small-bold" id="confirmUsersAccess">
											{\App\Language::translate('LBL_USERS_CONFIRMATIONS', $QUALIFIED_MODULE)}
										</label>
									</td>
									<td class="border-left-0 px-3">
										<select id="confirmUsersAccessList" class="form-control select2" multiple="true" name="confirmUsersAccess[]" aria-describedby="confirmUsersAccess">
											{foreach from=$USERS key=USER_ID item=USER}
												<option value="{$USER_ID}" {if in_array($USER_ID,$CONFIG_DATA['confirmUsersAccess'])}selected{/if}>
													{$USER->getDisplayName()} ({$USER->getRoleDetail()->get('rolename')})
												</option>
											{/foreach}
										</select>
									</td>
								</tr>
								<tr>
									<td class="u-w-37per px-2">
										<label class="muted float-right col-form-label u-text-small-bold" id="unlockUsersAccess">
											{\App\Language::translate('LBL_USERS_UNLOCK', $QUALIFIED_MODULE)}
										</label>
									</td>
									<td class="border-left-0 px-3">
										<select id="unlockUsersAccessList" class="form-control select2" multiple="true" name="unlockUsersAccess[]" aria-describedby="unlockUsersAccess">
											{foreach from=$USERS key=USER_ID item=USER}
												<option value="{$USER_ID}" {if in_array($USER_ID,$CONFIG_DATA['unlockUsersAccess'])}selected{/if}>
													{$USER->getDisplayName()} ({$USER->getRoleDetail()->get('rolename')})
												</option>
											{/foreach}
										</select>
									</td>
								</tr>
								<tr>
									<td class="u-w-37per px-2">
										<label class="muted float-right col-form-label u-text-small-bold">
											{\App\Language::translate('LBL_NOTIFICATIONS_EMAILS', $QUALIFIED_MODULE)}
										</label>
									</td>
									<td class="border-left-0 px-3">
										<input name="notificationsEmails" type="text" class="form-control" value="{$CONFIG_DATA['notificationsEmails']}" />
									</td>
								</tr>
								<tr>
									<td class="u-w-37per px-2">
										<label class="muted float-right col-form-label u-text-small-bold">
											{\App\Language::translate('LBL_SEND_MAIL_DURING_ACCESS_REQUEST', $QUALIFIED_MODULE)}
											<span class="fas fa-info-circle text-primary js-popover-tooltip ml-2" data-content="{\App\Language::translate('LBL_SEND_MAIL_DURING_ACCESS_REQUEST_INFO', $QUALIFIED_MODULE)}"></span>
										</label>
									</td>
									<td class="border-left-0 px-3 align-middle">
										<input name="sendMailAccessRequest" type="checkbox" class="form-control" data-js="is" {if !empty($CONFIG_DATA['sendMailAccessRequest'])}checked{/if} value="true">
									</td>
								</tr>
								<tr>
									<td class="u-w-37per px-2">
										<label class="muted float-right col-form-label u-text-small-bold">
											{\App\Language::translate('LBL_SEND_MAIL_DURING_ACCESS_RESPONSE', $QUALIFIED_MODULE)}
											<span class="fas fa-info-circle text-primary js-popover-tooltip ml-2" data-content="{\App\Language::translate('LBL_SEND_MAIL_DURING_ACCESS_RESPONSE_INFO', $QUALIFIED_MODULE)}"></span>
										</label>
									</td>
									<td class="border-left-0 px-3 align-middle">
										<input name="sendMailAccessResponse" type="checkbox" class="form-control" data-js="is" {if !empty($CONFIG_DATA['sendMailAccessResponse'])}checked{/if} value="true">
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<button class="btn btn-success float-right js-save" type="button">
						<span class="fas fa-check mr-2"></span>
						{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
					</button>
				</form>
			</div>
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'Modules'}active{/if}" id="Modules" data-name="Modules" data-js="data">
				<form class="js-filter-form">
					<table class="table table-bordered table-sm u-fs-sm js-confirm-table">
						<thead>
							<tr class="blockHeader">
								<th class="text-center"><strong>{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_MODULE_NAME',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_MODULES',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_RELATIONSHIP',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=\App\Components\InterestsConflict::getModules() key=KEY item=ROW}
								<tr>
									<td class="text-center">
										<input class="js-change align-middle" type="checkbox" name="{$ROW['key']}" {if isset($CONFIG_DATA['modules'][$ROW['key']])}checked="" {/if} value="{\App\Purifier::encodeHtml($ROW['value'])}" data-js="change">
									</td>
									<td>
										<span class="yfm-{$ROW['target']} mx-2"></span>
										{\App\Language::translate($ROW['target'],$ROW['target'])}
									</td>
									<td>
										<span class="yfm-{$ROW['base']} mx-2"></span>
										{\App\Language::translate($ROW['base'],$ROW['base'])}
									</td>
									<td class="pl-2">
										{$ROW['map']}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</form>
			</div>
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'Confirmations'}active{/if}" id="Confirmations" data-name="Confirmations" data-js="data">
				{include file=\App\Layout::getTemplatePath('InterestsConflictConfirmations.tpl', 'AppComponents')}
			</div>
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'Unlock'}active{/if}" id="Unlock" data-name="Unlock" data-js="data">
				{include file=\App\Layout::getTemplatePath('InterestsConflictUnlock.tpl', 'AppComponents')}
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-InterestsConflict-Index -->
{/strip}
