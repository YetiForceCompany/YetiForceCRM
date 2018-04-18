{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="actionMenu" aria-hidden="true">
		{if AppConfig::performance('BROWSING_HISTORY_WORKING')}
			<div class="row">
				<div class="dropdown quickAction historyBtn">
					<div class="float-left">
						{\App\Language::translate('LBL_PAGES_HISTORY')}
					</div>
					<div class="float-right">
						<a data-placement="left" data-toggle="dropdown" class="btn btn-default btn-sm showHistoryBtn" title="{\App\Language::translate('LBL_PAGES_HISTORY')}" aria-expanded="false" href="#">
							<span class="fas fa-history"></span>
						</a>
						{include file=\App\Layout::getTemplatePath('BrowsingHistory.tpl', $MODULE)}
					</div>
				</div>
			</div>
		{/if}
		{if $REMINDER_ACTIVE}
			<div class="row">
				<div class="remindersNotice js-popover-tooltip quickAction{if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')} autoRefreshing{/if}" data-js="popover">
					<div class="pull-left">
						{\App\Language::translate('LBL_REMINDER')}
					</div>
					<div class="pull-right">
						<a class="btn btn-default {if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}" title="{\App\Language::translate('LBL_REMINDER')}" data-content="{\App\Language::translate('LBL_REMINDER')}">
							<span class="fas fa-calendar-alt"></span>
							<span class="badge bgDanger d-none">0</span>
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
							<span class="fas fa-comments"></span>
						</a>
					</div>
				</div>
			</div>
		{/if}
		{if \App\Privilege::isPermitted('Notification', 'DetailView')}
			<div class="row">
				<div class="isBadge notificationsNotice js-popover-tooltip quickAction{if AppConfig::module('Home', 'AUTO_REFRESH_REMINDERS')} autoRefreshing{/if}" data-js="popover">
					<div class="pull-left">
						{\App\Language::translate('LBL_NOTIFICATIONS')}
					</div>
					<div class="pull-right">
						<a class="btn btn-default {if AppConfig::module('Notification', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}" title="{\App\Language::translate('LBL_NOTIFICATIONS')}" >
							<span class="fas fa-bell"></span>
							<span class="badge d-none">0</span>
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
						<span class="fas fa-plus"></span>
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
												<div class="col-4{if $count % 3 != 2} paddingRightZero{/if}">
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
				{assign var="ICON" value=$obj->getHeaderIcon()}
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
							<a class="btn btn-sm js-popover-tooltip {if $obj->getClassName()|strpos:"btn-" === false}btn-default {$obj->getClassName()}{else}{$obj->getClassName()}{/if} {if !empty($CHILD_LINKS)}dropdownMenu{/if} " data-js="popover" href="{$HREF}"
							   {if isset($obj->linkdata) && $obj->linkdata && is_array($obj->linkdata)}
								   {foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
									   data-{$DATA_NAME}="{$DATA_VALUE}"
								   {/foreach}
							   {/if}>
								{if $ICON}
									<span class="{$ICON}" style="width:18px;height:20px;font-size:18px"></span>
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
								<li class="dropdown-divider"></li>
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
	
{/strip}
