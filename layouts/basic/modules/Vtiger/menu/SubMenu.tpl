{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if isset($MENU['childs']) && $MENU['childs']|@count neq 0}
		{assign var=MENUS value=$MENU['childs']}
		<ul class="tpl-menu-SubMenu nav subMenu {if (isset($MENU['active']) && $MENU['active']) || $PARENT_MODULE == $MENU['id']}in{/if}" role="menu" aria-hidden="true">
			{assign var=TABINDEX value=$TABINDEX-1}
			{foreach key=KEY item=MENU from=$MENUS}
				{assign var=MENU_MODULE value='Menu'}
				{if isset($MENU['moduleName'])}
					{assign var=MENU_MODULE value=$MENU['moduleName']}
				{/if}
				{if isset($MENU['childs']) && $MENU['childs']|@count neq 0}
					{assign var=HASCHILDS value='true'}
				{else}
					{assign var=HASCHILDS value='false'}
				{/if}
				{include file=\App\Layout::getTemplatePath('menu/'|cat:$MENU.type|cat:'.tpl', $MODULE)}
			{/foreach}
		</ul>
	{/if}
{/strip}
