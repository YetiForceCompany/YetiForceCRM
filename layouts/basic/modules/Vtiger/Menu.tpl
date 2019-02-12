{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<nav class="tpl-Menu js-menu__content c-menu__content" id="submenu-0" data-js="container" aria-label="{\App\Language::translate("LBL_MAIN_MENU")}" data-js="keydown | focus">
		<ul class="nav flex-column modulesList">
			{assign var=PRIVILEGESMODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
			{assign var=TABINDEX value=0}
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
	</nav>
{/strip}
