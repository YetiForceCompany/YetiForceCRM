{*{[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]}*}
{strip}
	<div>
		<div class="widget_header row">
			<div class="col-md-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				&nbsp;{vtranslate('LBL_BRUTEFORCE_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
			<li class="active"><a href="#settings" data-toggle="tab">{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</a></li>
			<li><a href="#blocedIds" data-toggle="tab">{vtranslate('LBL_BLOCKED_IP', $QUALIFIED_MODULE)}</a></li>
		</ul>
		<div id="my-tab-content" class="tab-content padding10" >
			<div class="tab-pane active row" id="settings">
				<div class="col-sm-10 col-md-8">
					<div class="panel panel-default">
						<div class="panel-body">
							<form id="brutalForceTabForm1" class="form-horizontal" name="brutalForceTabForm1" data-mode="saveConfig">
								<div class="form-group">
									<label class="col-sm-3 control-label">{vtranslate('LBL_BRUTEFORCE_ACTIVE', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-8 col-md-9">
										<input type="checkbox" id="active" name="active" class="switchBtn" title="{vtranslate('LBL_BRUTEFORCE_ACTIVE', $QUALIFIED_MODULE)}" {if $CONFIG.active} checked {/if} data-size="small" data-label-width="5" data-on-text="{vtranslate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_NO', $QUALIFIED_MODULE)}"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{vtranslate('LBL_NUMBER_OF_ATTEMPTS', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-2">
										<input type="text" class="form-control" name="attempsnumber" title="{vtranslate('LBL_NUMBER_OF_ATTEMPTS', $QUALIFIED_MODULE)}" id="attempsNumber" value="{$CONFIG.attempsnumber}" data-validation-engine="validate[required,custom[number],min[2],max[100]]">
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{vtranslate('LBL_TIME_LOCK', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-2">
										<input type="text" class="form-control" name="timelock" id="timeLock" title="{vtranslate('LBL_TIME_LOCK', $QUALIFIED_MODULE)}" value="{$CONFIG.timelock}" data-validation-engine="validate[required,custom[integer]]">
									</div>
								</div>
								<div class="form-group marginbottomZero">
									<label class="col-sm-3 control-label">{vtranslate('LBL_SENT_NOTIFICATIONS', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-9">
										<input type="checkbox" id="sent" name="sent" class="switchBtn" {if $CONFIG.sent} checked {/if} title="{vtranslate('LBL_SENT_NOTIFICATIONS', $QUALIFIED_MODULE)}" data-size="small" data-label-width="5" data-on-text="{vtranslate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_NO', $QUALIFIED_MODULE)}"/>
										<div class="selectedUsersForm{if !$CONFIG.sent} hide{/if}">
											<label class="control-label">{vtranslate('LBL_USERS_FOR_NOTIFICATIONS', $QUALIFIED_MODULE)}</label>
											<select class="chzn-select form-control" name="selectedUsers" multiple id="selectedUsers" title="{vtranslate('LBL_USERS_FOR_NOTIFICATIONS', $QUALIFIED_MODULE)}" >
												{foreach key=KEY item=USER from=$ADMIN_USERS}
													<option value="{$KEY}" {if $USERS_FOR_NOTIFICATIONS } {if in_array($KEY, $USERS_FOR_NOTIFICATIONS)} selected {/if}{/if}>{$USER}</option>
												{/foreach}
											</select>
										</div>
									</div>
								</div>
						</div>
						<div class="panel-footer clearfix">
							<div class="pull-left">
								<button class="btn btn-success saveButton" type="submit" id="saveConfig" title="{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button></div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="blocedIds">
				<form id="brutalforceTabForm2" name="brutalforceTabForm2">
					<div class="table-responsive">
						<table  class="table tableRWD table-bordered table-condensed themeTableColor">
							<thead>
								<tr class="blockHeader">
									<th><span class="alignMiddle">{vtranslate('LBL_IP', $QUALIFIED_MODULE)}</span></th>
									<th><span class="alignMiddle">{vtranslate('LBL_DATE', $QUALIFIED_MODULE)}</span></th>
									<th><span class="alignMiddle">{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}</span></th>
									<th><span class="alignMiddle">{vtranslate('LBL_NUMBER_OF_ATTEMPTS', $QUALIFIED_MODULE)}</span></th>
									<th><span class="alignMiddle">{vtranslate('LBL_BROWSERS', $QUALIFIED_MODULE)}</span></th>
									<th><span class="alignMiddle">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</span></th>
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
											<button data-id="{$ITEM['id']}" class="btn btn-success unblock" type="button" title="{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}">
												<strong>{vtranslate('LBL_UNBLOCK', $QUALIFIED_MODULE)}</strong>
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
