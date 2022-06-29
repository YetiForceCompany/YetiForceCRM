{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, $MENU['name'])}
	{assign var=ACTIVE value=isset($MID) && $MENU['id'] eq $MID}
	<li class="tpl-menu-CustomFilter c-menu__item js-menu__item nav-item menuCustomFilter .modCT_{$MENU.mod}"
		data-id="{$MENU['id']}" data-js="mouseenter mouseleave">
		<a class="nav-link{if $ACTIVE} active{/if}{if $HASCHILDS=='true'} collapsed{/if}{if $ICON} hasIcon{/if}{if isset($MENU['hotkey'])} hotKey{/if}{if $HASCHILDS == 'true'} js-submenu-toggler is-submenu-toggler{/if}"
			{if isset($MENU['hotkey'])} data-hotkeys="{$MENU['hotkey']}" {/if} {if $HASCHILDS == 'true'}
		data-toggle="collapse" data-target="#submenu-{$MENU['id']}" role="button" {/if} href="{$MENU['dataurl']}"
		rel="noreferrer noopener" {if $HASCHILDS == 'true'} aria-haspopup="true" aria-expanded="false"
			aria-controls="submenu-{$MENU['id']}" {/if} {if $MENU['newwindow'] eq 1}target="_blank" {/if}>
			{$ICON}
			<span class="c-menu__item__text js-menu__item__text" title="{$MENU['name']}"
				data-js="class: u-white-space-n">{$MENU['name']}</span>
			{if !empty($MENU['countentries'])}<span class="count badge badge-danger c-badge--md ml-1 js-count" data-js="container"></span>{/if}
			{if $HASCHILDS == 'true'}
				<span class="toggler" aria-hidden="true"><span class="fas fa-plus-circle"></span><span
						class="fas fa-minus-circle"></span></span>
			{/if}
		</a>
		{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
	</li>
{/strip}
