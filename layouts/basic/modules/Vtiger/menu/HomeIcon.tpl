{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if $MODULE eq 'Home'}
		{assign var=ACTIVE value='true'}
	{else}
		{assign var=ACTIVE value='false'}
	{/if}
	<li class="tpl-menu-HomeIcon c-menu__item js-menu__item nav-item menuHomeIcon {if !$HASCHILDS}hasParentMenu{/if}"
		data-id="{$MENU['id']}" data-js="mouseenter mouseleave">
		<a class="nav-link {if $ACTIVE=='true'} active{else} collapsed{/if} hasIcon{if $HASCHILDS == 'true'} js-submenu-toggler is-submenu-toggler{/if}"
			{if $HASCHILDS == 'true'} data-toggle="collapse" data-target="#submenu-{$MENU['id']}" role="button" {/if}
			href="{$HOME_MODULE_MODEL->getDefaultUrl()}" {if $HASCHILDS == 'true'} aria-haspopup="true"
			aria-expanded="{$ACTIVE}" aria-controls="submenu-{$MENU['id']}" {/if}>
			<span class="c-menu__item__icon fas fa-home" aria-hidden="true"></span>
			<span class="c-menu__item__text js-menu__item__text" title="{$MENU['name']}"
				data-js="class: u-white-space-n">{\App\Language::translate('LBL_HOME',$MENU_MODULE)}</span>
			{if $HASCHILDS == 'true'}<span class="toggler" aria-hidden="true"><span class="fas fa-plus-circle"></span><span
					class="fas fa-minus-circle"></span></span>{/if}
		</a>
		{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
	</li>
{/strip}
