{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="actionMenu" aria-hidden="true">
		{if AppConfig::performance('BROWSING_HISTORY_WORKING')}
			<div class="row">
				<div class="dropdown quickAction historyBtn">
					<div class="pull-left">
						{\App\Language::translate('LBL_PAGES_HISTORY')}
					</div>
					<div class="pull-right">
						<a data-placement="left" data-toggle="dropdown" class="btn btn-default btn-sm showHistoryBtn" title="{\App\Language::translate('LBL_PAGES_HISTORY')}" aria-expanded="false" href="#">
							<span class="fas fa-history" aria-hidden="true"></span>
						</a>
						{include file=\App\Layout::getTemplatePath('BrowsingHistory.tpl', $MODULE)}
					</div>
				</div>
			</div>
		{/if}
		{if $REMINDER_ACTIVE}
			<div class="row">
				<div class="remindersNotice popoverTooltip quickAction{if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')} autoRefreshing{/if}">
					<div class="pull-left">
						{\App\Language::translate('LBL_REMINDER')}
					</div>
					<div class="pull-right">
						<a class="btn btn-default {if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}" title="{\App\Language::translate('LBL_REMINDER')}" data-content="{\App\Language::translate('LBL_REMINDER')}">
							<span class="fas fa-calendar-alt" aria-hidden="true"></span>
							<span class="badge bgDanger hide">0</span>
						</a>
					</div>
				</div>
			</div>
		{/if}
		{if isset($CHAT_ENTRIES)}
			<div class="row">
				<div class="headerLinkChat quickAction">
					<div class="pull-left">
						{\App\Language::translate('LBL_CHAT')}
					</div>
					<div class="pull-right">
						<a class="btn btn-default ChatIcon " title="{\App\Language::translate('LBL_CHAT')}" href="#">
							<span class="fas fa-comments" aria-hidden="true"></span>
						</a>
					</div>
				</div>
			</div>
		{/if}
		{if \App\Privilege::isPermitted('Notification', 'DetailView')}
			<div class="row">
				<div class="isBadge notificationsNotice popoverTooltip quickAction{if AppConfig::module('Home', 'AUTO_REFRESH_REMINDERS')} autoRefreshing{/if}">
					<div class="pull-left">
						{\App\Language::translate('LBL_NOTIFICATIONS')}
					</div>
 					<div class="pull-right">
 						<a class="btn btn-default {if AppConfig::module('Notification', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}" title="{\App\Language::translate('LBL_NOTIFICATIONS')}" >
							<span class="fas fa-bell" aria-hidden="true"></span>
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
						<span class="fas fa-plus" aria-hidden="true"></span>
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
													<a class="quickCreateModule list-group-item" data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)" title="{\App\Language::translate($singularLabel,$NAME)}">
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
		{foreach key=index item=obj from=$MENU_HEADER_LINKS}
			{if $obj->linktype == 'HEADERLINK'}
				{assign var="HREF" value='#'}
				{assign var="ICON_PATH" value=$obj->getIconPath()}
				{assign var="LINK" value=$obj->convertToNativeLink()}
				{assign var="GLYPHICON" value=$obj->getGlyphiconIcon()}
				{assign var="TITLE" value=$obj->getLabel()}
				{assign var="CHILD_LINKS" value=$obj->getChildLinks()}
				{if !empty($LINK)}
					{assign var="HREF" value=$LINK}
				{/if}
				<div class="row">
					<div class="quickAction">
						<div class="pull-left">
							{\App\Language::translate($TITLE,$MODULE)}
						</div>
						<div class="pull-right">
							<a class="btn btn-sm popoverTooltip {if $obj->getClassName()|strpos:"btn-" === false}btn-default {$obj->getClassName()}{else}{$obj->getClassName()}{/if} {if !empty($CHILD_LINKS)}dropdownMenu{/if} " href="{$HREF}"
								{if isset($obj->linkdata) && $obj->linkdata && is_array($obj->linkdata)}
									{foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
										data-{$DATA_NAME}="{$DATA_VALUE}"
									{/foreach}
								{/if}>
								{if $GLYPHICON}
									<span class="{$GLYPHICON}" aria-hidden="true" style="width:18px;height:20px;font-size:18px"></span>
								{/if}
								{if $ICON_PATH}
									<img src="{$ICON_PATH}" alt="{\App\Language::translate($TITLE,$MODULE)}" title="{\App\Language::translate($TITLE,$MODULE)}" />
								{/if}
							</a>
						</div>
					</div>
				</div>
				{if !empty($CHILD_LINKS)}
					<ul class="dropdown-menu">
						{foreach key=index item=obj from=$CHILD_LINKS}
							{if $obj->getLabel() eq NULL}
								<li class="divider"></li>
							{else}
								{assign var="id" value=$obj->getId()}
								{assign var="href" value=$obj->getUrl()}
								{assign var="label" value=$obj->getLabel()}
								{assign var="onclick" value=""}
								{if stripos($obj->getUrl(), 'javascript:') === 0}
									{assign var="onclick" value="onclick="|cat:$href}
									{assign var="href" value="javascript:;"}
								{/if}
								<li>
									<a target="{$obj->target}" id="menubar_item_right_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($label)}" {if $label=='Switch to old look'}switchLook{/if} href="{$href}" {$onclick}
									{if $obj->linkdata && is_array($obj->linkdata)}
										{foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
											data-{$DATA_NAME}="{$DATA_VALUE}"
										{/foreach}
									{/if}>{\App\Language::translate($label,$MODULE)}</a>
								</li>
							{/if}
						{/foreach}
					</ul>
				{/if}
			{/if}
		{/foreach}
	</div>
	{if AppConfig::performance('GLOBAL_SEARCH')}
		<div class="searchMenu globalSearchInput">
			<div class="input-group mb-1">
				<div class ="chzn-selectWithButtonWidth">
				<select class="chzn-select basicSearchModulesList form-control" title="{\App\Language::translate('LBL_SEARCH_MODULE')}">
					<option value="" class="globalSearch_module_All">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
					{foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
						{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $MODULE_NAME && $SEARCHED_MODULE !== 'All'}
							<option value="{$MODULE_NAME}" selected>{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}</option>
						{else}
							<option value="{$MODULE_NAME}" >{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}</option>
						{/if}
					{/foreach}
				</select>
				</div>
				<div class="input-group-append">
					<div class="">
						<button class="btn btn-outline-dark globalSearch " title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
							<span class="fas fa-th-large"></span>
						</button>
					</div>
				</div>
			</div>
			<div class="input-group">
				<input type="text" class="form-control globalSearchValue" title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10" />
				<div class="input-group-btn">
					<div class="pull-right">
						<button class="btn btn-outline-dark searchIcon" type="button">
							<span class="fa fa-search"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	{/if}
{/strip}
