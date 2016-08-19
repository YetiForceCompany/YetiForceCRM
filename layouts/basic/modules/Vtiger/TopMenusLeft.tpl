{*/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/*}
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
