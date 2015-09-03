{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
{assign var=MENUS value=$MENU.childs}
{if $MENUS|@count neq 0}
	<ul class="nav nav-pills nav-stacked collapse {if $PARENT_MODULE == $MENU.id}in{/if}" id="menu{$MENU.id}" role="menu" aria-hidden="true">
		{assign var=TABINDEX value=$TABINDEX-1}
		{foreach key=KEY item=MENU from=$MENUS}
			{*{assign var=CHILDS value=$MENU.childs}
			{if $CHILDS|@count neq 0}
				{assign var=HASPOPUP value='true'}
				{assign var=CLASS value='dropdown'}
			{else}
				{assign var=HASPOPUP value='false'}
				{assign var=CLASS value=''}
			{/if}*}
			{include file='menu/'|cat:$MENU.type|cat:'.tpl'|@vtemplate_path:$MODULE}
		{/foreach}
	</ul>
{/if}
