{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=PRIVILEGESMODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
	{assign var=TABINDEX value=0}
	{foreach key=KEY item=MENU from=$MENUS}
		{assign var=TABINDEX value=$TABINDEX+1}
		{if isset($MENU['childs']) && $MENU['childs']|@count neq 0}
			{assign var=HASPOPUP value='true'}
			{assign var=CLASS value='dropdown'}
		{else}
			{assign var=CLASS value=''}
			{assign var=HASPOPUP value='false'}
		{/if}
		<ul class="nav modulesList  navbar-nav navbar-left">
			{include file=\App\Layout::getTemplatePath('menu/'|cat:$MENU.type|cat:'.tpl', $MODULE)}
		</ul>
	{/foreach}
{/strip}
