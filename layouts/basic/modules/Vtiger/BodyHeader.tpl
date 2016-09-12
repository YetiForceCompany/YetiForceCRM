{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
    {assign var='count' value=0}
	<div class="container-fluid bodyHeader noSpaces commonActionsContainer{if $LEFTPANELHIDE} menuOpen{/if}">
		<div class="row noSpaces">
			<div class="rightHeader paddingRight10">
				<div class="pull-right rightHeaderBtn">
					<div class="dropdown quickAction historyBtn">
						<a data-placement="left" data-toggle="dropdown" class="btn btn-default btn-sm showHistoryBtn" aria-expanded="false" href="#">
							<img class='dropdown-toggle alignMiddle popoverTooltip' src="{vimage_path('history.png')}" alt="{vtranslate('LBL_PAGES_HISTORY',$MODULE)}" data-content="{vtranslate('LBL_PAGES_HISTORY')}" />
						</a>
					</div>
				</div>
				{if $REMINDER_ACTIVE}
					<div class="pull-right rightHeaderBtn">
						<div class="remindersNotice quickAction{if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')} autoRefreshing{/if}">
							<a class="btn btn-default btn-sm isBadge" title="{vtranslate('LBL_REMINDER',$MODULE)}" href="#">
								<span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
								<span class="badge hide">0</span>
							</a>
						</div>
					</div>
				{/if}
				{if $CHAT_ACTIVE}
					<div class="pull-right rightHeaderBtn">
						<div class="headerLinksAJAXChat quickAction">
							<a class="btn btn-default btn-sm ChatIcon" title="{vtranslate('LBL_CHAT',$MODULE)}" href="#">
								<span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
							</a>
						</div>
					</div>
				{/if}
				{if Users_Privileges_Model::isPermitted('Dashboard', 'NotificationPreview')}
					<div class="pull-right rightHeaderBtn">
						<div class="notificationsNotice quickAction{if AppConfig::module('Home', 'AUTO_REFRESH_REMINDERS')} autoRefreshing{/if}">
							<div class="btn-group">
								<a class="btn btn-default btn-sm isBadge" title="{vtranslate('LBL_NOTIFICATIONS',$MODULE)}" href="index.php?module=Home&view=NotificationsList">
									<span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
									<span class="badge hide">0</span>
								</a>
							</div>
						</div>
					</div>
				{/if}
				{assign var=QUICKCREATE_MODULES value=Vtiger_Module_Model::getQuickCreateModules(true)}
				{if !empty($QUICKCREATE_MODULES)}
					<div class="pull-right rightHeaderBtn">
						<div class="dropdown quickAction">
							<a id="menubar_quickCreate" class="dropdown-toggle btn btn-default btn-sm" data-toggle="dropdown" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" href="#">
								<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
							</a>
							<ul class="dropdown-menu dropdown-menu-right commonActionsButtonDropDown">
								<li class="quickCreateModules">
									<div class="panel-default">
										<div class="panel-heading">
											<h4 class="panel-title"><strong>{vtranslate('LBL_QUICK_CREATE',$MODULE)}</strong></h4>
										</div>
										<div class="panel-body paddingLRZero">
											{foreach key=NAME item=MODULEMODEL from=$QUICKCREATE_MODULES}
												{assign var='quickCreateModule' value=$MODULEMODEL->isQuickCreateSupported()}
												{assign var='singularLabel' value=$MODULEMODEL->getSingularLabelKey()}
												{if $singularLabel == 'SINGLE_Calendar'}
													{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
												{/if}	
												{if $quickCreateModule == '1'}
													{if $count % 3 == 0}
														<div class="">
														{/if}
														<div class="col-xs-4{if $count % 3 != 2} paddingRightZero{/if}">
															<a id="menubar_quickCreate_{$NAME}" class="quickCreateModule list-group-item" data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)" title="{vtranslate($singularLabel,$NAME)}">
																<span>{vtranslate($singularLabel,$NAME)}</span>
															</a>
														</div>
														{if $count % 3 == 2}
														</div>
													{/if}
													{assign var='count' value=$count+1}
												{/if}
											{/foreach}
											{if $count % 3 >= 1}
											</div>
										{/if}
									</div>
									</div>
								</li>
							</ul>
						</div>
					</div>
				{/if}
				<div class="pull-left selectSearch">
					<div class="input-group globalSearchInput">
						<span class="input-group-btn">

							<select class="chzn-select basicSearchModulesList form-control col-md-5" title="{vtranslate('LBL_SEARCH_MODULE', $MODULE_NAME)}">
								<option value="">{vtranslate('LBL_ALL_RECORDS', $MODULE_NAME)}</option>
								{foreach key=SEARCHABLE_MODULE item=fieldObject from=$SEARCHABLE_MODULES}
									{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $SEARCHABLE_MODULE && $SEARCHED_MODULE !== 'All'}
										<option value="{$SEARCHABLE_MODULE}" selected>{vtranslate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
									{else}
										<option value="{$SEARCHABLE_MODULE}">{vtranslate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
									{/if}
								{/foreach}
							</select>
						</span>
						<input type="text" class="form-control globalSearchValue" title="{vtranslate('LBL_GLOBAL_SEARCH')}" placeholder="{vtranslate('LBL_GLOBAL_SEARCH')}" results="10" />
						<span class="input-group-btn">
							<button class="btn btn-default searchIcon" type="button">
								<span class="glyphicon glyphicon-search"></span>
							</button>
							<button class="btn btn-default globalSearch" title="{vtranslate('LBL_ADVANCE_SEARCH')}" type="button">
								<span class="glyphicon glyphicon-th-large"></span>
							</button>
						</span>
					</div>
				</div>	
				<div class="pull-right rightHeaderBtnMenu">
					<div class="quickAction">
						<a class="btn btn-default btn-sm" href="#">
							<span aria-hidden="true" class="glyphicon glyphicon-menu-hamburger"></span>
						</a>
					</div>
				</div>
				<div class="pull-right actionMenuBtn">
					<div class="quickAction">
						<a class="btn btn-default btn-sm" href="#">
							<span aria-hidden="true" class="glyphicon glyphicon-tasks"></span>
						</a>
					</div>
				</div>
				<div class="pull-left searchMenuBtn">
					<div class="quickAction">
						<a class="btn btn-default btn-sm" href="#">
							<span aria-hidden="true" class="glyphicon glyphicon-search"></span>
						</a>
					</div>
				</div>
				{if !Settings_ModuleManager_Library_Model::checkLibrary('roundcube')}
					<div class="pull-right">
						{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('mailIcon')}
						{if $CONFIG['showMailIcon']=='true'}
							{assign var=AUTOLOGINUSERS value=OSSMail_Autologin_Model::getAutologinUsers()}
							{if count($AUTOLOGINUSERS) > 0}
								{assign var=MAIN_MAIL value=OSSMail_Module_Model::getDefaultMailAccount($AUTOLOGINUSERS)}
								<div class="headerLinksMails" id="OSSMailBoxInfo" {if $CONFIG['showNumberUnreadEmails']=='true'}data-numberunreademails="true" data-interval="{$CONFIG['timeCheckingMail']}"{/if}>
									<div class="btn-group">
										{if count($AUTOLOGINUSERS) eq 1}
											<a type="button" class="btn btn-sm btn-default" title="{$MAIN_MAIL.username}" href="index.php?module=OSSMail&view=index">
												<div class="hidden-xs">
													{$ITEM.username}
													<span class="mail_user_name">{$MAIN_MAIL.username}</span>
													<span data-id="{$MAIN_MAIL.rcuser_id}" class="noMails"></span>
												</div>
												<div class="visible-xs-block">
													<span class="glyphicon glyphicon-list-alt"></span>
												</div>
											</a>
										{elseif $CONFIG['showMailAccounts']=='true'}
											<select class="form-control" title="{vtranslate('LBL_SEARCH_MODULE', $MODULE_NAME)}">
												{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
													<option value="{$KEY}" {if $ITEM.active}selected{/if} data-id="{$KEY}" data-nomail="" class="noMails">
														{$ITEM.username}
													</option>
												{/foreach}
											</select>
										{/if}
									</div>
								</div>
							{/if}
						{/if}
					</div>
				{/if}
			</div>
		</div>
	</div>
{/strip}
