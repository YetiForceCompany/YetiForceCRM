{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<nav class="contents-bottomscroll" role="navigation">
		<ul class="nav modulesList">
			{assign var=PRIVILEGESMODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
			{assign var=TABINDEX value=0}
			{foreach key=KEY item=MENU from=$MENUS}
				{assign var=TABINDEX value=$TABINDEX+1}
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
				{include file='menu/'|cat:$MENU.type|cat:'.tpl'|@vtemplate_path:$MODULE}
			{/foreach}
		</ul>
	</nav>
{/strip}
