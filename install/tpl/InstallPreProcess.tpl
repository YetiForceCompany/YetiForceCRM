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
	{include file='Header.tpl'}
	<div class="container page-container">
		<div class="row">
			<div class="col-md-6">
				<div class="logo">
					<img src="../{\App\Layout::getPublicUrl('layouts/resources/Logo/logo_yetiforce.png')}" style="height: 70px;" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="head pull-right">
					<h3>{\App\Language::translate('LBL_INSTALLATION_WIZARD', 'Install')}</h3>
				</div>
			</div>
		</div>
		{if $MODE === 'step7'}
			<div id="progressIndicator" class="row main-container">
				<div class="inner-container">
					<div class="inner-container">
						<div class="row">
							<div class="span12 welcome-div alignCenter">
								<h3>{\App\Language::translate('LBL_INSTALLATION_IN_PROGRESS','Install')}...</h3><br />
								<img src="../{\App\Layout::getPublicUrl('layouts/basic/images/install_loading.gif')}" alt="Install loading"/>
								<h6>{\App\Language::translate('LBL_PLEASE_WAIT','Install')}.... </h6>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}			
	{/strip}
