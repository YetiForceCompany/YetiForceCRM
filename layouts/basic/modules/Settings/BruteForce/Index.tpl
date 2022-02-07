{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-BruteForce-Index">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-12 mb-2">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
			<li class="nav-item">
				<a class="nav-link active" href="#settings" data-toggle="tab">{\App\Language::translate('LBL_SETTINGS', $QUALIFIED_MODULE)}</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#blocedIds" data-toggle="tab">{\App\Language::translate('LBL_BLOCKED_IP', $QUALIFIED_MODULE)}</a>
			</li>
		</ul>
		<div id="my-tab-content" class="tab-content">
			<div class="tab-pane active row" id="settings">
				<div class="col-sm-10 col-md-8">
					<div class="card">
						<div class="card-body">
							<form id="brutalForceTabForm1" class="form-horizontal" name="brutalForceTabForm1"
								data-mode="saveConfig">
								<div class="form-group row align-items-center">
									<label class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_BRUTEFORCE_ACTIVE', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-8 col-md-9">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-outline-primary {if $CONFIG.active} active{/if}">
												<input class="js-switch" type="radio" name="active" data-js="change" id="active1" autocomplete="off" {' '}
													value="1" {if $CONFIG.active}checked{/if}> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
											</label>
											<label class="btn btn-outline-primary {if !$CONFIG.active} active{/if}">
												<input class="js-switch" type="radio" name="active" data-js="change" id="active2" autocomplete="off" value="0" {if !$CONFIG.active}checked{/if}> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
											</label>
										</div>
									</div>
								</div>
								<div class="form-group row align-items-center">
									<label class="col-sm-3 col-form-label text-right">
										{\App\Language::translate('LBL_NUMBER_OF_ATTEMPTS', $QUALIFIED_MODULE)}
										<div class="js-popover-tooltip d-inline ml-1" data-js="popover" data-content="{\App\Language::translate('LBL_NUMBER_OF_ATTEMPTS_DESC', $QUALIFIED_MODULE)}">
											<span class="fas fa-info-circle"></span>
										</div>
									</label>
									<div class="col-sm-2">
										<input type="text" class="form-control" name="attempsnumber" title="{\App\Language::translate('LBL_NUMBER_OF_ATTEMPTS', $QUALIFIED_MODULE)}" {' '} id="attempsNumber" value="{$CONFIG.attempsnumber}" data-validation-engine="validate[required,custom[number],min[2],max[100]]">
									</div>
								</div>
								<div class="form-group row align-items-center">
									<label class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_TIME_LOCK', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-2">
										<input type="text" class="form-control" name="timelock" id="timeLock" title="{\App\Language::translate('LBL_TIME_LOCK', $QUALIFIED_MODULE)}" {' '}
											value="{$CONFIG.timelock}" data-validation-engine="validate[required,custom[integer]]">
									</div>
								</div>
								<div class="form-group row align-items-center mb-0">
									<label class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_SENT_NOTIFICATIONS', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-9">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-outline-primary {if $CONFIG.sent} active{/if}">
												<input class="js-switch--sent" type="radio" name="sent" data-js="change" id="sent1" autocomplete="off" value="1" {if $CONFIG.sent}checked{/if}> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
											</label>
											<label class="btn btn-outline-primary {if !$CONFIG.sent} active{/if}">
												<input class="js-switch--sent" type="radio" name="sent" data-js="change" id="sent2" autocomplete="off" value="0" {if !$CONFIG.sent}checked{/if}> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
											</label>
										</div>
										<div class="selectedUsersForm{if !$CONFIG.sent} d-none{/if}">
											<label class="col-form-label">{\App\Language::translate('LBL_USERS_FOR_NOTIFICATIONS', $QUALIFIED_MODULE)}</label>
											<select class="select2 form-control" name="selectedUsers" multiple id="selectedUsers" title="{\App\Language::translate('LBL_USERS_FOR_NOTIFICATIONS', $QUALIFIED_MODULE)}">
												{foreach key=KEY item=USER from=$ADMIN_USERS}
													<option value="{$KEY}" {if $USERS_FOR_NOTIFICATIONS } {if in_array($KEY, $USERS_FOR_NOTIFICATIONS)} selected {/if}{/if}>{$USER}</option>
												{/foreach}
											</select>
										</div>
									</div>
								</div>
						</div>
						<div class="card-footer clearfix">
							<div class="float-left">
								<button class="btn btn-success saveButton" type="submit" id="saveConfig" title="{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}">
									<span class="fa fa-check u-mr-5px"></span>
									<strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
							</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="blocedIds">
				<form id="brutalforceTabForm2" name="brutalforceTabForm2">
					<div class="table-responsive">
						<table class="table tableRWD table-bordered table-sm themeTableColor">
							<thead>
								<tr class="blockHeader">
									<th>
										<span class="alignMiddle">{\App\Language::translate('LBL_IP', $QUALIFIED_MODULE)}</span>
									</th>
									<th>
										<span class="alignMiddle">{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</span>
									</th>
									<th>
										<span class="alignMiddle">{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}</span>
									</th>
									<th>
										<span class="alignMiddle">{\App\Language::translate('LBL_NUMBER_OF_ATTEMPTS', $QUALIFIED_MODULE)}</span>
									</th>
									<th>
										<span class="alignMiddle">{\App\Language::translate('LBL_BROWSERS', $QUALIFIED_MODULE)}</span>
									</th>
									<th>
										<span class="alignMiddle">{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</span>
									</th>
								</tr>
							</thead>
							<tbody>
								{foreach from=$BLOCKED item=ITEM}
									{assign var=ITEM value=$MODULE_MODEL->getLoginHistoryData($ITEM)}
									<tr>
										<td><label>{$ITEM['ip']}</label></td>
										<td><label>{$ITEM['time']}</label></td>
										<td><label>{$ITEM['usersName']}</label></td>
										<td><label>{$ITEM['attempts']}</label></td>
										<td><label>{$ITEM['browsers']}</label></td>
										<td class="text-center">
											<button data-id="{$ITEM['id']}" class="btn btn-success unblock" type="button" title="{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}">
												<strong>{\App\Language::translate('LBL_UNBLOCK', $QUALIFIED_MODULE)}</strong>
											</button>
										</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
