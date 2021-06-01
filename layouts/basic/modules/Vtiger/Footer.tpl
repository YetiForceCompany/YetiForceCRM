{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o
**********************************************************************************/

/*****************************************************************************************************************
****   Modifying this file or functions that affect the footer appearance will violate the license terms!!!	  ****
******************************************************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-Footer -->
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	<input class="d-none noprint" type="hidden" id="activityReminder" value="{$ACTIVITY_REMINDER}"/>
	{assign var="IS_ADMIN" value=$USER_MODEL->isAdminUser()}
	{if $SHOW_FOOTER_BAR}
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
			{assign var="FOOTER_NAME" value=''}
		{/if}
		<footer class="c-footer fixed-bottom js-footer{if App\Config::module('Users', 'IS_VISIBLE_USER_INFO_FOOTER')} c-footer--user-info-active{/if} {if $DISABLE_BRANDING} c-footer--limited {/if}" data-js="height">
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
							<a class="page-link" href="{$URL_LINKEDIN}" target="_blank"	rel="noreferrer noopener">
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
							<a class="page-link" href="{$URL_FACEBOOK}" target="_blank"	rel="noreferrer noopener">
								<span class="fab fa-facebook-square fa-2x" title="Facebook"></span>
							</a>
						</li>
					{/if}
					{if !empty($URL_GITHUB)}
						<li class="page-item">
							<a class="page-link" href="{$URL_GITHUB}" target="_blank" rel="noreferrer noopener">
								<span class="fab fa-github-square fa-2x" title="Github"></span>
							</a>
						</li>
					{/if}
				</ul>
				<div class="float-right p-0">
					<ul class="pagination {if $DISABLE_BRANDING }mt-1{/if}">
						{if !\App\YetiForce\Register::verify(true)}
							{if \App\Security\AdminAccess::isPermitted('Companies')}
								{assign var="INFO_REGISTRATION_ERROR" value="<a href='index.php?module=Companies&parent=Settings&view=List&displayModal=online'>{\App\Language::translate('LBL_YETIFORCE_REGISTRATION_CHECK_STATUS', $MODULE_NAME)}</a>"}
							{else}
								{assign var="INFO_REGISTRATION_ERROR" value=\App\Language::translate('LBL_YETIFORCE_REGISTRATION_CHECK_STATUS', $MODULE_NAME)}
							{/if}
							<li class="page-item">
								<a class="page-link text-warning p-0 mr-md-1 text-danger js-popover-tooltip c-header__btn" role="button"
										data-content="{\App\Language::translateArgs('LBL_YETIFORCE_REGISTRATION_ERROR', $MODULE_NAME, $INFO_REGISTRATION_ERROR)}"
										title="{\App\Purifier::encodeHtml('<span class="yfi yfi-yeti-register-alert mr-1"></span>')}{\App\Language::translate('LBL_YETIFORCE_REGISTRATION', $MODULE_NAME)}"
										{if \App\Security\AdminAccess::isPermitted('Companies')}
											href="index.php?parent=Settings&module=Companies&view=List&displayModal=online"
										{else}
											href="#"
										{/if} >
									<span class="yfi yfi-yeti-register-alert fa-2x">
									</span>
								</a>
							</li>
						{/if}
						{assign var=VERIFY value=\App\YetiForce\Shop::verify()}
						{if $VERIFY}
							<li class="page-item">
								<a class="page-link text-warning mr-md-1 js-popover-tooltip" role="button" data-content="{$VERIFY}" title="{\App\Purifier::encodeHtml('<span class="yfi yfi-shop-alert mr-1"></span>')}{\App\Language::translate('LBL_YETIFORCE_SHOP')}"
									{if $IS_ADMIN} href="index.php?module=YetiForce&parent=Settings&view=Shop"{else} href="#"{/if}>
									<span class="yfi yfi-shop-alert {if !$DISABLE_BRANDING }fa-2x{/if}"></span>
								</a>
							</li>
						{/if}
						{if !$DISABLE_BRANDING}
							{if $IS_ADMIN}
								<li class="page-item">
									<a class="page-link mr-md-1" href="index.php?module=YetiForce&parent=Settings&view=Shop" target="_blank" rel="noreferrer noopener">
										<span class="fas fa-shopping-cart fa-2x" title="{\App\Language::translate('LBL_YETIFORCE_SHOP')}"></span>
									</a>
								</li>
							{/if}
							<li class="page-item">
								<a class="page-link mr-md-1" href="https://doc.yetiforce.com" target="_blank" rel="noreferrer noopener">
									<span class="mdi mdi-book-open-page-variant fa-2x" title="doc.yetiforce.com"></span>
								</a>
							</li>
							<li class="page-item u-cursor-pointer">
								<a class="page-link" data-toggle="modal" href="#" role="button" data-target="#yetiforceDetails">
									<span class="fas fa-info-circle fa-2x" title="YetiForceCRM"></span>
								</a>
							</li>
						{/if}
					</ul>
				</div>
				<div class="mx-auto w-75">
					{assign var=SCRIPT_TIME value=round(microtime(true) - \App\Process::$startTime, 3)}
					<p class="text-center">
						{if !$DISABLE_BRANDING}
							{assign var=FOOTVR value= '['|cat:\App\Language::translate('WEBLOADTIME')|cat:': '|cat:$SCRIPT_TIME|cat:'s.]'}
							{assign var=FOOTOSP value='open source project'}
							{if $IS_ADMIN}
								{assign var=FOOTVR value= "[ver. {$YETIFORCE_VERSION}] {$FOOTVR}"}
							{/if}
							{if \App\Security\AdminAccess::isPermitted('Dependencies')}
								{assign var=FOOTOSP value= '<em><a class="u-text-underline" href="index.php?module=Dependencies&view=Credits&parent=Settings">open source project</a></em>'}
							{/if}
							<span class="d-none d-sm-inline">
								Copyright &copy; YetiForce.com All rights reserved. {$FOOTVR}
								<br/>
								{\App\Language::translateArgs('LBL_FOOTER_CONTENT', '_Base', $FOOTOSP)}
							</span>
							<span class="d-inline d-sm-none text-center">&copy; YetiForce.com All rights reserved.</span>
						{else}
							{$FOOTER_NAME} [{\App\Language::translate('WEBLOADTIME')}: {$SCRIPT_TIME}s.]
						{/if}
					</p>
				</div>
			</div>
		</footer>
		<div class="modal fade" id="yetiforceDetails" tabindex="-1" role="dialog" aria-labelledby="yetiforceDetails">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="myModalLabel">
							YetiForceCRM {if $IS_ADMIN}v{$YETIFORCE_VERSION}{/if} - The most flexible CRM in the world
						</h5>
						<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p class="text-center">
							<img class="u-h-120px" src="{App\Layout::getPublicUrl('layouts/resources/Logo/logo_hor.png')}" title="YetiForceCRM" alt="YetiForceCRM"/>
						</p>
						<p class="text-center">
							<ul class="list-group o-min-width m-auto">
								<li class="list-group-item d-flex justify-content-between align-items-center">
									<span class="badge badge-primary mr-2 badge-pill">APP ID</span>
									<span>{\App\YetiForce\Register::getInstanceKey()}</span>
								</li>
							</ul>
						</p>
						<p>Copyright © YetiForce.com All rights reserved.</p>
						<p class="my-2">The Program is provided AS IS, without warranty. Licensed under
							<a href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt" class="ml-2" target="_blank" rel="noreferrer noopener">
								<strong>YetiForce Public License 3.0</strong>
							</a>.
						</p>
						<p>YetiForce is based on two systems - <strong>VtigerCRM</strong> and <strong>SugarCRM</strong>.<br/><br/>
						</p>
						<div class="u-word-break">
							<p>
								<span class="badge badge-secondary mr-2">License</span>
								<a href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt" target="_blank" rel="noreferrer noopener">
									<strong>YetiForce Public License 3.0</strong>
								</a>
							</p>
							<p>
								<span class="badge badge-primary mr-2">WWW</span>
								<a href="https://yetiforce.com" target="_blank" rel="noreferrer noopener">
									<strong>https://yetiforce.com</strong>
								</a>
							</p>
							<p>
								<span class="badge badge-success mr-2">Code</span>
								<a href="https://github.com/YetiForceCompany/YetiForceCRM" target="_blank" rel="noreferrer noopener">
									<strong>https://github.com/YetiForceCompany/YetiForceCRM</strong>
								</a>
							</p>
							<p>
								<span class="badge badge-info mr-2">Documentation</span>
								<a href="https://doc.yetiforce.com" target="_blank" rel="noreferrer noopener">
									<strong>https://doc.yetiforce.com</strong>
								</a>
							</p>
							<p>
								<span class="badge badge-warning mr-2">Issues</span>
								<a href="https://github.com/YetiForceCompany/YetiForceCRM/issues" target="_blank" rel="noreferrer noopener">
									<strong>https://github.com/YetiForceCompany/YetiForceCRM/issues</strong>
								</a>
							</p>
						</div>
						<ul class="text-center list-inline">
							<li class="yetiforceDetailsLink list-inline-item mr-3">
								<a rel="noreferrer noopener" target="_blank" href="https://www.linkedin.com/groups/8177576">
									<span class="fab fa-linkedin" title="LinkendIn"></span>
								</a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item mr-3">
								<a rel="noreferrer noopener" target="_blank" href="https://www.youtube.com/c/YetiForceCRM">
									<span class="fab fa-youtube-square" title="YouTube"></span>
								</a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item mr-3">
								<a rel="noreferrer noopener" target="_blank" href="https://twitter.com/YetiForceEN">
									<span class="fab fa-twitter-square" title="Twitter"></span>
								</a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item mr-3">
								<a rel="noreferrer noopener" target="_blank" href="https://www.facebook.com/YetiForce-CRM-158646854306054/">
									<span class="fab fa-facebook-square" title="Facebook"></span>
								</a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item mr-3">
								<a rel="noreferrer noopener" target="_blank" href="https://github.com/YetiForceCompany/YetiForceCRM">
									<span class="fab fa-github-square" title="Github"></span>
								</a>
							</li>
							<li class="yetiforceDetailsLink list-inline-item">
								<a rel="noreferrer noopener" target="_blank" href="https://doc.yetiforce.com">
									<span class="mdi mdi-book-open-page-variant" title="YetiForce Documentation"></span>
								</a>
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
	{else}
		<div class="js-footer" data-js="height">
		</div>
	{/if}
	<!-- /tpl-Base-Footer -->
{/strip}
