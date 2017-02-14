{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="actionMenu" aria-hidden="true">
		<div class="row">
			<div class="dropdown quickAction historyBtn">
				<div class="pull-left">
					{\App\Language::translate('LBL_PAGES_HISTORY')}
				</div>						
				<div class="pull-right">
					<a data-placement="left" data-toggle="dropdown" class="btn btn-default btn-sm showHistoryBtn" aria-expanded="false" href="#">
						<img class='alignMiddle popoverTooltip dropdown-toggle' src="{vimage_path('history.png')}" alt="{\App\Language::translate('LBL_PAGES_HISTORY')}" data-content="{vtranslate('LBL_PAGES_HISTORY')}" />
					</a>
				</div>
			</div>
		</div>
		{if $REMINDER_ACTIVE}
			<div class="row">
				<div class="remindersNotice quickAction{if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')} autoRefreshing{/if}">
					<div class="pull-left">
						{\App\Language::translate('LBL_REMINDER')}
					</div>	
					<div class="pull-right">
						<a class="btn btn-default" title="{\App\Language::translate('LBL_REMINDER')}" href="#">
							<span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
							<span class="badge hide bgDanger">0</span>
						</a>
					</div>
				</div>
			</div>
		{/if}
		{if $CHAT_ACTIVE}
			<div class="row">
				<div class="headerLinksAJAXChat quickAction">
					<div class="pull-left">
						{\App\Language::translate('LBL_CHAT')}
					</div>
					<div class="pull-right">
						<a class="btn btn-default ChatIcon" title="{\App\Language::translate('LBL_CHAT')}" href="#">
							<span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
						</a>
					</div>
				</div>
			</div>
		{/if}
			{if Users_Privileges_Model::isPermitted('Notification', 'DetailView')}
			<div class="row">
				<div class="notificationsNotice quickAction{if AppConfig::module('Home', 'AUTO_REFRESH_REMINDERS')} autoRefreshing{/if}">
					<div class="pull-left">
						{\App\Language::translate('LBL_NOTIFICATIONS')}
					</div>
 					<div class="pull-right">
 						<a class="btn btn-default isBadge" title="{\App\Language::translate('LBL_NOTIFICATIONS')}" href="index.php?module=Notification&view=List">
							<span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
							<span class="badge hide">0</span>
						</a>
					</div>
				</div>
			</div>
		{/if}
		<div class='row'>
			<div class="dropdown quickAction">
				<div class='pull-left'>
					{\App\Language::translate('LBL_QUICK_CREATE')}
				</div>
				<div class='pull-right'>
					<a id="mobile_menubar_quickCreate" class="dropdown-toggle btn btn-default" data-toggle="dropdown" title="{\App\Language::translate('LBL_QUICK_CREATE')}" href="#">
						<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
					</a>
					<ul class="dropdown-menu dropdown-menu-right commonActionsButtonDropDown">
						<li class="quickCreateModules">
							<div class="panel-default">
								<div class="panel-heading">
									<h4 class="panel-title"><strong>{\App\Language::translate('LBL_QUICK_CREATE')}</strong></h4>
								</div>
								<div class="panel-body paddingLRZero">
									{assign var='count' value=0}
									{foreach key=NAME item=MODULEMODEL from=Vtiger_Module_Model::getQuickCreateModules(true)}
										{assign var='quickCreateModule' value=$MODULEMODEL->isQuickCreateSupported()}
										{assign var='singularLabel' value=$MODULEMODEL->getSingularLabelKey()}
										{if $singularLabel == 'SINGLE_Calendar'}
											{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
										{/if}	
										{if $quickCreateModule == '1'}
											{if $count % 3 == 0}
												<div class="rows">
												{/if}
												<div class="col-xs-4{if $count % 3 != 2} paddingRightZero{/if}">
													<a class="quickCreateModule list-group-item" data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)" title="{vtranslate($singularLabel,$NAME)}">
														<span>{\App\Language::translate($singularLabel,$NAME)}</span>
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
		</div>
	</div>
	{if AppConfig::performance('GLOBAL_SEARCH')}
		<div class="searchMenu globalSearchInput">
			<div class="input-group">
				<select class="chzn-select basicSearchModulesList form-control col-md-5" title="{\App\Language::translate('LBL_SEARCH_MODULE')}">
					<option value="" class="globalSearch_module_All">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
					{foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
						{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $MODULE_NAME && $SEARCHED_MODULE !== 'All'}
							<option value="{$MODULE_NAME}" selected>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
						{else}
							<option value="{$MODULE_NAME}" >{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
						{/if}
					{/foreach}
				</select>
				<div class="input-group-btn">
					<div class="pull-right">
						<button class="btn btn-default globalSearch " title="{vtranslate('LBL_ADVANCE_SEARCH')}" type="button">
							<span class="glyphicon glyphicon-th-large"></span>
						</button>
					</div>
				</div>
			</div>
			<div class="input-group">
				<input type="text" class="form-control globalSearchValue" title="{vtranslate('LBL_GLOBAL_SEARCH')}" placeholder="{vtranslate('LBL_GLOBAL_SEARCH')}" results="10" />
				<div class="input-group-btn">
					<div class="pull-right">
						<button class="btn btn-default searchIcon" type="button">
							<span class="glyphicon glyphicon-search"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	{/if}
{/strip}
