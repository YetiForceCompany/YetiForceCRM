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
			<form class="" name="step3" method="post" action="Install.php">
				<input type="hidden" name="mode" value="step6">
				<input type="hidden" name="lang" value="{$LANG}">
				<div class="row">
					<div class="col-12 text-center">
						<h2>{App\Language::translate('LBL_INSTALL_PREREQUISITES', 'Install')}</h2>
					</div>
				</div>
				<hr>
				<div>
					<div class="float-right">
						<div class="button-container">
							<a href="#">
								<button type="button" class="btn btn-default" id="recheck">
									<span class="fas fa-redo-alt m-1"></span>
									{App\Language::translate('LBL_RECHECK', 'Install')}
								</button>
							</a>
						</div>
					</div>
					{\App\Language::translate('LBL_STEP3_DESCRIPTION','Install')}&nbsp;
					<a target="_blank" rel="noreferrer"
					   href="https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirements">
						<span class="fas fa-link"></span>
					</a>
					<div class="clearfix"></div>
					<div class="offset2">
						<div>
							{assign var="LIBRARY" value=Settings_ConfReport_Module_Model::getLibrary()}
							<table class="config-table table u-word-break-all">
								<thead>
								<tr>
									<th>
										<label>{App\Language::translate('LBL_LIBRARY', 'Settings::ConfReport')}</label>
									</th>
									<th>
										<label>{App\Language::translate('LBL_INSTALLED', 'Settings::ConfReport')}</label>
									</th>
									<th>
										<label>{App\Language::translate('LBL_MANDATORY', 'Settings::ConfReport')}</label>
									</th>
								</tr>
								</thead>
								<tbody>
								{foreach from=$LIBRARY key=key item=item}
									<tr {if $item.status == 'LBL_NO'}class="table-danger font-weight-bold"{/if}>
										<td>{App\Language::translate($key, 'Settings::ConfReport')}</td>
										<td>{App\Language::translate($item.status, 'Settings::ConfReport')}</td>
										<td>
											{if $item.mandatory}
												{App\Language::translate('LBL_MANDATORY', 'Settings::ConfReport')}
											{else}
												{App\Language::translate('LBL_OPTIONAL', 'Settings::ConfReport')}
											{/if}
										</td>
									</tr>
								{/foreach}
								</tbody>
							</table>
							<br>
							<table class="config-table table u-word-break-all">
								<thead>
								<tr>
									<th>{App\Language::translate('LBL_SECURITY_RECOMMENDED_SETTINGS', 'Install')}</th>
									<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
									<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
								</tr>
								</thead>
								<tbody>
								{foreach from=$SECURITY_CONF key=key item=item}
									<tr {if $item.status}class="table-danger font-weight-bold"{/if}>
										<td>
											<label>{$key}</label>
											{if isset($item.help)}
												<a href="#" class="js-popover-tooltip float-right"
												   data-js="popover" data-placement="top"
												   data-content="{App\Language::translate($item.help, 'Settings::ConfReport')}">
													<i class="fas fa-info-circle"></i>
												</a>
											{/if}
										</td>
										<td>
											<label>{App\Language::translate($item.recommended, 'Settings::ConfReport')}</label>
										</td>
										<td>
											<label>{App\Language::translate($item.current, 'Settings::ConfReport')}</label>
										</td>
									</tr>
								{/foreach}
								</tbody>
							</table>
							{assign var="STABILITY_CONF" value=$STABILITY_CONF}
							<br>
							<table class="config-table table u-word-break-all">
								<thead>
								<tr>
									<th>{App\Language::translate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</th>
									<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
									<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
								</tr>
								</thead>
								<tbody>
								{foreach from=$STABILITY_CONF key=key item=item}
									<tr {if $item.incorrect}class="table-danger font-weight-bold"{/if}>
										<td>
											<label>{$key}</label>
											{if isset($item.help)}
												<a href="#" class="js-popover-tooltip float-right"
												   data-js="popover" data-placement="top"
												   data-content="{App\Language::translate($item.help, 'Settings::ConfReport')}">
													<i class="fas fa-info-circle"></i>
												</a>
											{/if}
										</td>
										<td>
											<label>{App\Language::translate($item.recommended, 'Settings::ConfReport')}</label>
										</td>
										<td>
											<label>{App\Language::translate($item.current, 'Settings::ConfReport')}</label>
										</td>
									</tr>
								{/foreach}
								</tbody>
							</table>
							{if $DB_CONF}
								<br>
								<table class="config-table table u-word-break-all">
									<thead>
									<tr>
										<th>{App\Language::translate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</th>
										<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
										<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$DB_CONF key=key item=item}
										<tr {if $item['status']}class="table-danger font-weight-bold"{/if}>
											<td>
												<label>{App\Language::translate($key, 'Settings::ConfReport')}</label>
												{if isset($item.help)}
													<a href="#" class="js-popover-tooltip float-right"
													   data-js="popover"
													   data-trigger="focus"
													   data-placement="right"
													   data-content="{\App\Language::translateEncodeHtml($item.help, 'Settings::ConfReport')}">
														<span class="fas fa-info-circle"></span></a>
												{/if}
											</td>
											{if $item['recommended'] === false}
												<td colspan="2">
													<label>{$item['current']}</label>
												</td>
											{else}
												<td><label>{$item['recommended']}</label></td>
												<td><label>{$item['current']}</label></td>
											{/if}
										</tr>
									{/foreach}
									</tbody>
								</table>
							{/if}
							{if $FAILED_FILE_PERMISSIONS}
								<table class="config-table table u-word-break-all">
									<thead>
									<tr class="blockHeader">
										<th colspan="1" class="mediumWidthType">
											<span>{App\Language::translate('LBL_READ_WRITE_ACCESS', 'Install')}</span>
										</th>
										<th colspan="1" class="mediumWidthType">
											<span>{App\Language::translate('LBL_PATH', 'Settings::ConfReport')}</span>
										</th>
										<th colspan="1" class="mediumWidthType">
											<span>{App\Language::translate('LBL_PERMISSION', 'Settings::ConfReport')}</span>
										</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$FAILED_FILE_PERMISSIONS key=key item=item}
										<tr {if $item.permission eq 'FailedPermission'}class="table-danger font-weight-bold"{/if}>
											<td width="23%">
												<label class="marginRight5px">{App\Language::translate($key, 'Settings::ConfReport')}</label>
											</td>
											<td width="23%">
												<label class="marginRight5px">{App\Language::translate($item.path, 'Settings::ConfReport')}</label>
											</td>
											<td width="23%">
												<label class="marginRight5px">
													{if $item.permission eq 'FailedPermission'}
														{App\Language::translate('LBL_FAILED_PERMISSION', 'Settings::ConfReport')}
													{else}
														{App\Language::translate('LBL_TRUE_PERMISSION', 'Settings::ConfReport')}
													{/if}
												</label>
											</td>
										</tr>
									{/foreach}
									</tbody>
								</table>
							{/if}
						</div>
					</div>
				</div>
				<div class="form-buttom-nav fixed-bottom button-container p-1">
					<div class="text-center">
						<a class="btn c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php">
							<span class="fas fa-arrow-circle-left mr-1"></span>
							{App\Language::translate('LBL_BACK', 'Install')}
						</a>
						<button type="submit" role="button" class="btn c-btn-block-xs-down btn-primary">
							<span class="fas fa-arrow-circle-right mr-1"></span>
							{App\Language::translate('LBL_NEXT', 'Install')}
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
