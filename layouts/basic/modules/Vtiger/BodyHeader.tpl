{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<!-- tpl-Base-BodyHeader -->
	{assign var='count' value=0}
	<header class="navbar navbar-expand-md navbar-dark fixed-top px-2 js-header c-header" data-js="height">
		<div class="o-navbar__left d-inline-flex">
			<div class="rightHeaderBtnMenu">
				<div class="quickAction">
					<a class="btn btn-light c-header__btn ml-0 js-sidebar-btn" role="button" href="#" data-js="click"
					   aria-haspopup="true" aria-expanded="false">
						<span class="fas fa-bars fa-fw" title="{\App\Language::translate('LBL_MENU')}"></span>
					</a>
				</div>
			</div>
			{if \App\Config::performance('GLOBAL_SEARCH')}
				{assign var='SEARCH_FIELD_MODEL' value=\App\RecordSearch::getSearchField()}
				<div class="js-global-search__input o-global-search__input o-global-search__input--desktop input-group input-group-sm d-none d-xl-flex mr-2"
					 data-js="container">
					<div class="input-group-prepend select2HeaderWidth">
						{assign var="USER_DEFAULT_MODULE" value=$USER_MODEL->get('default_search_module')}
						{assign var="DEFAULT_OVERRIDE" value=$USER_MODEL->get('default_search_override')}
						{assign var="SELECTABLE_ACTUAL_MODULE" value="{array_key_exists($MODULE_NAME,$SEARCHABLE_MODULES)}"}
						{assign var="SELECTABLE_USER_MODULE" value="{array_key_exists($USER_DEFAULT_MODULE,$SEARCHABLE_MODULES)}"}
						<select class="select2 basicSearchModulesList form-control"
								title="{\App\Language::translate('LBL_SEARCH_MODULE')}" data-dropdown-auto-width="true">
							<option value="-">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
							{foreach key=SEARCHABLE_MODULE item=fieldObject from=$SEARCHABLE_MODULES}
								{assign var="SELECTED" value=""}
								{if $SEARCHABLE_MODULE === $USER_DEFAULT_MODULE && ($DEFAULT_OVERRIDE || !$SELECTABLE_ACTUAL_MODULE) && $SELECTABLE_USER_MODULE}
									{assign var="SELECTED" value="selected"}
								{elseif !$USER_MODEL->get('default_override') && isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $SEARCHABLE_MODULE && $SEARCHED_MODULE !== 'All'}
									{assign var="SELECTED" value="selected"}
								{/if}
								<option value="{$SEARCHABLE_MODULE}" {$SELECTED}>{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
							{/foreach}
						</select>
					</div>
					<input id="global-search-__value" type="text"
						   class="form-control js-global-search__value o-global-search__value"
						   title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" maxlength="{$SEARCH_FIELD_MODEL->getMaxValue()}"
						   placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10"
						   data-js="keypress | value | autocomplete"/>
					<div class="input-group-append bg-white rounded-right">
						<button class="btn btn-outline-dark border-0 h-100 searchIcon" type="button">
							<span class="fas fa-search fa-fw" title="{\App\Language::translate('LBL_SEARCH')}"></span>
						</button>
						{if App\Config::search('GLOBAL_SEARCH_OPERATOR_SELECT')}
							<div class="btn-group u-remove-dropdown-icon">
								<a class="btn btn-outline-dark border-bottom-0 border-top-0 dropdown-toggle rounded-0 border-left border-right"
								   id="globalSearchOperator" href="#" role="button" data-toggle="dropdown"
								   aria-haspopup="true" aria-expanded="false">
									<span class="fas fa-crosshairs fa-fw"
										  title="{\App\Language::translate('LBL_SPECIAL_OPTIONS')}"></span>
								</a>
								<ul class="dropdown-menu js-global-search-operator"
									aria-labelledby="globalSearchOperator" data-js="click">
									{foreach key=LABEL item=VALUE from=\App\RecordSearch::OPERATORS}
										<li class="{if $USER_MODEL->get('default_search_operator') eq $LABEL}active{/if} dropdown-item u-cursor-pointer"
											href="#" data-operator="{$VALUE}">
											{\App\Language::translate($LABEL, 'Users')}
										</li>
									{/foreach}
								</ul>
							</div>
						{/if}
						{if $USER_MODEL->getRoleDetail()->get('globalsearchadv')}
							<button class="btn btn-outline-dark border-0 h-100 globalSearch"
									title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
								<span class="fa fa-th-large fa-fw"></span>
							</button>
						{/if}
					</div>
				</div>
				<div class="searchMenu d-xl-none">
					<div class="searchMenuBtn">
						<div class="quickAction">
							<a class="btn btn-light c-header__btn" href="#" role="button" aria-expanded="false"
							   aria-controls="o-search-menu__container">
								<span class="fas fa-search fa-fw"
									  title="{\App\Language::translate('LBL_SEARCH')}"></span>
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
							{if $USER_MODEL->getRoleDetail()->get('globalsearchadv')}
								<div class="input-group-append">
									<button class="btn btn-light globalSearch"
											title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
										<span class="fas fa-th-large"></span>
									</button>
								</div>
							{/if}
						</div>
						<div class="input-group mb-3 js-global-search__input o-global-search__input"
							 data-js="container">
							<input id="global-search-__value--mobile" maxlength="{$SEARCH_FIELD_MODEL->getMaxValue()}" type="text"
								   class="form-control js-global-search__value o-global-search__value"
								   title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}"
								   placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10"
								   data-js="keypress | value | autocomplete"/>
							<div class="input-group-append">
								<button class="btn btn-light searchIcon" type="button">
									<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
								</button>
							</div>
						</div>
						<div class="searchMenuBtn">
							<a class="btn btn-light c-header__btn float-right" href="#" role="button"
							   aria-expanded="false" aria-controls="o-search-menu__container">
								<span class="fas fa-times fa-fw" title="{\App\Language::translate('LBL_CLOSE')}"></span>
								<span>{\App\Language::translate('LBL_CLOSE')}</span>
							</a>
						</div>
					</div>
				</div>
			{/if}
			{if !\App\YetiForce\Shop::verify()}
				<a class="d-flex align-items-center text-warning mr-2 js-popover-tooltip" role="button" data-content="{\App\Language::translate('LBL_YETIFORCE_SHOP_PRODUCT_CANCELED', $MODULE_NAME)}<hr>{\App\YetiForce\Shop::$verifyProduct}" title="{\App\Purifier::encodeHtml('<span class="yfi yfi-shop-alert mr-1"></span>')}{\App\Language::translate('LBL_YETIFORCE_SHOP')}"
						{if $USER_MODEL->isAdminUser()}
							href="index.php?module=YetiForce&parent=Settings&view=Shop"
						{else}
							href="#"
						{/if} >
					<span class="yfi yfi-shop-alert fa-2x"></span>
				</a>
			{/if}
			{if !\App\YetiForce\Register::verify(true)}
				{if \App\Security\AdminAccess::isPermitted('Companies')}
					{assign var="INFO_REGISTRATION_ERROR" value="<a href='index.php?module=Companies&parent=Settings&view=List&displayModal=online'>{\App\Language::translate('LBL_YETIFORCE_REGISTRATION_CHECK_STATUS', $MODULE_NAME)}</a>"}
				{else}
					{assign var="INFO_REGISTRATION_ERROR" value=\App\Language::translate('LBL_YETIFORCE_REGISTRATION_CHECK_STATUS', $MODULE_NAME)}
				{/if}
				<a class="d-flex align-items-center text-center text-warning p-0 text-danger js-popover-tooltip c-header__btn" role="button"
						data-content="{\App\Language::translateArgs('LBL_YETIFORCE_REGISTRATION_ERROR', $MODULE_NAME, $INFO_REGISTRATION_ERROR)}"
						title="{\App\Purifier::encodeHtml('<span class="yfi yfi-yeti-register-alert mr-1"></span>')}{\App\Language::translate('LBL_YETIFORCE_REGISTRATION', $MODULE_NAME)}"
						{if \App\Security\AdminAccess::isPermitted('Companies')}
							href="index.php?parent=Settings&module=Companies&view=List&displayModal=online"
						{else}
							href="#"
						{/if} >
					<span class="yfi yfi-yeti-register-alert fa-2x">
					</span>
				</a>
			{/if}
		</div>
		{if !empty(\Config\Main::$headerAlertMessage)}
			<div class="alert {if empty(\Config\Main::$headerAlertType)}alert-danger{else}{\Config\Main::$headerAlertType}{/if} m-auto mb-0 px-3 py-1 text-center u-font-size-19px text-nowrap" role="alert">
				<i class="{if empty(\Config\Main::$headerAlertIcon)}fas fa-exclamation-triangle{else}{\Config\Main::$headerAlertIcon}{/if}"></i>
				<span class="font-weight-bold mx-5">{\Config\Main::$headerAlertMessage}</span>
				<i class="{if empty(\Config\Main::$headerAlertIcon)}fas fa-exclamation-triangle{else}{\Config\Main::$headerAlertIcon}{/if}"></i>
			</div>
		{/if}
		<div class="o-navbar__right ml-auto d-inline-flex flex-sm-nowrap">
			{if !Settings_ModuleManager_Library_Model::checkLibrary('roundcube')}
				{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('mailIcon')}
				{if $CONFIG['showMailIcon']=='true' && App\Privilege::isPermitted('OSSMail')}
					{assign var=AUTOLOGINUSERS value=OSSMail_Autologin_Model::getAutologinUsers()}
					{if count($AUTOLOGINUSERS) > 0}
						{assign var=MAIN_MAIL value=OSSMail_Module_Model::getDefaultMailAccount($AUTOLOGINUSERS)}
						<div class="c-header__btn__container bg-white rounded js-header__btn--mail" {if $CONFIG['showNumberUnreadEmails']=='true'}data-numberunreademails="true" data-interval="{$CONFIG['timeCheckingMail']}"{/if}>
							{if count($AUTOLOGINUSERS) eq 1}
								<a class="c-header__btn btn btn-outline-dark border-0 h-100" title="{$MAIN_MAIL.username}" href="index.php?module=OSSMail&view=Index">
									<div class="d-none d-xxl-block">
										{if !empty($ITEM.username)}{$ITEM.username}{/if}
										<span class="mail_user_name">{$MAIN_MAIL.username}</span>
										<span data-id="{$MAIN_MAIL.rcuser_id}" class="noMails"></span>
									</div>
									<div class="d-xxl-none">
										<span class="fas fa-inbox fa-fw" title="{\App\Language::translate('LBL_EMAIL')}"></span>
										<span data-id="{$MAIN_MAIL.rcuser_id}" class="noMails"></span>
									</div>
								</a>
							{else}
								<div class="d-none d-xxl-block">
									<select id="mail-select" class="form-control-sm" title="{\App\Language::translate('LBL_SEARCH_MODULE', $MODULE_NAME)}">
										{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
											<option value="{$KEY}" {if $ITEM.active}selected{/if} data-id="{$KEY}" data-nomail="" class="noMails">
												{$ITEM.username}
											</option>
										{/foreach}
									</select>
								</div>
								<div class="o-action-menu__item d-xxl-none dropdown">
									<a class="c-header__btn btn btn-outline-dark border-0 dropdown-toggle" id="show-mail-list" data-toggle="dropdown" data-boundary="window" href="#" role="button" aria-haspopup="true" aria-expanded="false">
										<span class="fas fa-inbox fa-fw" title="{\App\Language::translate('LBL_EMAIL')}"></span>
									</a>
									<ul class="dropdown-menu js-mail-list" aria-labelledby="show-mail-list" role="list" data-js="click">
										{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
											<li value="{$KEY}" data-nomail="" class="dropdown-item js-mail-link px-2" data-js="click">
												<div class="d-flex w-100">
													<span class="mr-2">{$ITEM.username}</span>
													<span data-id="{$KEY}" class="noMails ml-auto"></span>
												</div>
											</li>
										{/foreach}
									</ul>
								</div>
							{/if}
						</div>
					{/if}
				{/if}
			{/if}
			{if $PARENT_MODULE === 'Settings'}
				<div class="mr-xxl-4 d-flex flex-sm-nowrap ml-4">
					<a class="btn btn-light c-header__btn ml-2" title="YetiForce Documentation" role="button" href="https://doc.yetiforce.com" target="_blank" rel="noreferrer noopener">
						<span class="mdi mdi-book-open-page-variant"></span>
					</a>
					<a class="btn btn-light c-header__btn ml-2" title="{\App\Language::translate('LBL_YETIFORCE_ASSISTANCE', $QUALIFIED_MODULE)}" role="button" href="index.php?module=YetiForce&parent=Settings&view=Shop&category=Support" target="_blank">
						<span class="far fa-life-ring fa-fw"></span>
					</a>
					<a class="btn btn-light c-header__btn ml-2" title="{\App\Language::translate('LBL_YETIFORCE_ISSUES', $QUALIFIED_MODULE)}" role="button" href="https://github.com/YetiForceCompany/YetiForceCRM/issues" target="_blank" rel="noreferrer noopener">
						<span class="fas fa-bug fa-fw"></span>
					</a>
					<a class="btn btn-light c-header__btn ml-2" title="YetiForceCRM" role="button" href="#" data-toggle="modal" data-target="#yetiforceDetails">
						<span class="fas fa-info-circle fa-fw"></span>
					</a>
				</div>
			{/if}
			{if \App\Privilege::isPermitted('Chat')}
				<div class="ml-2 quasar-reset">
					<div id="ChatModalVue"></div>
				</div>
			{/if}
			<nav class="actionMenu" aria-label="{\App\Language::translate("QUICK_ACCESS_MENU")}">
				<a class="btn btn-light c-header__btn ml-2 c-header__btn--mobile js-quick-action-btn" href="#"
				   data-js="click" role="button" aria-expanded="false" aria-controls="o-action-menu__container">
					<span class="fas fa-ellipsis-h fa-fw" title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
				</a>
				<div class="o-action-menu__container d-flex flex-md-nowrap flex-column flex-md-row" id="o-action-menu__container">
					{assign var=QUICKCREATE_MODULES_PARENT value=Vtiger_Module_Model::getQuickCreateModules(true, true)}
					{if !empty($QUICKCREATE_MODULES_PARENT)}
						<div class="o-action-menu__item commonActionsContainer">
							<a class="c-header__btn ml-2 btn-light btn js-popover-tooltip dropdownMenu" role="button"
							   data-js="popover" data-toggle="modal" data-target="#quickCreateModules"
							   data-placement="bottom" data-content="{\App\Language::translate('LBL_QUICK_CREATE')}"
							   href="#">
								<span class="fas fa-plus fa-fw"
									  title="{\App\Language::translate('LBL_QUICK_CREATE')}"></span>
								<span class="c-header__label--sm-down"> {\App\Language::translate('LBL_QUICK_CREATE')}</span>
							</a>
						</div>
					{/if}
					{if !$IS_IE && \App\Privilege::isPermitted('KnowledgeBase')}
						<div class="o-action-menu__item">
							<a class="c-header__btn ml-2 btn-light btn js-popover-tooltip js-knowledge-base-modal"
							   role="button"
							   data-js="popover|modal" data-content="{\App\Language::translate('BTN_KNOWLEDGE_BASE', 'KnowledgeBase')}"
							   href="#">
								<span class="yfm-KnowledgeBase"
									  title="{\App\Language::translate('BTN_KNOWLEDGE_BASE', 'KnowledgeBase')}"></span>
								<span class="c-header__label--sm-down"> {\App\Language::translate('BTN_KNOWLEDGE_BASE', 'KnowledgeBase')}</span>
							</a>
							<div id="KnowledgeBaseModal"></div>
						</div>
					{/if}
					{if \App\Privilege::isPermitted('Notification', 'DetailView')}
						<div class="o-action-menu__item">
							<a class="c-header__btn ml-2 btn btn-light btn isBadge text-nowrap notificationsNotice js-popover-tooltip {if App\Config::module('Notification', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}"
							   role="button" data-js="popover"
							   data-content="{\App\Language::translate('LBL_NOTIFICATIONS')}" href="#">
								<span class="fas fa-bell fa-fw"
									  title="{\App\Language::translate('LBL_NOTIFICATIONS')}"> </span>
								<span class="badge badge-dark d-none mr-1">0</span>
								<span class="c-header__label--sm-down"> {\App\Language::translate('LBL_NOTIFICATIONS')}</span>
							</a>
						</div>
					{/if}
					{if $REMINDER_ACTIVE}
						<div class="o-action-menu__item">
							<a class="c-header__btn ml-2 btn btn-light btn isBadge text-nowrap remindersNotice js-popover-tooltip {if App\Config::module('Calendar', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}"
							   data-js="popover" role="button" data-content="{\App\Language::translate('LBL_REMINDER')}"
							   href="#">
							<span class="fas fa-calendar fa-fw"
								  title="{\App\Language::translate('LBL_REMINDER')}"></span>
								<span class="badge badge-danger d-none mr-1">0</span>
								<span class="c-header__label--sm-down">{\App\Language::translate('LBL_REMINDER')}</span>
							</a>
						</div>
					{/if}
					{if App\Config::performance('BROWSING_HISTORY_WORKING')}
						<div class="o-action-menu__item">
							<div class="dropdown">
								<a class="c-header__btn ml-2 btn btn-light btn js-popover-tooltip dropdownMenu"
								   id="showHistoryBtn" data-js="popover" data-toggle="dropdown" data-boundary="window"
								   data-content="{\App\Language::translate('LBL_PAGES_HISTORY')}" href="#"
								   role="button">
								<span class="fas fa-history fa-fw"
									  title="{\App\Language::translate('LBL_PAGES_HISTORY')}"></span>
									<span class="c-header__label--sm-down">{\App\Language::translate('LBL_PAGES_HISTORY')}</span>
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
								<a class="c-header__btn ml-2 btn btn js-popover-tooltip {if $obj->getClassName()|strrpos:"btn-" === false}btn-light {$obj->getClassName()}{else}{$obj->getClassName()}{/if} {if !empty($CHILD_LINKS)}dropdownMenu{/if}"
								   role="button" data-js="popover" data-content="{\App\Language::translate($TITLE)}"
								   data-placement="bottom"
								   href="{$HREF}"
										{if isset($obj->linkdata) && $obj->linkdata && is_array($obj->linkdata)}
									{foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
										data-{$DATA_NAME}="{$DATA_VALUE}"
									{/foreach}
										{/if}>
									{if $ICON}
										<span class="{$ICON}" title="{\App\Language::translate($TITLE,$MODULE)}"></span>
										<span class="c-header__label--sm-down">{\App\Language::translate($TITLE,$MODULE)}</span>
									{/if}
									{if $ICON_PATH}
										<img src="{$ICON_PATH}" alt="{\App\Language::translate($TITLE,$MODULE)}"
											 title="{\App\Language::translate($TITLE,$MODULE)}"/>
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
					<div class="o-action-menu__item">
						<div class="dropdown">
							<a class="c-header__btn ml-2 btn dropdown-toggle js-popover-tooltip dropdownMenu {if $CURRENT_USER->getId() != App\User::getCurrentUserRealId()}btn-info{else}btn-light{/if}"
								id="showUserQuickMenuBtn" data-js="popover" data-toggle="dropdown" data-boundary="window"
								data-content="{\App\Language::translate('LBL_MY_PREFERENCES')}" href="#" role="button">
								{assign var="IMAGE" value=$CURRENT_USER->getImage()}
								{if $IMAGE}
									<img src="{$IMAGE['url']}" alt="{$CURRENT_USER->getName()}" title="{$CURRENT_USER->getName()}" class="c-user-avatar-small">
									<span class="c-header__label--sm-down ml-2">{\App\Language::translate('LBL_MY_PREFERENCES')}</span>
								{else}
									<span class="fas fa-user fa-fw" title="{\App\Language::translate('LBL_MY_PREFERENCES')}"></span>
									<span class="c-header__label--sm-down">{\App\Language::translate('LBL_MY_PREFERENCES')}</span>
								{/if}
							</a>
							{include file=\App\Layout::getTemplatePath('UserQuickMenu.tpl', $MODULE)}
						</div>
					</div>
				</div>
			</nav>
		</div>
	</header>
	{if !empty($QUICKCREATE_MODULES_PARENT)}
		{include file=\App\Layout::getTemplatePath('QuickCreateModal.tpl')}
	{/if}
	<!-- /tpl-Base-BodyHeader -->
{/strip}
