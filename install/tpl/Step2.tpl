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
	<div class="main-container">
		<div class="inner-container">
			<form class="" name="step2" method="post" action="Install.php">
				<input type="hidden" name="mode" value="step3">
				<input type="hidden" name="lang" value="{$LANG}">
				<div class="row">
					<div class="col-12 text-center">
						<h2>{\App\Language::translate('LBL_LICENSE', 'Install')}</h2>
					</div>
				</div>
				<div class="row">
					<div class="span12">
						<div class="license">
							<div class="lic-scroll">
								{$LICENSE}
							</div>
						</div>
					</div>
				</div>
				<div class="form-buttom-nav fixed-bottom button-container p-1">
					<div class="text-center">
						<input name="back" type="button" class="btn btn-md btn-danger" value="{\App\Language::translate('LBL_DISAGREE', 'Install')}">
						<input id="agree" type="submit" class="btn btn-md btn-primary" value="{\App\Language::translate('LBL_I_AGREE', 'Install')}">
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
