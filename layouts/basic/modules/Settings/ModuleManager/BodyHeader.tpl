{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<!-- tpl-Base-BodyHeader -->
	{assign var='count' value=0}
	<header class="navbar navbar-expand-md navbar-dark fixed-top px-2 js-header c-header"
		data-js="height">
		<div class="o-navbar__left d-inline-flex">
			<div class="rightHeaderBtnMenu">
				<div class="quickAction">
					<a class="btn btn-light c-header__btn ml-0 js-sidebar-btn" role="button" href="#" data-js="click"
						aria-haspopup="true" aria-expanded="false">
						<span class="fas fa-bars fa-fw" title="{\App\Language::translate('LBL_MENU')}"></span>
					</a>
				</div>
			</div>
			{assign var=VERIFY value=\App\YetiForce\Shop::verify()}
			{if $VERIFY}
				<a class="d-flex align-items-center text-warning mr-2 js-popover-tooltip" data-content="{$VERIFY}" aria-label="{\App\Language::translate('LBL_YETIFORCE_SHOP')}"
					{if $USER_MODEL->isAdminUser()} href="index.php?module=YetiForce&parent=Settings&view=Shop" {else} href="#" {/if}>
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
					{/if}>
					<span class="yfi yfi-yeti-register-alert fa-2x">
					</span>
				</a>
			{/if}
		</div>
		<div class="o-navbar__right ml-auto d-inline-flex flex-sm-nowrap">
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
					<a class="btn btn-light c-header__btn ml-2 js-show-modal" title="YetiForceCRM" role="button" data-url="index.php?module=AppComponents&view=YetiForceDetailModal" data-js="click">
						<span class="fas fa-info-circle fa-fw"></span>
					</a>
				</div>
			{/if}
			<nav class="actionMenu" aria-label="{\App\Language::translate("QUICK_ACCESS_MENU")}">
				<a class="btn btn-light c-header__btn ml-2 c-header__btn--mobile js-quick-action-btn" href="#"
					data-js="click" role="button" aria-expanded="false" aria-controls="o-action-menu__container">
					<span class="fas fa-ellipsis-h fa-fw" title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
				</a>
				<div class="o-action-menu__container d-flex flex-md-nowrap flex-column flex-md-row" id="o-action-menu__container">
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
								<a class="c-header__btn ml-2 btn btn js-popover-tooltip {if $obj->getClassName()|strrpos:"btn-" === false}btn-light {$obj->getClassName()}{else}{$obj->getClassName()}{/if} {if !empty($CHILD_LINKS)}dropdownMenu{/if}" href="{$HREF}" data-placement="bottom"
									role="button" data-js="popover" data-content="{\App\Language::translate($TITLE)}"
									{if isset($obj->linkdata) && $obj->linkdata && is_array($obj->linkdata)}
										{foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
											data-{$DATA_NAME}="{$DATA_VALUE}"
										{/foreach}
									{/if}>
									{if $ICON}
										<span class="{$ICON}" title="{\App\Language::translate($TITLE)}"></span>
										<span class="c-header__label--sm-down">{\App\Language::translate($TITLE)}</span>
									{/if}
									{if $ICON_PATH}
										<img src="{$ICON_PATH}" alt="{\App\Language::translate($TITLE)}" title="{\App\Language::translate($TITLE)}" />
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
													<a class="dropdown-item" href="{$href}" target="{$obj->target}" {$onclick}
														id="menubar_item_right_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($label)}"
														{if $obj->linkdata && is_array($obj->linkdata)}
															{foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}data-{$DATA_NAME}="{$DATA_VALUE}" {/foreach}
														{/if}>
														{\App\Language::translate($label)}
													</a>
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
	<!-- /tpl-Base-BodyHeader -->
{/strip}
