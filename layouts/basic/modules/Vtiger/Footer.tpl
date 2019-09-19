{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-Footer -->
	{assign var="DISABLE_BRANDING" value=\App\YetiForce\Shop::check('YetiForceDisableBranding')}
	{if $DISABLE_BRANDING}
		{assign var="URL_LINKEDIN" value=\App\Config::component('Branding', 'urlLinkedIn')}
		{assign var="URL_TWITTER" value=\App\Config::component('Branding', 'urlTwitter')}
		{assign var="URL_FACEBOOK" value=\App\Config::component('Branding', 'urlFacebook')}
		{assign var="URL_GITHUB" value=null}
		{assign var="FOOTER_NAME" value=\App\Config::component('Branding', 'footerName')}
	{else}
		{assign var="URL_LINKEDIN" value='https://www.linkedin.com/groups/8177576'}
		{assign var="URL_TWITTER" value='https://twitter.com/YetiForceEN'}
		{assign var="URL_FACEBOOK" value='https://www.facebook.com/YetiForce-CRM-158646854306054/'}
		{assign var="URL_GITHUB" value='https://github.com/YetiForceCompany/YetiForceCRM'}
	{/if}
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	<input class="tpl-Footer d-none noprint" type="hidden" id="activityReminder" value="{$ACTIVITY_REMINDER}"/>
	{if \App\Privilege::isPermitted('Chat')}
		<div class="quasar-reset">
			<div id="ChatModalVue"></div>
		</div>
	{/if}
	{if $SHOW_FOOTER}
		<footer class="c-footer fixed-bottom js-footer{if App\Config::module('Users', 'IS_VISIBLE_USER_INFO_FOOTER')} c-footer--user-info-active{/if} {if $DISABLE_BRANDING} c-footer--limited {/if}"
				data-js="height">
			{if App\Config::module('Users', 'IS_VISIBLE_USER_INFO_FOOTER')}
				<div class="js-footer__user-info c-footer__user-info">
					<p>
						<span class="mr-1"> {$USER_MODEL->getName()}</span>(
						<span>{$USER_MODEL->get('email1')}</span>
						{if !empty($USER_MODEL->get('phone_crm_extension'))}
						,<span class="ml-1">{$USER_MODEL->get('phone_crm_extension')}</span>
						{/if})
					</p>
				</div>
			{/if}
			<div class="container-fluid px-0 px-md-1">
				<ul class="float-left pagination border-0">
					{if !empty($URL_LINKEDIN)}
						<li class="page-item">
							<a class="page-link" href="{$URL_LINKEDIN}" target="_blank"
								rel="noreferrer noopener">
								<span class="fab fa-linkedin fa-2x" title="Linkedin"></span>
							</a>
						</li>
					{/if}
					{if !empty($URL_TWITTER)}
						<li class="page-item">
							<a class="page-link" href="{$URL_TWITTER}" target="_blank" rel="noreferrer noopener">
								<span class="fab fa-twitter-square fa-2x" title="Twitter"></span>
							</a>
						</li>
					{/if}
					{if !empty($URL_FACEBOOK)}
						<li class="page-item">
							<a class="page-link" href="{$URL_FACEBOOK}" target="_blank"
								rel="noreferrer noopener">
								<span class="fab fa-facebook-square fa-2x" title="Facebook"></span>
							</a>
						</li>
					{/if}
					{if !empty($URL_GITHUB)}
						<li class="page-item">
							<a class="page-link" href="{$URL_GITHUB}" target="_blank"
								rel="noreferrer noopener">
								<span class="fab fa-github-square fa-2x" title="Github"></span>
							</a>
						</li>
					{/if}
				</ul>
				<div class="float-right p-0">
					<ul class="pagination">
						{if !$DISABLE_BRANDING }
							<li class="page-item">
								<a class="page-link mr-md-1" href="https://yetiforce.shop" rel="noreferrer noopener">
									<span class="fas fa-shopping-cart fa-2x" title="yetiforce.shop"></span>
								</a>
							</li>
							<li class="page-item u-cursor-pointer">
								<a class="page-link" data-toggle="modal" href="#" role="button"
									data-target="#yetiforceDetails">
									<span class="fas fa-info-circle fa-2x" title="YetiForceCRM"></span>
								</a>
							</li>
						{/if}
					</ul>
				</div>
				<div class="mx-auto w-75">
					{assign var=SCRIPT_TIME value=round(microtime(true) - \App\Process::$startTime, 3)}
					{assign var=FOOTVR value= '[ver. '|cat:$YETIFORCE_VERSION|cat:'] ['|cat:\App\Language::translate('WEBLOADTIME')|cat:': '|cat:$SCRIPT_TIME|cat:'s.]'}
					{if $USER_MODEL->isAdminUser()}
						{assign var=FOOTVRM value= '['|cat:$SCRIPT_TIME|cat:'s.]'}
						{assign var=FOOTOSP value= '<em><a class="u-text-underline" href="index.php?module=Vtiger&view=Credits&parent=Settings">open source project</a></em>'}
						<p class="text-center text-center">
							{if !$DISABLE_BRANDING}
								<span class="d-none d-sm-inline ">Copyright &copy; YetiForce.com All rights reserved. {$FOOTVR}
										<br/>
										{\App\Language::translateArgs('LBL_FOOTER_CONTENT', '_Base', $FOOTOSP)}
								</span>
								<span class="d-inline d-sm-none text-center">&copy; YetiForce.com All rights reserved.</span>
							{else}
								{$FOOTER_NAME} [{\App\Language::translate('WEBLOADTIME')}: {$SCRIPT_TIME}s.]
							{/if}
						</p>
					{else}
						<p class="text-center">
							{if !$DISABLE_BRANDING}
								<span class="d-none d-sm-inline">
									Copyright &copy; YetiForce.com All rights reserved.
									[{\App\Language::translate('WEBLOADTIME')}: {$SCRIPT_TIME}s.]
									<br/>
									{\App\Language::translateArgs('LBL_FOOTER_CONTENT', '_Base', 'open source project')}
								</span>
								<span class="d-inline d-sm-none text-center">&copy; YetiForce.com All rights reserved.</span>
							{else}
								{$FOOTER_NAME} [{\App\Language::translate('WEBLOADTIME')}: {$SCRIPT_TIME}s.]
							{/if}
						</p>
					{/if}
				</div>
			</div>
		</footer>
		<div class="modal fade" id="yetiforceDetails" tabindex="-1" role="dialog" aria-labelledby="yetiforceDetails">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="myModalLabel">
							YetiForceCRM {if $USER_MODEL->isAdminUser()}v{$YETIFORCE_VERSION}{/if} - The best open
							system in the world
						</h5>
						<button type="button" class="close" data-dismiss="modal"
								title="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p class="text-center">
							<img class="u-h-120px"
								 src="{App\Layout::getPublicUrl('layouts/resources/Logo/blue_yetiforce_logo.png')}"
								 title="YetiForceCRM" alt="YetiForceCRM"/>
						</p>
						<p>Copyright © YetiForce.com All rights reserved.</p>
						<p>The Program is provided AS IS, without warranty. Licensed under <a
									href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt"
									target="_blank" rel="noreferrer noopener"><strong>YetiForce Public License
									3.0</strong></a>.</p>
						<p>YetiForce is based on two systems - <strong>VtigerCRM</strong> and <strong>SugarCRM</strong>.<br/><br/>
						</p>
						<div class="u-word-break">
							<p><span class="badge badge-secondary">License:</span> <a
										href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt"
										target="_blank" rel="noreferrer noopener"><strong>YetiForce Public License
										3.0</strong></a></p>
							<p><span class="badge badge-primary">WWW:</span> <a href="https://yetiforce.com"
																				target="_blank"
																				rel="noreferrer noopener"><strong>https://yetiforce.com</strong></a>
							</p>
							<p><span class="badge badge-success">Code:</span> <a
										href="https://github.com/YetiForceCompany/YetiForceCRM" target="_blank"
										rel="noreferrer noopener"><strong>https://github.com/YetiForceCompany/YetiForceCRM</strong></a>
							</p>
							<p><span class="badge badge-info">Documentation:</span> <a
										href="https://yetiforce.com/en/knowledge-base/documentation" target="_blank"
										rel="noreferrer noopener"><strong>https://yetiforce.com/en/documentation.html</strong></a>
							</p>
							<p><span class="badge badge-warning">Issues:</span> <a
										href="https://github.com/YetiForceCompany/YetiForceCRM/issues" target="_blank"
										rel="noreferrer noopener"><strong>https://github.com/YetiForceCompany/YetiForceCRM/issues</strong></a>
							</p>
							<p><span class="badge badge-primary">Shop:</span> <a
										href="https://yetiforce.shop/" target="_blank"
										rel="noreferrer noopener"><strong>https://yetiforce.shop/</strong></a>
							</p>
						</div>
						<ul class="text-center list-inline">
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer noopener" href="https://www.linkedin.com/groups/8177576"><span
											class="fab fa-linkedin" title="LinkendIn"></span></a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer noopener" href="https://twitter.com/YetiForceEN"><span
											class="fab fa-twitter-square" title="Twitter"></span></a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer noopener"
								   href="https://www.facebook.com/YetiForce-CRM-158646854306054/"><span
											class="fab fa-facebook-square" title="Facebook"></span></a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer noopener"
								   href="https://github.com/YetiForceCompany/YetiForceCRM"><span
											class="fab fa-github-square" title="Github"></span></a>
							</li>
						</ul>
					</div>
					<div class="modal-footer">
						<button class="btn btn-danger" type="reset" data-dismiss="modal">
							<span class="fa fa-times u-mr-5px"></span>
							<strong>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
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
	<!-- /tpl-Base-Footer -->
{/strip}
