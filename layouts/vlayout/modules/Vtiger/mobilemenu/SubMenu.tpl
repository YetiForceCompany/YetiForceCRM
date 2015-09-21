{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
{assign var=MENUS value=$MENU.childs}
{if $MENUS|@count neq 0}
		{assign var=TABINDEX value=$TABINDEX-1}
		{foreach key=KEY item=MENU from=$MENUS}
			{assign var=CHILDS value=$MENU['childs']}
			{assign var=MENU_MODULE value='Menu'}
			{if isset($MENU['moduleName'])}
				{assign var=MENU_MODULE value=$MENU['moduleName']}
			{/if}
				
			{if $CHILDS|@count neq 0}
				{assign var=HASCHILDS value='true'}
			{else}
				{assign var=HASCHILDS value='false'}
			{/if}
			{include file='mobilemenu/'|cat:$MENU.type|cat:'.tpl'|@vtemplate_path:$MODULE}
		{/foreach}
	
{/if}
