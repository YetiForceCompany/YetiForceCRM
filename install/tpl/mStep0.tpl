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
			<h2>{vtranslate('LBL_LICENSE', 'Install')}</h2>
			<form class="form-horizontal" name="step2" method="post" action="Install.php">
				<input type="hidden" name="mode" value="mStep1" />
				<input type="hidden" name="lang" value="{$LANG}" />
				<div class="row">
					<div class="span12">
						<div class="license">
							<div class="lic-scroll">
								{include file="licenses/License.html"}
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="span12">
						<div class="button-container">
							<input name="back" type="button" class="btn btn-sm btn-default" value="{vtranslate('LBL_DISAGREE', 'Install')}"/>
							<input id="agree" type="submit" class="btn btn-sm btn-primary" value="{vtranslate('LBL_I_AGREE', 'Install')}"/>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
