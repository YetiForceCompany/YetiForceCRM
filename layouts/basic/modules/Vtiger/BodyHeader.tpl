{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
    {assign var='count' value=0}
	<header class="navbar navbar-expand-md navbar-dark fixed-top px-2 bodyHeader{if $LEFTPANELHIDE} menuOpen{/if}">
		{if AppConfig::performance('GLOBAL_SEARCH')}
			<div class="searchMenuBtn d-xl-none">
				<div class="quickAction">
					<a class="btn btn-light" href="#">
						<span class="fas fa-search fa-fw"></span>
					</a>
				</div>
			</div>
			<div class="input-group input-group-sm d-none d-xl-flex globalSearchInput">
				<div class="input-group-prepend select2HeaderWidth">
					<select class="select2 basicSearchModulesList form-control" title="{\App\Language::translate('LBL_SEARCH_MODULE')}">
						<option value="-">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
						{foreach key=SEARCHABLE_MODULE item=fieldObject from=$SEARCHABLE_MODULES}
							{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $SEARCHABLE_MODULE && $SEARCHED_MODULE !== 'All'}
								<option value="{$SEARCHABLE_MODULE}" selected>{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
							{else}
								<option value="{$SEARCHABLE_MODULE}">{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
							{/if}
						{/foreach}
					</select>
				</div>
				<input type="text" class="form-control form-control-sm globalSearchValue" title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10" data-operator="{AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR')}" />
				<div class="input-group-append bg-white rounded-right">
					<button class="btn btn-outline-dark border-0 searchIcon" type="button">
						<span class="fas fa-search fa-fw"></span>
					</button>
					{if AppConfig::search('GLOBAL_SEARCH_OPERATOR_SELECT')}
						<div class="btn-group">
							<button type="button" class="btn btn-outline-dark border-bottom-0 border-top-0 dropdown-toggle rounded-0 border-left border-right" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="fas fa-crosshairs fa-fw"></span>
							</button>
							<div class="dropdown-menu globalSearchOperator">
								<a class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'FulltextBegin'}active{/if} dropdown-item" href="#" data-operator="FulltextBegin">
									{\App\Language::translate('LBL_FULLTEXT_BEGIN')}
								</a>
								<a class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'FulltextWord'}active{/if} dropdown-item" href="#" data-operator="FulltextWord">
									{\App\Language::translate('LBL_FULLTEXT_WORD')}
								</a>
								<a class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'Contain'}active{/if} dropdown-item" href="#" data-operator="Contain">
									{\App\Language::translate('LBL_CONTAINS')}
								</a>
								<a class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'Begin'}active{/if} dropdown-item" href="#" data-operator="Begin">
									{\App\Language::translate('LBL_STARTS_WITH')}
								</a>
								<a class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'End'}active{/if} dropdown-item" href="#" data-operator="End">
									{\App\Language::translate('LBL_ENDS_WITH')}
								</a>
							</div>
						</div>
					{/if}
					<button class="btn btn-outline-dark border-0 globalSearch" title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
						<span class="fa fa-th-large fa-fw"></span>
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
								<a class="btn btn-outline-dark border-0" title="{$MAIN_MAIL.username}" href="index.php?module=OSSMail&view=Index">
									<div class="d-none d-sm-none d-md-block">
										{$ITEM.username}
										<span class="mail_user_name">{$MAIN_MAIL.username}</span>
										<span data-id="{$MAIN_MAIL.rcuser_id}" class="noMails"></span>
									</div>
									<div class="d-none d-block d-sm-block d-md-none">
										<span class="fas fa-inbox fa-fw"></span>
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
					<a class="btn btn-light btn" href="#">
						<span class="fas fa-bars fa-fw"></span>
					</a>
				</div>
			</div>
			<div class="actionMenuBtn">
				<div class="quickAction">
					<a class="btn btn-light btn" href="#">
						<span class="fas fa-certificate fa-fw"></span>
					</a>
				</div>
			</div>
			<div class="noSpaces">
				<div class="rightHeader">
					{assign var=QUICKCREATE_MODULES value=Vtiger_Module_Model::getQuickCreateModules(true)}
					{if !empty($QUICKCREATE_MODULES)}
						<span class="commonActionsContainer">
							<a class="headerButton btn-light btn popoverTooltip dropdownMenu d-none d-lg-inline-block" data-toggle="modal" data-target="#quickCreateModules" data-placement="bottom" data-content="{\App\Language::translate('LBL_QUICK_CREATE')}" href="#">
								<span class="fas fa-plus fa-fw"></span>
							</a>
							<div class="quickCreateModules modal fade" id="quickCreateModules" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog modal-lg" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<strong>{\App\Language::translate('LBL_QUICK_CREATE')}</strong>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											{foreach key=NAME item=MODULEMODEL from=$QUICKCREATE_MODULES}
												{assign var='quickCreateModule' value=$MODULEMODEL->isQuickCreateSupported()}
												{assign var='singularLabel' value=$MODULEMODEL->getSingularLabelKey()}
												{if $singularLabel == 'SINGLE_Calendar'}
													{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
												{/if}
												{if $quickCreateModule == '1'}
													{if $count % 3 == 0}
														<div class="row small">
														{/if}
														<div class="col-4{if $count % 3 != 2} paddingRightZero{/if}">
															<a id="menubar_quickCreate_{$NAME}" class="quickCreateModule" data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)" title="{\App\Language::translate($singularLabel,$NAME)}">
																<span class="modCT_{$NAME} userIcon-{$NAME}"></span>&nbsp;<span>{\App\Language::translate($singularLabel,$NAME)}</span>
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
										<div class="modal-footer">
											<button class="btn btn-warning btn-sm" type="reset" data-dismiss="modal"><strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
										</div>
									</div>
								</div>
							</div>
						</span>
					{/if}
					{if \App\Privilege::isPermitted('Notification', 'DetailView')}
						<a class="headerButton btn btn-light btn isBadge notificationsNotice popoverTooltip {if AppConfig::module('Notification', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if} d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_NOTIFICATIONS')}">
							<span class="fas fa-bell fa-fw"></span>
							<span hidden class="badge">0</span>
						</a>
					{/if}
					{if isset($CHAT_ENTRIES)}
						<a class="headerButton btn btn-light btn headerLinkChat popoverTooltip d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_CHAT')}" href="#">
							<span class="fas fa-comments fa-fw"></span>
						</a>
						<div class="chatModal modal fade" tabindex="-1" role="dialog" aria-labelledby="chatLabel" data-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}000">
							<div class="modal-dialog modalRightSiteBar" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title" id="myModalLabel">
											<span class="fas fa-comments fa-fw"></span>&nbsp;&nbsp;
											{\App\Language::translate('LBL_CHAT')}
										</h4>
										<button type="button" class="btn btn-warning p-0 float-right marginLeft10" data-dismiss="modal" aria-hidden="true">&times;</button>
									</div>
									<div class="modal-body">
										{include file=\App\Layout::getTemplatePath('Items.tpl', 'Chat')}
									</div>
									<div class="modal-footer pinToDown row mx-0 d-block">
										<input type="text" class="form-control message" />
										<button type="button" class="btn btn-primary addMsg float-right mt-2">{\App\Language::translate('LBL_SEND_MESSAGE')}</button>
									</div>
								</div>
							</div>
						</div>
					{/if}
					{if $REMINDER_ACTIVE}
						<a class="headerButton btn btn-light btn isBadge remindersNotice popoverTooltip {if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if} d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_REMINDER')}" href="#">
							<span class="fas fa-calendar fa-fw"></span>
							<span hidden class="badge bgDanger">0</span>
						</a>
					{/if}
					{if AppConfig::performance('BROWSING_HISTORY_WORKING')}
						<a class="headerButton btn btn-light btn showHistoryBtn popoverTooltip dropdownMenu d-none d-lg-inline-block" data-content="{\App\Language::translate('LBL_PAGES_HISTORY')}" href="#">
							<i class="fas fa-history fa-fw"></i>
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
							<a class="headerButton btn btn popoverTooltip {if $obj->getClassName()|strrpos:"btn-" === false}btn-light {$obj->getClassName()}{else}{$obj->getClassName()}{/if} {if !empty($CHILD_LINKS)}dropdownMenu{/if} d-none d-lg-inline-block" data-content="{\App\Language::translate($TITLE)}" href="{$HREF}"
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
			</div>
		</div>
	</header>
{/strip}
