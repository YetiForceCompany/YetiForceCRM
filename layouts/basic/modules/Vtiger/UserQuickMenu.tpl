{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-UserQuickMenu -->
	<div class="dropdown-menu historyList p-0 u-max-w-sm-100 u-min-w-300pxr" aria-labelledby="showHistoryBtn" role="list" data-js="perfectscrollbar">
		<div class="user-info-body container-fluid m-0 pl-2 pr-2 pt-2">
			<div class="user-info row w-100 m-0 p-0">
				<div class="col-12 p-1">
					<div class="user-photo mr-2 float-left">
						{assign var="IMAGE" value=$CURRENT_USER->getImage()}
						{if $IMAGE}
							<img src="{$IMAGE['url']}" alt="{$CURRENT_USER->getName()}" title="{$CURRENT_USER->getName()}" class="c-user-avatar-medium">
						{else}
							<span class="o-detail__icon js-detail__icon yfm-Users"></span>
						{/if}
					</div>
					<div class="user-detail">
						<h6 class="mb-0 pb-0 u-text-ellipsis">{$CURRENT_USER->getDetail('first_name')} {$CURRENT_USER->getDetail('last_name')}</h6>
						<span class="u-fs-xs text-gray">{$CURRENT_USER->get('roleName')}</span>
					</div>
				</div>
			</div>
		</div>
		<div class="user-links container-fluid d-block mt-2 p-0 u-max-w-xsm-100">
			{foreach item="MENU_ELEMENT" from=$USER_QUICK_MENU_LINKS}
				{assign var="HREF" value='#'}
				{assign var="LINK" value=$MENU_ELEMENT->convertToNativeLink()}
				{assign var="ICON" value=$MENU_ELEMENT->getHeaderIcon()}
				{assign var="TITLE" value=$MENU_ELEMENT->getLabel()}
				{assign var="LINK_TYPE" value=$MENU_ELEMENT->getType()}
				{if !empty($LINK)}
					{assign var="HREF" value=$LINK}
				{/if}
				{if $LINK_TYPE === 'SEPARATOR'}
					<div class="dropdown-divider {$MENU_ELEMENT->getClassName()}"></div>
				{else if $LINK_TYPE === 'GROUPNAME'}
					<div class="user-menu-element row p-0 m-0">
						<div class="col-12 pt-1 pb-1 bg-light border border-light">
							<span class="text-uppercase font-weight-bold text-dark u-fs-sm">{\App\Language::translate($TITLE, 'Users')}</span>
						</div>
					</div>
				{else}
					<div class="user-menu-element row">
						<div class="col-12 u-bg-light-darken">
							<a class="text-decoration-none u-fs-sm text-secondary pt-2 pb-2 {$MENU_ELEMENT->getClassName()}"
								href="{$HREF}"
								{if isset($MENU_ELEMENT->linkdata) && $MENU_ELEMENT->linkdata && is_array($MENU_ELEMENT->linkdata)}
									{foreach item=DATA_VALUE key=DATA_NAME from=$MENU_ELEMENT->linkdata}
										data-{$DATA_NAME}="{$DATA_VALUE}"
									{/foreach}
								{/if}>
								{if $ICON}
									<span class="{$ICON}" title="{\App\Language::translate($TITLE, 'Users')}"></span>
									<span class="ml-2">{\App\Language::translate($TITLE, 'Users')}</span>
								{else}
									<span>{\App\Language::translate($TITLE, 'Users')}</span>
								{/if}
							</a>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
	<!-- /tpl-Base-UserQuickMenu -->
{/strip}
