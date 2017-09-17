{*<!--
/************************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************/
-->*}
{strip}
	{include file=\App\Layout::getTemplatePath('Header.tpl', $MODULE)}
	<div class=" page-container">
		<div class="row">
			<div class="col-md-6">
				<div class="logo">
					<img src="{\App\Layout::getImagePath('vt1.png')}" alt="Vtiger Logo"/>
				</div>
			</div>
			<div class="col-md-6">
				<div class="head pull-right">
					<h3> {\App\Language::translate('LBL_MIGRATION_WIZARD', $MODULE)}</h3>
				</div>
			</div>
		</div>
		<div class="row main-container">
			<div class="col-md-12 inner-container">
					<div class="row">
						<div class="col-md-10">
							<h4> {\App\Language::translate('LBL_MIGRATION_COMPLETED', $MODULE)} </h4> 
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-4 welcome-image">
							<img src="{\App\Layout::getImagePath('migration_screen.png')}" alt="Vtiger Logo"/>
						</div>
						<div class="col-md-1"></div>
						<div class="col-md-6">
							<br /><br />
							<h5>{\App\Language::translate('LBL_MIGRATION_COMPLETED_SUCCESSFULLY', $MODULE)}  </h5><br /><br />
								{\App\Language::translate('LBL_RELEASE_NOTES', $MODULE)}<br />
								{\App\Language::translate('LBL_CRM_DOCUMENTATION', $MODULE)}<br />
								{\App\Language::translate('LBL_TALK_TO_US_AT_FORUMS', $MODULE)}<br />
								{\App\Language::translate('LBL_DISCUSS_WITH_US_AT_BLOGS', $MODULE)}<br /><br />
								Connect with us &nbsp;&nbsp;
								<a href="https://www.facebook.com/vtiger" target="_blank" rel="noreferrer"><img src="{\App\Layout::getImagePath('facebook.png')}"></a> 
	                            &nbsp;&nbsp;<a href="https://twitter.com/vtigercrm" target="_blank" rel="noreferrer"><img src="{\App\Layout::getImagePath('twitter.png')}"></a> 
	                            &nbsp;&nbsp;<a href="//www.vtiger.com/products/crm/privacy_policy.html" target="_blank" rel="noreferrer"><img src="{\App\Layout::getImagePath('linkedin.png')}"></a> 
								<br /><br />
							<div class="button-container">
								<input type="button" onclick="window.location.href='index.php'" class="btn btn-lg btn-primary" value="Finish"/>
							</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
