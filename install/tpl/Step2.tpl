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
				<hr>
				<div class="row">
					<div class="col-12">
						<p>
							{\App\Language::translate('LBL_STEP2_DESCRIPTION_1','Install')}&nbsp;
							<a target="_blank" rel="noreferrer"
							   href="https://yetiforce.com/en/yetiforce/license">
								<span class="fas fa-link"></span>							</a><br/><br/>
							{\App\Language::translate('LBL_STEP2_DESCRIPTION_2','Install')}
						</p>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<p class="license">{$LICENSE}</p>
					</div>
				</div>
				<div class="form-buttom-nav fixed-bottom button-container p-1">
					<div class="text-center">
						<a class="btn c-btn-block-xs-down btn-danger mb-1 mb-sm-0 mr-sm-1" href="Install.php">
							<span class="fas fa-times-circle mr-1"></span>
							{App\Language::translate('LBL_DISAGREE', 'Install')}
						</a>
						<button type="submit" class="btn c-btn-block-xs-down btn-primary">
							<span class="fas fa-check mr-1"></span>
							{App\Language::translate('LBL_I_AGREE', 'Install')}
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
