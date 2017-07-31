{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
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
			{include file='menu/'|cat:$MENU.type|cat:'.tpl'|@vtemplate_path:$MODULE}
		</ul>
	{/foreach}
{/strip}
