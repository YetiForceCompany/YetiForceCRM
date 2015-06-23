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
<form class="form-horizontal" name="step3" method="post" action="Install.php">
	<input type="hidden" name="mode" value="Step4" />
	<input type="hidden" name="lang" value="{$LANG}" />

	<div class="row main-container">
		<div class="inner-container">
			<h4>{vtranslate('LBL_INSTALL_PREREQUISITES', 'Install')}</h4>
			<hr>
			<div>
				<div class="offset2">
					<div class="pull-right">
						<div class="button-container">
							<a href ="#">
								<input type="button" class="btn btn-default" value="{vtranslate('LBL_RECHECK', 'Install')}" id='recheck'/>
							</a>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="offset2">
					<div>
						<table class="config-table">
							<tr><th>{vtranslate('LBL_PHP_CONFIGURATION', 'Install')}</th><th>{vtranslate('LBL_REQUIRED_VALUE', 'Install')}</th><th>{vtranslate('LBL_PRESENT_VALUE', 'Install')}</th></tr>
							{foreach key=CONFIG_NAME item=INFO from=$SYSTEM_PREINSTALL_PARAMS}
								<tr><td>{vtranslate($CONFIG_NAME, 'Install')}</td><td>{if $INFO.1 eq 1} {vtranslate('LBL_TRUE', 'Install')} {else} {$INFO.1} {/if}</td>
									<td {if $INFO.2 eq false} class="no" >
										{if $INFO.0 !== true && $INFO.0 !== false}{$INFO.0}{else}{vtranslate('LBL_NO', 'Install')}{/if}
									{else if ($INFO.2 eq true and $INFO.1 === true)} > 
									{if $INFO.0 !== true}{$INFO.0}{else}{vtranslate('LBL_YES', 'Install')}{/if}
									{else} > {$INFO.0} {/if}
										
									</td></tr>
							{/foreach}
						</table>
						<br>
						{if $PHP_INI_CURRENT_SETTINGS}
						<table class="config-table">
							<tr>
								<th colspan="3">{vtranslate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</th>
							</tr>
							{foreach key=DIRECTIVE item=VALUE from=$PHP_INI_CURRENT_SETTINGS name=directives}
								<tr>
									<td>{$DIRECTIVE}</td><td>{$PHP_INI_RECOMMENDED_SETTINGS[$DIRECTIVE]}</td><td class="no">{$VALUE}</td>
								</tr>
							{/foreach}
						</table>
						{/if}
						{if $FAILED_FILE_PERMISSIONS}
							<table class="config-table">
								<tr><th colspan="2">{vtranslate('LBL_READ_WRITE_ACCESS', 'Install')}</th></tr>
								{foreach item=FILE_PATH key=FILE_NAME from=$FAILED_FILE_PERMISSIONS}
									<tr>
										<td nowrap>{$FILE_NAME} ({str_replace("./","",$FILE_PATH)})</td><td class="no">{vtranslate('LBL_NO', 'Install')}</td>
									</tr>
								{/foreach}
							</table>
						{/if}
					</div>
				</div>
			</div>
			<div class="offset2">
				<div class="button-container">
					<input type="button" class="btn btn-sm btn-default" value="{vtranslate('LBL_BACK', 'Install')}" name="back"/>
					<input type="button" class="btn btn-sm btn-primary" value="{vtranslate('LBL_NEXT', 'Install')}" name="step4"/>
				</div>
			</div>
		</div>
	</div>
</form>
