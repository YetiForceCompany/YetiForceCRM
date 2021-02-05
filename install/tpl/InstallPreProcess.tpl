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
	<!-- tpl-install-tpl-InstallPreProcess -->
	{include file='Header.tpl'}
	<div class="w-100 bg-light o-install__header__wrapper">
		<div class="container px-2 px-sm-3">
			<header class="d-flex flex-nowrap align-items-center">
				<div class="logo">
					<img src="../{\App\Layout::getPublicUrl('layouts/resources/Logo/logo_yetiforce.png')}"
						 alt="{\App\Language::translate('LBL_COMPANY_LOGO_TITLE', 'Install')} YetiForce">
				</div>
				<div class="pl-1 pl-sm-3">
					<h1 class="h3">{\App\Language::translate('LBL_INSTALLATION_WIZARD', 'Install')}</h1>
				</div>
			</header>
		</div>
	</div>
	{if $MODE === 'step7'}
		<div id="progressIndicator" class="main-container">
			<div class="inner-container py-5">
				<div class="col-12 text-center py-5">
					<h3>{\App\Language::translate('LBL_INSTALLATION_IN_PROGRESS','Install')}...</h3><br>
					<img src="../{\App\Layout::getPublicUrl('layouts/basic/images/install_loading.gif')}"
						 alt="Install loading"><br>
					<h6>{\App\Language::translate('LBL_STEP7_DESCRIPTION','Install')}</h6>
					<ul class="text-center list-inline">
						<li class="yetiforceDetailsLink list-inline-item">
							<a rel="noreferrer noopener" target="_blank" href="https://yetiforce.com">
								<span class="fas fa-link"></span>
								<span class="sr-only">yetiforce.com</span>
							</a>
						</li>
						<li class="yetiforceDetailsLink list-inline-item">
							<a rel="noreferrer noopener" target="_blank" href="https://doc.yetiforce.com">
								<span class="mdi mdi-book-open-page-variant" title="YetiForce Doc"></span>
							</a>
						</li>
						<li class="yetiforceDetailsLink list-inline-item">
							<a rel="noreferrer noopener" target="_blank" href="https://www.linkedin.com/groups/8177576">
								<span class="fab fa-linkedin"></span>
								<span class="sr-only">Linkedin</span>
							</a>
						</li>
						<li class="yetiforceDetailsLink list-inline-item">
							<a rel="noreferrer noopener" target="_blank" href="https://twitter.com/YetiForceEN">
								<span class="fab fa-twitter-square"></span>
								<span class="sr-only">Twitter</span>
							</a>
						</li>
						<li class="yetiforceDetailsLink list-inline-item">
							<a rel="noreferrer noopener" target="_blank"
							   href="https://www.facebook.com/YetiForce-CRM-158646854306054/">
								<span class="fab fa-facebook-square"></span>
								<span class="sr-only">Facebook</span>
							</a>
						</li>
						<li class="yetiforceDetailsLink list-inline-item">
							<a rel="noreferrer noopener" target="_blank" href="https://github.com/YetiForceCompany/YetiForceCRM">
								<span class="fab fa-github-square"></span>
								<span class="sr-only">Github</span>
							</a>
						</li>
						<li class="yetiforceDetailsLink list-inline-item">
							<a rel="noreferrer noopener" target="_blank"
							   href="https://github.com/YetiForceCompany/YetiForceCRM/issues">
								<span class="fas fa-bug"></span>
								<span class="sr-only">Issues</span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	{/if}
	<!-- /tpl-install-tpl-InstallPreProcess -->
{/strip}
