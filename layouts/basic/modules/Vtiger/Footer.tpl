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
</div>
</div>
</div>
{if $SHOW_FOOTER}
	<div class="clearfix"></div>
	<input id="activityReminder" class="hide noprint" type="hidden" value="{$ACTIVITY_REMINDER}" />
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
	<footer class="footerContainer fixed-bottom">
		<div class="container-fluid px-1">
			<ul class="float-left pagination border-0">
				<li class="page-item">
					<a class="page-link" href="https://www.linkedin.com/groups/8177576" rel="noreferrer">
						<span class="fab fa-linkedin fa-2x" title="Linkedin"></span>
					</a>
				</li>
				<li class="page-item">
					<a class="page-link" href="https://twitter.com/YetiForceEN" rel="noreferrer">
						<span class="fab fa-twitter-square fa-2x" title="Twitter"></span>
					</a>
				</li>
				<li class="page-item">
					<a class="page-link" href="https://www.facebook.com/YetiForce-CRM-158646854306054/" rel="noreferrer">
						<span class="fab fa-facebook-square fa-2x" title="Facebook"></span>
					</a>
				</li>
				<li class="page-item">
					<a class="page-link" href="https://github.com/YetiForceCompany/YetiForceCRM" rel="noreferrer">
						<span class="fab fa-github-square fa-2x" title="Github"></span>
					</a>
				</li>
			</ul>
			<div class="float-right p-1">
				<a data-toggle="modal" data-target="#yetiforceDetails">
					<img class="logoFooter" src="{App\Layout::getPublicUrl('layouts/resources/Logo/white_logo_yetiforce.png')}" alt="YetiForceCRM" />
				</a>
			</div>
			<div class="mx-auto w-75 pt-1">
				{assign var=SCRIPT_TIME value=round(microtime(true) - \App\Config::$startTime, 3)}
				{if $USER_MODEL->isAdminUser()}
					{assign var=FOOTVR value= '[ver. '|cat:$YETIFORCE_VERSION|cat:'] ['|cat:\App\Language::translate('WEBLOADTIME')|cat:': '|cat:$SCRIPT_TIME|cat:'s.]'}
					{assign var=FOOTVRM value= '['|cat:$SCRIPT_TIME|cat:'s.]'}
					{assign var=FOOTOSP value= '<u><a href="index.php?module=Home&view=Credits&parent=Settings">open source project</a></u>'}
					<p class="text-center">
						<span class="d-none d-sm-inline ">{\App\Language::translateArgs('LBL_FOOTER_CONTENT', 'Vtiger', $FOOTVR ,$FOOTOSP)}</span>
						<span class="d-inline d-sm-none text-center">{\App\Language::translateArgs('LBL_FOOTER_CONTENT', 'Vtiger', $FOOTVRM ,$FOOTOSP)}</span>
					</p>
				{else}
					<p class="text-center">{\App\Language::translateArgs('LBL_FOOTER_CONTENT', 'Vtiger', '['|cat:\App\Language::translate('WEBLOADTIME')|cat:': '|cat:$SCRIPT_TIME|cat:'s.]', 'open source project')}</p>
				{/if}
			</div>
	</footer>
	<div class="modal fade" id="yetiforceDetails" tabindex="-1" role="dialog" aria-labelledby="yetiforceDetails">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">YetiForceCRM {if $USER_MODEL->isAdminUser()}v{$YETIFORCE_VERSION}{/if} - The best open system in the world</h4>
				</div>
				<div class="modal-body">
					<p class="text-center"><img  src="{App\Layout::getPublicUrl('layouts/resources/Logo/blue_yetiforce_logo.png')}" title="YetiForceCRM" alt="YetiForceCRM" style="height: 120px;" /></p>
					<p>Copyright © YetiForce.com All rights reserved.</p>
					<p>The Program is provided AS IS, without warranty. Licensed under <a href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt" target="_blank"><strong>YetiForce Public License 3.0</strong></a>.</p>
					<p>YetiForce is based on two systems - <strong>VtigerCRM</strong> and <strong>SugarCRM</strong>.<br /><br /></p>
					<p><span class="badge badge-secondary">License:</span> <a href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt" target="_blank"><strong>YetiForce Public License 3.0</strong></a></p>
					<p><span class="badge label-primary">WWW:</span> <a href="https://yetiforce.com" target="_blank" rel="noreferrer"><strong>https://yetiforce.com</strong></a></p>
					<p><span class="badge badge-success">Code:</span> <a href="https://github.com/YetiForceCompany/YetiForceCRM" target="_blank" rel="noreferrer"><strong>https://github.com/YetiForceCompany/YetiForceCRM</strong></a></p>
					<p><span class="badge label-info">Documentation:</span> <a href="https://yetiforce.com/en/knowledge-base/documentation" target="_blank" rel="noreferrer"><strong>https://yetiforce.com/en/documentation.html</strong></a></p>
					<p><span class="badge badge-warning">Issues:</span> <a href="https://github.com/YetiForceCompany/YetiForceCRM/issues" target="_blank" rel="noreferrer"><strong>https://github.com/YetiForceCompany/YetiForceCRM/issues</strong></a></p>
					<p class="text-center">
						<a class="yetiforceDetailsLink" rel="noreferrer" href="https://www.linkedin.com/groups/8177576">
							<span class="fab fa-linkedin" title="LinkendIn"></span>
						</a>
						<a class="yetiforceDetailsLink" rel="noreferrer" href="https://twitter.com/YetiForceEN">
							<span class="fab fa-twitter-square" title="Twitter"></span>
						</a>
						<a class="yetiforceDetailsLink" rel="noreferrer" href="https://www.facebook.com/YetiForce-CRM-158646854306054/">
							<span class="fab fa-facebook-square" title="Facebook"></span>
						</a>
						<a class="yetiforceDetailsLink" rel="noreferrer" href="https://github.com/YetiForceCompany/YetiForceCRM">
							<span class="fab fa-github-square" title="Github"></span>
						</a>
					</p>
				</div>
				<div class="modal-footer">
					<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
{/if}
{* javascript files *}
{include file=\App\Layout::getTemplatePath('JSResources.tpl')}
{if \App\Debuger::isDebugBar()}
	{\App\Debuger::getDebugBar()->getJavascriptRenderer()->render()}
{/if}
</body>
</html>
{/strip}
