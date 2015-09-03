{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<nav class="" role="navigation">
		{assign var=PRIVILEGESMODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
		{assign var=TABINDEX value=0}
		{foreach key=KEY item=MENU from=$MENUS}
			{assign var=TABINDEX value=$TABINDEX+1}
			{assign var=CHILDS value=$MENU.childs}

			{if $CHILDS|@count neq 0}
				{assign var=HASPOPUP value='true'}
				{assign var=CLASS value='dropdown'}
			{else}
				{assign var=CLASS value=''}
				{assign var=HASPOPUP value='false'}
			{/if}
			<ul class="nav nav-pills nav-stacked modulesList">
				{include file='menu/'|cat:$MENU.type|cat:'.tpl'|@vtemplate_path:$MODULE}
			</ul>
		{/foreach}
	</nav>
{/strip}
