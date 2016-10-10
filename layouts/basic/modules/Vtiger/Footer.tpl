{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
	<input id="activityReminder" class="hide noprint" type="hidden" value="{$ACTIVITY_REMINDER}"/>
	{if AppConfig::module('Users', 'IS_VISIBLE_USER_INFO_FOOTER')}
		<div class="infoUser">
			{$USER_MODEL->getName()}&nbsp;(
			{$USER_MODEL->get('email1')}&nbsp;
			{if !empty({$USER_MODEL->get('phone_crm_extension')})} 
				,&nbsp; {$USER_MODEL->get('phone_crm_extension')}
			{/if}
			)
		</div>
	{/if}
	<footer class="footerContainer navbar-default navbar-fixed-bottom noprint">
		<div class="vtFooter">
			{if $USER_MODEL->is_admin == 'on'}
				<div class="pull-left">
					<a class="iconsInFooter" href="https://www.linkedin.com/groups/8177576">
						<span class="AdditionalIcon-Linkedin" title="Linkendin"/>
					</a>
					<a class="iconsInFooter" href="https://twitter.com/YetiForceEN">
						<span class="AdditionalIcon-Twitter" title="Twitter"/>
					</a>
					<a class="iconsInFooter" href="https://www.facebook.com/YetiForce-CRM-158646854306054/">
						<span class="AdditionalIcon-Facebook" title="Facebook"/>
					</a>
				</div>
			{/if}
			<div class="pull-right">
				{if $USER_MODEL->is_admin == 'on'}
					<a class="iconsInFooter" href="https://github.com/YetiForceCompany/YetiForceCRM">
						<span class="AdditionalIcon-Github" title="Github"/>
					</a>
				{/if}
				{if AppConfig::main('isVisibleLogoInFooter')}
					{assign var=ADDRESS value='https://yetiforce.com'}
					<a href='{$ADDRESS}'>
						<img class="logoFooter" src="storage/Logo/white_logo_yetiforce.png" title="{$COMPANY_LOGO->get('title')}" alt="{$COMPANY_LOGO->get('alt')}"/>
					</a>
				{/if}
			</div>
			{assign var=SCRIPT_TIME value=round(microtime(true) - vglobal('startTime'), 3)}
			{if $USER_MODEL->is_admin == 'on'}
				{assign var=FOOTVR value= '[ver. '|cat:$YETIFORCE_VERSION|cat:'] ['|cat:vtranslate('WEBLOADTIME')|cat:': '|cat:$SCRIPT_TIME|cat:'s.]'}
				{assign var=FOOTVRM value= '['|cat:$SCRIPT_TIME|cat:'s.]'}
				{assign var=FOOTOSP value= '<u><a href="index.php?module=Home&view=Credits&parent=Settings">open source project</a></u>'}
				<p class="hidden-xs">{sprintf( vtranslate('LBL_FOOTER_CONTENT') , $FOOTVR ,$FOOTOSP)}</p>
				<p class="visible-xs-block">{sprintf( vtranslate('LBL_FOOTER_CONTENT') , $FOOTVRM ,$FOOTOSP)}</p>
			{else}
				<p>{sprintf( vtranslate('LBL_FOOTER_CONTENT') , '['|cat:vtranslate('WEBLOADTIME')|cat:': '|cat:$SCRIPT_TIME|cat:'s.]', 'open source project' )}</p>
			{/if}
		</div>
	</footer>
	{* javascript files *}
	{include file='JSResources.tpl'|@vtemplate_path}
</div>
{if AppConfig::debug('DISPLAY_DEBUG_CONSOLE')}
	{\includes\Debuger::getDebugBar()->getJavascriptRenderer()->render()}
{/if}
</body>
</html>
{/strip}
