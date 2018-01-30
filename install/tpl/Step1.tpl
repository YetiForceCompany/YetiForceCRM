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
	<div class="row main-container">
		<div class="inner-container">
			<form class="form-horizontal" name="step1" method="post" action="Install.php">
				<div class="row">
					<div class="col-md-9">
						<h4>{\App\Language::translate('LBL_WELCOME', 'Install')}</h4>
					</div>
					<div class="col-md-3">
						<select name="lang" class="chzn-select" style="width: 250px;">
							{foreach key=key item=item from=$LANGUAGES}
								<option value="{$key}" {if $LANG eq $key}selected{/if}>{$item}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<hr>
				<div class="float-right">
					<a class="helpBtn" href="https://yetiforce.com/en/implementer/installation-updates.html" target="_blank" rel="noreferrer">
						<span class="fa fa-info-circle" aria-hidden="true"></span>
					</a>
				</div>
				<input type="hidden" name="mode" value="step2" />
				<div class="col-md-4 welcome-image">
					<img src="../{\App\Layout::getPublicUrl('layouts/resources/Logo/logo_yetiforce.png')}" alt="Yetiforce Logo"/>
				</div>
				<div class="col-md-8">
					<div class="welcome-div">
						<h3>{\App\Language::translate('LBL_WELCOME_TO_VTIGER6_SETUP_WIZARD', 'Install')}</h3>
						<p>{\App\Language::translate('LBL_VTIGER6_SETUP_WIZARD_DESCRIPTION','Install')}</p>
					</div>
				</div>
				<div class="row">
					<div class="button-container">
						<a href="#" class="btn btn-sm btn-primary bt_install">
							{\App\Language::translate('LBL_INSTALL_BUTTON','Install')}
						</a>
						{if $IS_MIGRATE}
							<a style="" href="#" class="btn btn-sm btn-primary bt_migrate">
								{\App\Language::translate('LBL_MIGRATION','Install')}
							</a>
						{/if}
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
