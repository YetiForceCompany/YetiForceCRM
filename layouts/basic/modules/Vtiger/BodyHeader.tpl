{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var='count' value=0}
	<header class="tpl-BodyHeader navbar navbar-expand-md navbar-dark fixed-top px-2 js-header bodyHeader"
			data-js="height">
		<div class="o-navbar__left d-inline-flex">
			<div class="rightHeaderBtnMenu">
				<div class="quickAction">
					<a class="btn btn-light headerButton ml-0 js-sidebar-btn" role="button" href="#" data-js="click" aria-haspopup="true" aria-expanded="false">
						<span class="fas fa-bars fa-fw" title="{\App\Language::translate('LBL_MENU')}"></span>
					</a>
				</div>
			</div>

			{if AppConfig::performance('GLOBAL_SEARCH')}
				<div class="globalSearchInput input-group input-group-sm d-none d-xl-flex">
					<div class="input-group-prepend select2HeaderWidth">
						<select class="select2 basicSearchModulesList form-control"
								title="{\App\Language::translate('LBL_SEARCH_MODULE')}" data-dropdown-auto-width="true">
							<option value="-">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
							{foreach key=SEARCHABLE_MODULE item=fieldObject from=$SEARCHABLE_MODULES}
								{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $SEARCHABLE_MODULE && $SEARCHED_MODULE !== 'All'}
									<option value="{$SEARCHABLE_MODULE}"
											selected>{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
								{else}
									<option value="{$SEARCHABLE_MODULE}">{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
								{/if}
							{/foreach}
						</select>
					</div>
					<input type="text" class="form-control form-control-sm globalSearchValue"
						   title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}"
						   placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10"
						   data-operator="{AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR')}"/>
					<div class="input-group-append bg-white rounded-right">
						<button class="btn btn-outline-dark border-0 searchIcon" type="button">
							<span class="fas fa-search fa-fw" title="{\App\Language::translate('LBL_SEARCH')}"></span>
						</button>
						{if AppConfig::search('GLOBAL_SEARCH_OPERATOR_SELECT')}
							<div class="btn-group">
								<a class="btn btn-outline-dark border-bottom-0 border-top-0 dropdown-toggle rounded-0 border-left border-right" id="globalSearchOperator" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<span class="fas fa-crosshairs fa-fw" title="{\App\Language::translate('LBL_SPECIAL_OPTIONS')}"></span>
								</a>
								<ul class="dropdown-menu js-global-search-operator"
									aria-labelledby="globalSearchOperator" data-js="click">
									<li class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'FulltextBegin'}active{/if} dropdown-item u-cursor-pointer"
										href="#" data-operator="FulltextBegin">
										{\App\Language::translate('LBL_FULLTEXT_BEGIN')}
									</li>
									<li class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'FulltextWord'}active{/if} dropdown-item u-cursor-pointer"
										href="#" data-operator="FulltextWord">
										{\App\Language::translate('LBL_FULLTEXT_WORD')}
									</li>
									<li class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'Contain'}active{/if} dropdown-item u-cursor-pointer"
										href="#" data-operator="Contain">
										{\App\Language::translate('LBL_CONTAINS')}
									</li>
									<li class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'Begin'}active{/if} dropdown-item u-cursor-pointer"
										href="#" data-operator="Begin">
										{\App\Language::translate('LBL_STARTS_WITH')}
									</li>
									<li class="{if AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR') === 'End'}active{/if} dropdown-item u-cursor-pointer"
										href="#" data-operator="End">
										{\App\Language::translate('LBL_ENDS_WITH')}
									</li>
								</ul>
							</div>
						{/if}
						<button class="btn btn-outline-dark border-0 globalSearch" title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
							<span class="fa fa-th-large fa-fw"></span>
						</button>
					</div>
				</div>
				<div class="searchMenu d-xl-none">
					<div class="searchMenuBtn">
						<div class="quickAction">
							<a class="btn btn-light headerButton" href="#" role="button" aria-expanded="false" aria-controls="o-search-menu__container">
								<span class="fas fa-search fa-fw" title="{\App\Language::translate('LBL_SEARCH')}"></span>
							</a>
						</div>
					</div>
					<div class="o-search-menu__container" id="o-search-menu__container">
						<div class="input-group mb-3">
							<div class="form-control select2WithButtonWidth">
								<select class="select2 basicSearchModulesList"
										title="{\App\Language::translate('LBL_SEARCH_MODULE')}">
									<option value="-"
											class="globalSearch_module_All">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
									{foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
										{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $MODULE_NAME && $SEARCHED_MODULE !== 'All'}
											<option value="{$MODULE_NAME}"
													selected>{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}</option>
										{else}
											<option value="{$MODULE_NAME}">{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}</option>
										{/if}
									{/foreach}
								</select>
							</div>
							<div class="input-group-append">
								<button class="btn btn-light globalSearch" title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
									<span class="fas fa-th-large"></span>
								</button>
							</div>
						</div>
						<div class="input-group mb-3">
							<input type="text" class="form-control globalSearchValue"
								   title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}"
								   placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10"/>
							<div class="input-group-append">
								<button class="btn btn-light searchIcon" type="button">
									<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
								</button>
							</div>
						</div>
						<div class="searchMenuBtn">
							<a class="btn btn-light headerButton float-right" href="#" role="button" aria-expanded="false" aria-controls="o-search-menu__container">
								<span class="fas fa-times fa-fw" title="{\App\Language::translate('LBL_CLOSE')}"></span>
								<span>{\App\Language::translate('LBL_CLOSE')}</span>
							</a>
						</div>
					</div>
				</div>
			{/if}
		</div>

		<div class="o-navbar__right ml-auto d-inline-flex">
			{if $PARENT_MODULE === 'Settings'}
				<div class="mr-4">
					<a class="btn btn-light headerButton js-popover-tooltip" role="button" href="https://yetiforce.shop" data-content="{\App\Language::translate('LBL_YETIFORCE_SHOP',$QUALIFIED_MODULE)}" target="_blank">
						<span class="fas fa-shopping-cart fa-fw" title="{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}"></span>
					</a>
					<a class="btn btn-light headerButton js-popover-tooltip" role="button" href="https://yetiforce.shop/#support" data-content="{\App\Language::translate('LBL_YETIFORCE_ASSISTANCE',$QUALIFIED_MODULE)}" target="_blank">
						<span class="far fa-life-ring fa-fw" title="{\App\Language::translate('LBL_YETIFORCE_ASSISTANCE', $QUALIFIED_MODULE)}"></span>
					</a>
					<a class="btn btn-light headerButton js-popover-tooltip" role="button" href="https://github.com/YetiForceCompany/YetiForceCRM/issues" data-content="{\App\Language::translate('LBL_YETIFORCE_ISSUES',$QUALIFIED_MODULE)}" target="_blank">
						<span class="fas fa-bug fa-fw" title="{\App\Language::translate('LBL_YETIFORCE_ISSUES', $QUALIFIED_MODULE)}"></span>
					</a>
				</div>
			{/if}
			{if !Settings_ModuleManager_Library_Model::checkLibrary('roundcube')}
				{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('mailIcon')}
				{if $CONFIG['showMailIcon']=='true' && App\Privilege::isPermitted('OSSMail')}
					{assign var=AUTOLOGINUSERS value=OSSMail_Autologin_Model::getAutologinUsers()}
					{if count($AUTOLOGINUSERS) > 0}
						{assign var=MAIN_MAIL value=OSSMail_Module_Model::getDefaultMailAccount($AUTOLOGINUSERS)}
						<div class="headerLinksMails bg-white rounded" id="OSSMailBoxInfo"
							 {if $CONFIG['showNumberUnreadEmails']=='true'}data-numberunreademails="true"
							 data-interval="{$CONFIG['timeCheckingMail']}"{/if}>
							{if count($AUTOLOGINUSERS) eq 1}
								<a class="btn btn-outline-dark border-0" title="{$MAIN_MAIL.username}"
								   href="index.php?module=OSSMail&view=Index">
									<div class="d-none d-sm-none d-md-block">
										{$ITEM.username}
										<span class="mail_user_name">{$MAIN_MAIL.username}</span>
										<span data-id="{$MAIN_MAIL.rcuser_id}" class="noMails"></span>
									</div>
									<div class="d-none d-block d-sm-block d-md-none">
										<span class="fas fa-inbox fa-fw" title="{\App\Language::translate('LBL_EMAIL')}"></span>
									</div>
								</a>
							{elseif $CONFIG['showMailAccounts']=='true'}
								<select class="form-control"
										title="{\App\Language::translate('LBL_SEARCH_MODULE', $MODULE_NAME)}">
									{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
										<option value="{$KEY}" {if $ITEM.active}selected{/if} data-id="{$KEY}"
												data-nomail="" class="noMails">
											{$ITEM.username}
										</option>
									{/foreach}
								</select>
							{/if}
						</div>
					{/if}
				{/if}
			{/if}

			<nav class="actionMenu" aria-label="{\App\Language::translate("QUICK_ACCESS_MENU")}">
				<div class="actionMenuBtn">
					<div class="quickAction">
						<a class="btn btn-light headerButton" href="#" role="button" aria-expanded="false" aria-controls="o-action-menu__container">
							<span class="fas fa-ellipsis-h fa-fw" title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
						</a>
					</div>
				</div>
				<div class="o-action-menu__container" id="o-action-menu__container">
					{assign var=QUICKCREATE_MODULES value=Vtiger_Module_Model::getQuickCreateModules(true)}
					{if !empty($QUICKCREATE_MODULES)}
					<div class="o-action-menu__item commonActionsContainer">
						<a class="headerButton btn-light btn js-popover-tooltip dropdownMenu" role="button" data-js="popover" data-toggle="modal" data-target="#quickCreateModules" data-placement="bottom" data-content="{\App\Language::translate('LBL_QUICK_CREATE')}" href="#">
							<span class="fas fa-plus fa-fw" title="{\App\Language::translate('LBL_QUICK_CREATE')}"></span>
						</a>
						<div class="quickCreateModules modal fade" id="quickCreateModules" tabindex="-1" role="dialog"
							 aria-labelledby="c-quick-create__title" aria-hidden="true">
							<div class="modal-dialog modal-lg" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-tile" id="c-quick-create__title">
											<span class="fas fa-plus fa-fw mr-1"></span>
											{\App\Language::translate('LBL_QUICK_CREATE')}
										</h5>
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
													<div class="row">
												{/if}
												<div class="col-md-4">
													<a id="menubar_quickCreate_{$NAME}" class="quickCreateModule" data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)">
														<span class="modCT_{$NAME} userIcon-{$NAME}" title="{\App\Language::translate($singularLabel,$NAME)}"></span>
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
								<div class="modal-footer">
									<button class="btn btn-danger btn-sm" type="reset" data-dismiss="modal">
										<span class="fas fa-times mr-1"></span>
										<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				{/if}
				{if \App\Privilege::isPermitted('Notification', 'DetailView')}
					<div class="o-action-menu__item">
						<a class="headerButton btn btn-light btn isBadge notificationsNotice js-popover-tooltip {if AppConfig::module('Notification', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}" role="button" data-js="popover" data-content="{\App\Language::translate('LBL_NOTIFICATIONS')}" href="#">
							<span class="fas fa-bell fa-fw" title="{\App\Language::translate('LBL_NOTIFICATIONS')}"></span>
							<span class="badge badge-dark d-none">0</span>
						</a>
					</div>
				{/if}
				{if isset($CHAT_ENTRIES)}
					<div class="o-action-menu__item">
						<a class="headerButton btn btn-light btn headerLinkChat js-popover-tooltip" role="button" data-js="popover" data-content="{\App\Language::translate('LBL_CHAT')}" href="#">
							<span class="fas fa-comments fa-fw" title="{\App\Language::translate('LBL_CHAT')}"></span>
						</a>
						<div class="chatModal modal fade row" tabindex="-1" role="dialog"
							 aria-labelledby="c-chat-modal__title"
							 data-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}000">
							<div class="modal-dialog modalRightSiteBar" role="document">
								<div class="modal-content rounded-0">
									<div class="modal-header">
										<h5 class="modal-title" id="c-chat-modal__title">
											<span class="fas fa-comments fa-fw"></span>
											{\App\Language::translate('LBL_CHAT')}
										</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										{include file=\App\Layout::getTemplatePath('Items.tpl', 'Chat')}
									</div>
									<div class="modal-footer pinToDown row mx-0 d-block">
										<label for="c-chat-modal__message">{\App\Language::translate('LBL_MESSAGE', 'Notification')}</label>
										<input class="form-control message" id="c-chat-modal__message" type="text"/>
										<button type="button"
												class="btn btn-primary addMsg float-right mt-2">{\App\Language::translate('LBL_SEND_MESSAGE')}</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				{/if}
				{if $REMINDER_ACTIVE}
					<div class="o-action-menu__item">
						<a class="headerButton btn btn-light btn isBadge remindersNotice js-popover-tooltip {if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}" data-js="popover" role="button" data-content="{\App\Language::translate('LBL_REMINDER')}" href="#">
							<span class="fas fa-calendar fa-fw" title="{\App\Language::translate('LBL_REMINDER')}"></span>
							<span class="badge badge-danger d-none">0</span>
						</a>
					</div>
				{/if}
				{if AppConfig::performance('BROWSING_HISTORY_WORKING')}
					<div class="o-action-menu__item">
						<div class="dropdown">
							<a class="headerButton btn btn-light btn js-popover-tooltip dropdownMenu" id="showHistoryBtn" data-js="popover" data-toggle="dropdown" data-boundary="window" data-content="{\App\Language::translate('LBL_PAGES_HISTORY')}" href="#" role="button">
								<span class="fas fa-history fa-fw" title="{\App\Language::translate('LBL_PAGES_HISTORY')}"></span>
							</a>
							{include file=\App\Layout::getTemplatePath('BrowsingHistory.tpl', $MODULE)}
						</div>
					</div>
				{/if}
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
						<div class="o-action-menu__item">
							<a class="headerButton btn btn js-popover-tooltip {if $obj->getClassName()|strrpos:"btn-" === false}btn-light {$obj->getClassName()}{else}{$obj->getClassName()}{/if} {if !empty($CHILD_LINKS)}dropdownMenu{/if}" role="button" data-js="popover" data-content="{\App\Language::translate($TITLE)}" href="{$HREF}"
									{if isset($obj->linkdata) && $obj->linkdata && is_array($obj->linkdata)}
								{foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
									data-{$DATA_NAME}="{$DATA_VALUE}"
								{/foreach}
									{/if}>
								{if $ICON}
									<span class="{$ICON}" title="{\App\Language::translate($TITLE,$MODULE)}"></span>
								{/if}
								{if $ICON_PATH}
									<img src="{$ICON_PATH}" alt="{\App\Language::translate($TITLE,$MODULE)}" title="{\App\Language::translate($TITLE,$MODULE)}"/>
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
												<a class="dropdown-item" target="{$obj->target}"
												   id="menubar_item_right_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($label)}"
												   {if $label=='Switch to old look'}switchLook{/if}
												   href="{$href}" {$onclick}
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
						</div>
					{/if}
				{/foreach}
		</div>
		</nav>
		</div>
	</header>
{/strip}
