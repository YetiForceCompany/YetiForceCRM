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

<div class="row-fluid main-container">
	<div class="inner-container">
		<form class="form-horizontal" name="step1" method="post" action="Install.php">
			<div class="row-fluid">
				<div class="span9">
					<h4>{vtranslate('LBL_WELCOME', 'Install')}</h4>
				</div>
				<div class="span3">
					<select name="lang" class="chzn-select span3" style="width: 250px;">
						{foreach key=key item=item from=$LANGUAGES}
							<option value="{$key}" {if $LANG eq $key}selected{/if}>{$item}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<hr>
			<input type=hidden name="mode" value="Step2" />
			<div class="row-fluid">
				<div class="span4 welcome-image">
					<img src="../layouts/vlayout/skins/images/wizard_screen.png" alt="Vtiger Logo"/>
				</div>
				<div class="span8">
					<div class="welcome-div">
						<h3>{vtranslate('LBL_WELCOME_TO_VTIGER6_SETUP_WIZARD', 'Install')}</h3>
						{vtranslate('LBL_VTIGER6_SETUP_WIZARD_DESCRIPTION','Install')}
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="button-container">
					<a href="#" class="btn btn-large btn-primary bt_install">
						{vtranslate('LBL_INSTALL_BUTTON','Install')}
					</a>
					<a style="" href="#" class="btn btn-large btn-primary bt_migrate">
						{vtranslate('LBL_MIGRATION','Install')}
					</a>
				</div>
			</div>
		</form>
	</div>
</div>