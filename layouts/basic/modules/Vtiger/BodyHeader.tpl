{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
    {assign var='count' value=0}
	<nav class="navbar navbar-expand-md navbar-dark fixed-top px-2 bodyHeader{if $LEFTPANELHIDE} menuOpen{/if}">
		{if AppConfig::performance('GLOBAL_SEARCH')}
			<div class="searchMenuBtn d-xl-none">
				<div class="quickAction">
					<a class="btn btn-light p-1" href="#">
						<span class="fas fa-search fa-fw fa-lg"></span>
					</a>
				</div>
			</div>
			<div class="input-group input-group-sm mb-2 d-none d-xl-flex globalSearchInput">
				<div class="input-group-prepend">
					<select class="chzn-select basicSearchModulesList form-control" title="{\App\Language::translate('LBL_SEARCH_MODULE')}">
						<option value="">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
						{foreach key=SEARCHABLE_MODULE item=fieldObject from=$SEARCHABLE_MODULES}
							{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $SEARCHABLE_MODULE && $SEARCHED_MODULE !== 'All'}
								<option value="{$SEARCHABLE_MODULE}" selected>{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
							{else}
								<option value="{$SEARCHABLE_MODULE}">{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
							{/if}
						{/foreach}
					</select>
				</div>
				<input type="text" class="form-control globalSearchValue" title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10" data-operator="contains" />
				<div class="input-group-append bg-white rounded-right">
					<button class="btn btn-outline-dark p-1 border-0 searchIcon" type="button">
						<span class="fas fa-search fa-fw fa-lg"></span>
					</button>
					{if AppConfig::search('GLOBAL_SEARCH_OPERATOR')}
						<div class="btn-group">
							<button type="button" class="btn btn-outline-dark p-1 border-bottom-0 border-top-0 dropdown-toggle rounded-0 border-left border-right" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="fas fa-crosshairs fa-fw fa-lg"></span>
							</button>
							<ul class="dropdown-menu globalSearchOperator">
								<li class="active"><a href="#" data-operator="contains">{\App\Language::translate('contains')}</a></li>
								<li><a href="#" data-operator="begin">{\App\Language::translate('starts with')}</a></li>
								<li><a href="#" data-operator="ends">{\App\Language::translate('ends with')}</a></li>
							</ul>
						</div>
					{/if}
					<button class="btn btn-outline-dark p-1 border-0 globalSearch" title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
						<span class="fa fa-th-large fa-fw fa-lg"></span>
					</button>
				</div>
			</div>
		{/if}
		<div class="headerRightWrapper ml-auto d-inline-flex">
			{if !Settings_ModuleManager_Library_Model::checkLibrary('roundcube')}
				{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('mailIcon')}
				{if $CONFIG['showMailIcon']=='true' && App\Privilege::isPermitted('OSSMail')}
					{assign var=AUTOLOGINUSERS value=OSSMail_Autologin_Model::getAutologinUsers()}
					{if count($AUTOLOGINUSERS) > 0}
						{assign var=MAIN_MAIL value=OSSMail_Module_Model::getDefaultMailAccount($AUTOLOGINUSERS)}
						<div class="headerLinksMails bg-white rounded" id="OSSMailBoxInfo" {if $CONFIG['showNumberUnreadEmails']=='true'}data-numberunreademails="true" data-interval="{$CONFIG['timeCheckingMail']}"{/if}>
							{if count($AUTOLOGINUSERS) eq 1}
								<a class="btn btn-outline-dark p-1 border-0 m-0 py-2" title="{$MAIN_MAIL.username}" href="index.php?module=OSSMail&view=Index">
									<div class="d-none d-sm-none d-md-block">
										{$ITEM.username}
										<span class="mail_user_name">{$MAIN_MAIL.username}</span>
										<span data-id="{$MAIN_MAIL.rcuser_id}" class="noMails"></span>
									</div>
									<div class="d-none d-block d-sm-block d-md-none">
										<span class="fas fa-inbox fa-fw fa-lg"></span>
									</div>
								</a>
							{elseif $CONFIG['showMailAccounts']=='true'}
								<select class="form-control" title="{\App\Language::translate('LBL_SEARCH_MODULE', $MODULE_NAME)}">
									{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
										<option value="{$KEY}" {if $ITEM.active}selected{/if} data-id="{$KEY}" data-nomail="" class="noMails">
											{$ITEM.username}
										</option>
									{/foreach}
								</select>
							{/if}
						</div>
					{/if}
				{/if}
			{/if}
			<div class="rightHeaderBtnMenu">
				<div class="quickAction">
					<a class="btn btn-light btn p-1" href="#">
						<span class="fas fa-bars fa-fw fa-lg"></span>
					</a>
				</div>
			</div>
			<div class="actionMenuBtn">
				<div class="quickAction">
					<a class="btn btn-light btn p-1" href="#">
						<span class="fas fa-certificate fa-fw fa-lg"></span>
					</a>
				</div>
			</div>
			<div class="noSpaces">
				<div class="rightHeader">
					{assign var=QUICKCREATE_MODULES value=Vtiger_Module_Model::getQuickCreateModules(true)}
					{if !empty($QUICKCREATE_MODULES)}
						<a class="btn-light p-1 btn popoverTooltip dropdownMenu d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_QUICK_CREATE')}" href="#">
							<span class="fas fa-plus fa-fw fa-lg"></span>
						</a>
						<ul class="dropdown-menu dropdown-menu-right commonActionsButtonDropDown">
							<li class="quickCreateModules">
								<div class="card">
									<div class="card-header">
										<h4 class="card-title"><strong>{\App\Language::translate('LBL_QUICK_CREATE')}</strong></h4>
									</div>
									<div class="card-body paddingLRZero">
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
													<div class="col-sm-4{if $count % 3 != 2} paddingRightZero{/if}">
														<a id="menubar_quickCreate_{$NAME}" class="quickCreateModule list-group-item" data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)" title="{\App\Language::translate($singularLabel,$NAME)}">
															<span class="modCT_{$NAME} userIcon-{$NAME}"></span><span>{\App\Language::translate($singularLabel,$NAME)}</span>
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
							</li>
						</ul>
					{/if}
					{if \App\Privilege::isPermitted('Notification', 'DetailView')}
						<a class="btn btn-light p-1 btn isBadge notificationsNotice popoverTooltip {if AppConfig::module('Notification', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if} d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_NOTIFICATIONS')}">
							<span class="fas fa-bell fa-fw fa-lg"></span>
							<span hidden class="badge">0</span>
						</a>
					{/if}
					{if isset($CHAT_ENTRIES)}
						<a class="btn btn-light p-1 btn headerLinkChat popoverTooltip d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_CHAT')}" href="#">
							<span class="fas fa-comments fa-fw fa-lg"></span>
						</a>
						<div class="chatModal modal fade" tabindex="-1" role="dialog" aria-labelledby="chatLabel" data-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}000">
							<div class="modal-dialog modalRightSiteBar" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="btn btn-warning float-right marginLeft10" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="myModalLabel">
											<span class="fas fa-comments fa-fw fa-lg"></span>&nbsp;&nbsp;
											{\App\Language::translate('LBL_CHAT')}
										</h4>
									</div>
									<div class="modal-body">
										{include file=\App\Layout::getTemplatePath('Items.tpl', 'Chat')}
									</div>
									<div class="modal-footer pinToDown">
                                        <input type="text" class="form-control message" /><br />
										<button type="button" class="btn btn-primary addMsg">{\App\Language::translate('LBL_SEND_MESSAGE')}</button>
									</div>
								</div>
							</div>
						</div>
					{/if}
					{if $REMINDER_ACTIVE}
						<a class="btn btn-light p-1 btn isBadge remindersNotice popoverTooltip {if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if} d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_REMINDER')}" href="#">
							<span class="fas fa-calendar fa-fw fa-lg"></span>
							<span hidden class="badge bgDanger">0</span>
						</a>
					{/if}
					{if AppConfig::performance('BROWSING_HISTORY_WORKING')}
						<a class="btn btn-light p-1 btn showHistoryBtn popoverTooltip dropdownMenu d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_PAGES_HISTORY')}" href="#">
							<i class="fas fa-history fa-fw fa-lg"></i>
						</a>
						{include file=\App\Layout::getTemplatePath('BrowsingHistory.tpl', $MODULE)}
					{/if}
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
							<a class="btn btn popoverTooltip {if $obj->getClassName()|strrpos:"btn-" === false}btn-light p-1 {$obj->getClassName()}{else}{$obj->getClassName()}{/if} {if !empty($CHILD_LINKS)}dropdownMenu{/if} d-none d-lg-inline-block" data-content="{\App\Language::translate($TITLE)}" href="{$HREF}"
							   {if isset($obj->linkdata) && $obj->linkdata && is_array($obj->linkdata)}
								   {foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
									   data-{$DATA_NAME}="{$DATA_VALUE}"
								   {/foreach}
							   {/if}>
								{if $GLYPHICON}
									<span class="{$GLYPHICON}"></span>
								{/if}
								{if $ICON_PATH}
									<img src="{$ICON_PATH}" alt="{\App\Language::translate($TITLE,$MODULE)}" title="{\App\Language::translate($TITLE,$MODULE)}" />
								{/if}
							</a>
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
			</div>
		</div>
	</nav>
{/strip}
