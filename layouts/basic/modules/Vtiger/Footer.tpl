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
	<input id="activityReminder" class="d-none noprint" type="hidden" value="{$ACTIVITY_REMINDER}" />
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
		<footer class="footerContainer fixed-bottom js-footer" data-js="height">
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
						<a class="page-link" href="https://www.facebook.com/YetiForce-CRM-158646854306054/"
						   rel="noreferrer">
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
					<ul class="pagination">
						<li class="page-item">
							<a class="page-link-right mr-2" href="https://yetiforce.shop" rel="noreferrer">
								<span class="fas fa-shopping-cart fa-2x" title="yetiforce.shop"></span>
							</a>
						</li>
						<li class="page-item u-cursor-pointer">
							<a data-toggle="modal" role="button" data-target="#yetiforceDetails">
								<span class="fas fa-info-circle fa-2x" title="YetiForceCRM"></span>
							</a>
						</li>
					</ul>
				</div>
				<div class="mx-auto w-75">
					{assign var=SCRIPT_TIME value=round(microtime(true) - \App\Config::$startTime, 3)}
					{if $USER_MODEL->isAdminUser()}
						{assign var=FOOTVR value= '[ver. '|cat:$YETIFORCE_VERSION|cat:'] ['|cat:\App\Language::translate('WEBLOADTIME')|cat:': '|cat:$SCRIPT_TIME|cat:'s.]'}
						{assign var=FOOTVRM value= '['|cat:$SCRIPT_TIME|cat:'s.]'}
						{assign var=FOOTOSP value= '<em><a class="u-text-underline" href="index.php?module=Vtiger&view=Credits&parent=Settings">open source project</a></em>'}
						<p class="text-center">
							<span class="d-none d-sm-inline ">Copyright &copy; YetiForce.com All rights reserved. {$FOOTVR}
								<br/>{\App\Language::translateArgs('LBL_FOOTER_CONTENT', 'Vtiger',$FOOTOSP)}</span>
							<span class="d-inline d-sm-none text-center">{\App\Language::translateArgs('LBL_FOOTER_CONTENT', 'Vtiger', $FOOTVRM ,$FOOTOSP)}</span>
						</p>
					{else}
						<p class="text-center">Copyright &copy; YetiForce.com All rights reserved.
							[{\App\Language::translate('WEBLOADTIME')}: {$SCRIPT_TIME}
							s.]<br/>{\App\Language::translateArgs('LBL_FOOTER_CONTENT', 'Vtiger', 'open source project')}
						</p>
					{/if}
				</div>
		</footer>
		<div class="modal fade" id="yetiforceDetails" tabindex="-1" role="dialog" aria-labelledby="yetiforceDetails">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="myModalLabel">
							YetiForceCRM {if $USER_MODEL->isAdminUser()}v{$YETIFORCE_VERSION}{/if} - The best open
							system in the world</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<p class="text-center"><img
									src="{App\Layout::getPublicUrl('layouts/resources/Logo/blue_yetiforce_logo.png')}"
									title="YetiForceCRM" alt="YetiForceCRM" style="height: 120px;"/></p>
						<p>Copyright © YetiForce.com All rights reserved.</p>
						<p>The Program is provided AS IS, without warranty. Licensed under <a
									href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt"
									target="_blank"><strong>YetiForce Public License 3.0</strong></a>.</p>
						<p>YetiForce is based on two systems - <strong>VtigerCRM</strong> and <strong>SugarCRM</strong>.<br/><br/>
						</p>
						<p><span class="badge badge-secondary">License:</span> <a
									href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt"
									target="_blank"><strong>YetiForce Public License 3.0</strong></a></p>
						<p><span class="badge badge-primary">WWW:</span> <a href="https://yetiforce.com" target="_blank"
																			rel="noreferrer"><strong>https://yetiforce.com</strong></a>
						</p>
						<p><span class="badge badge-success">Code:</span> <a
									href="https://github.com/YetiForceCompany/YetiForceCRM" target="_blank"
									rel="noreferrer"><strong>https://github.com/YetiForceCompany/YetiForceCRM</strong></a>
						</p>
						<p><span class="badge badge-info">Documentation:</span> <a
									href="https://yetiforce.com/en/knowledge-base/documentation" target="_blank"
									rel="noreferrer"><strong>https://yetiforce.com/en/documentation.html</strong></a>
						</p>
						<p><span class="badge badge-warning">Issues:</span> <a
									href="https://github.com/YetiForceCompany/YetiForceCRM/issues" target="_blank"
									rel="noreferrer"><strong>https://github.com/YetiForceCompany/YetiForceCRM/issues</strong></a>
						</p>
						<p><span class="badge badge-primary">Shop:</span> <a
									href="https://yetiforce.shop/" target="_blank"
									rel="noreferrer"><strong>https://yetiforce.shop/</strong></a>
						</p>
						<ul class="text-center list-inline">
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer" href="https://www.linkedin.com/groups/8177576"><span
											class="fab fa-linkedin" title="LinkendIn"></span></a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer" href="https://twitter.com/YetiForceEN"><span
											class="fab fa-twitter-square" title="Twitter"></span></a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer" href="https://www.facebook.com/YetiForce-CRM-158646854306054/"><span
											class="fab fa-facebook-square" title="Facebook"></span></a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer" href="https://github.com/YetiForceCompany/YetiForceCRM"><span
											class="fab fa-github-square" title="Github"></span></a>
							</li>
						</ul>
					</div>
					<div class="modal-footer">
						<button class="btn btn-warning" type="reset" data-dismiss="modal"><span
									class="fa fa-times u-mr-5px"></span><strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
						</button>
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
