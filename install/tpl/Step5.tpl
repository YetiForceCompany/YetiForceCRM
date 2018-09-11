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
	<div class="tpl-install-tpl-Step5 container px-2 px-sm-3">
		<main class="main-container">
			<div class="inner-container">
				<form class="js-confirm" name="step3" method="post" action="Install.php" data-js="submit">
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
								<button type="button" class="btn btn-default" id="recheck">
									<span class="fas fa-redo-alt m-1"></span>
									{App\Language::translate('LBL_RECHECK', 'Install')}
								</button>
							</div>
						</div>
						{\App\Language::translate('LBL_STEP3_DESCRIPTION','Install')}&nbsp;
						<a target="_blank" rel="noreferrer"
						   href="https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirements"
						   aria-label="https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirements">
							<span class="fas fa-link"></span>
						</a>

						<div class="offset2">
							<div>
								<table class="config-table table u-word-break-all">
									<thead>
									<tr>
										<th colspan="1" scope="col" class="text-left">
											{App\Language::translate('LBL_LIBRARY', 'Settings::ConfReport')}
										</th>
										<th colspan="1" scope="col">
											{App\Language::translate('LBL_MANDATORY', 'Settings::ConfReport')}
										</th>
										<th colspan="1" scope="col">
											{App\Language::translate('LBL_INSTALLED', 'Settings::ConfReport')}
										</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$ALL['libraries'] key=KEY item=ITEM}
										<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
											<td>
												{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'Settings::ConfReport')}{/if}
												{if !$ITEM['status']}
													{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), 'Settings::ConfReport')}
													{if !empty($HELP_TEXT)}
														<a href="#" class="js-popover-tooltip float-right"
														   data-js="popover"
														   data-trigger="focus hover" data-placement="right"
														   data-content="{$HELP_TEXT}">
															<span class="fas fa-info-circle"></span>
														</a>
													{/if}
												{/if}
											</td>
											{if isset($ITEM['mandatory'])}
											<td>
												{if $ITEM['mandatory']}
													{App\Language::translate('LBL_MANDATORY', 'Settings::ConfReport')}
												{else}
													{App\Language::translate('LBL_OPTIONAL', 'Settings::ConfReport')}
												{/if}
											</td>
											<td>
												{else}
											<td colspan="2" class="u-word-break-keep-all">
												{/if}
												{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'Settings::ConfReport')}{/if}
											</td>
										</tr>
									{/foreach}
									</tbody>
								</table>
								<br>
								<table class="config-table table u-word-break-all">
									<caption
											class="sr-only">{App\Language::translate('LBL_SECURITY_RECOMMENDED_SETTINGS', 'Install')}</caption>
									<thead>
									<tr>
										<th>{App\Language::translate('LBL_SECURITY_RECOMMENDED_SETTINGS', 'Install')}</th>
										<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
										<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$ALL['security'] key=KEY item=ITEM}
										<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
											<td class="bg-light text-left u-word-break-keep-all">
												{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'Settings::ConfReport')}{/if}
												{if !$ITEM['status']}
													{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), 'Settings::ConfReport')}
													{if !empty($HELP_TEXT)}
														<a href="#" class="js-popover-tooltip float-right" data-js="popover"
														   data-trigger="focus hover" data-placement="right"
														   data-content="{$HELP_TEXT}">
															<span class="fas fa-info-circle"></span>
														</a>
													{/if}
												{/if}
											</td>
											<td>
												{if isset($ITEM['recommended'])}
													{App\Language::translate($ITEM['recommended'], 'Settings::ConfReport')}
												{else}
													-
												{/if}
											</td>
											<td>
												{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'Settings::ConfReport')}{/if}
											</td>
										</tr>
									{/foreach}
									</tbody>
								</table>
								<br>
								<table class="config-table table u-word-break-all">
									<caption
											class="sr-only">{App\Language::translate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</caption>
									<thead>
									<tr>
										<th>{App\Language::translate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</th>
										<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
										<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$ALL['stability'] key=KEY item=ITEM}
										<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
											<td>
												{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'Settings::ConfReport')}{/if}
												{if !$ITEM['status']}
													{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), 'Settings::ConfReport')}
													{if !empty($HELP_TEXT)}
														<a href="#" class="js-popover-tooltip float-right" data-js="popover"
														   data-trigger="focus hover" data-placement="right"
														   data-content="{$HELP_TEXT}">
															<span class="fas fa-info-circle"></span>
														</a>
													{/if}
												{/if}
											</td>
											<td>
												{if isset($ITEM['recommended'])}
													{App\Language::translate($ITEM['recommended'], 'Settings::ConfReport')}
												{else}
													-
												{/if}
											</td>
											<td colspan="2">
												{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'Settings::ConfReport')}{/if}
											</td>
										</tr>
									{/foreach}
									</tbody>
								</table>
								{if !empty($ALL['database'])}
									<br>
									<table class="config-table table u-word-break-all">
										<caption
												class="sr-only">{App\Language::translate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</caption>
										<thead>
										<tr>
											<th>{App\Language::translate('LBL_PARAMETER', 'Settings::ConfReport')}</th>
											<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
											<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
										</tr>
										</thead>
										<tbody>
										{foreach from=$ALL['database'] key=KEY item=ITEM}
											<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
												<td>
													{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'Settings::ConfReport')}{/if}
													{if !$ITEM['status']}
														{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), 'Settings::ConfReport')}
														{if !empty($HELP_TEXT)}
															<a href="#" class="js-popover-tooltip float-right"
															   data-js="popover"
															   data-trigger="focus hover" data-placement="right"
															   data-content="{$HELP_TEXT}">
																<span class="fas fa-info-circle"></span>
															</a>
														{/if}
													{/if}
												</td>
												{if isset($ITEM['recommended'])}
													<td>
														{$ITEM['recommended']}
													</td>
													<td>
														{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'Settings::ConfReport')}{/if}
													</td>
												{else}
													<td colspan="2">
														{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'Settings::ConfReport')}{/if}
													</td>
												{/if}
											</tr>
										{/foreach}
										</tbody>
									</table>
								{/if}
								<br>
								<table class="config-table table u-word-break-all">
									<caption class="sr-only">
										{App\Language::translate('LBL_PERFORMANCE_VERIFICATION', 'Settings::ConfReport')}
									</caption>
									<thead>
									<tr>
										<th colspan="1" scope="col" class="text-left">
											{App\Language::translate('LBL_PARAMETER', 'Settings::ConfReport')}
										</th>
										<th colspan="1" scope="col">
											{App\Language::translate('LBL_RECOMMENDED', 'Settings::ConfReport')}
										</th>
										<th colspan="1" scope="col">
											{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}
										</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$ALL['performance'] key=KEY item=ITEM}
										<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
											<td>
												{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'Settings::ConfReport')}{/if}
												{if !$ITEM['status']}
													{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), 'Settings::ConfReport')}
													{if !empty($HELP_TEXT)}
														<a href="#" class="js-popover-tooltip float-right" data-js="popover"
														   data-trigger="focus hover" data-placement="right"
														   data-content="{$HELP_TEXT}">
															<span class="fas fa-info-circle"></span>
														</a>
													{/if}
												{/if}
											</td>
											<td>
												{if isset($ITEM['recommended'])}
													{App\Language::translate($ITEM['recommended'], 'Settings::ConfReport')}
												{else}
													-
												{/if}
											</td>
											<td>
												{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'Settings::ConfReport')}{/if}
											</td>
										</tr>
									{/foreach}
									</tbody>
								</table>
								<br>
								<table class="config-table table u-word-break-all">
									<caption class="sr-only">
										{App\Language::translate('LBL_DENY_PUBLIC_DIR_TITLE', 'Settings::ConfReport')}
									</caption>
									<thead>
									<tr>
										<th colspan="1" scope="col" class="text-left">
											{App\Language::translate('LBL_PUBLIC_DIR', 'Settings::ConfReport')}
										</th>
										<th colspan="1" scope="col">
											{App\Language::translate('LBL_DENY_PUBLIC_DIR_STATUS', 'Settings::ConfReport')}
										</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$ALL['publicDirectoryAccess'] key=KEY item=ITEM}
										<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
											<td>
												{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'Settings::ConfReport')}{/if}
												{if !$ITEM['status']}
													{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), 'Settings::ConfReport')}
													{if !empty($HELP_TEXT)}
														<a href="#" class="js-popover-tooltip float-right" data-js="popover"
														   data-trigger="focus hover" data-placement="right"
														   data-content="{$HELP_TEXT}">
															<span class="fas fa-info-circle"></span>
														</a>
													{/if}
												{/if}
											</td>
											<td colspan="2">
												{if $ITEM.status}
													{App\Language::translate('LBL_YES', 'Settings::ConfReport')}
												{else}
													{App\Language::translate('LBL_NO', 'Settings::ConfReport')}
												{/if}
											</td>
										</tr>
									{/foreach}
									</tbody>
								</table>
								<br>
								<table class="config-table table u-word-break-all">
									<caption class="sr-only">
										{App\Language::translate('LBL_ENVIRONMENTAL_INFORMATION', 'Settings::ConfReport')}
									</caption>
									<thead>
									<tr>
										<th colspan="1" scope="col" class="text-left">
											{App\Language::translate('LBL_PARAMETER', 'Settings::ConfReport')}
										</th>
										<th colspan="1" scope="col">
											{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}
										</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$ALL['environment'] key=KEY item=ITEM}
										<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
											<td>
												{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'Settings::ConfReport')}{/if}
												{if !$ITEM['status']}
													{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), 'Settings::ConfReport')}
													{if !empty($HELP_TEXT)}
														<a href="#" class="js-popover-tooltip float-right"
														   data-js="popover"
														   data-trigger="focus hover" data-placement="right"
														   data-content="{$HELP_TEXT}">
															<span class="fas fa-info-circle"></span>
														</a>
													{/if}
												{/if}
											</td>
											<td>
												{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'Settings::ConfReport')}{/if}
											</td>
										</tr>
									{/foreach}
									</tbody>
								</table>
							</div>
						</div>
						<div class="offset2">
							<div>
								{if !empty($ALL['writableFilesAndFolders'])}
									<table class="config-table table u-word-break-all">
										<thead>
										<tr class="blockHeader">
											<th colspan="1" class="mediumWidthType">
												<span>{App\Language::translate('LBL_PATH', 'Settings::ConfReport')}</span>
											</th>
											<th colspan="1" class="mediumWidthType">
												<span>{App\Language::translate('LBL_PERMISSION', 'Settings::ConfReport')}</span>
											</th>
										</tr>
										</thead>
										<tbody>
										{foreach from=$ALL['writableFilesAndFolders'] key=KEY item=ITEM}
											<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
												<td>
													{if empty($ITEM['label'])}{$KEY}{else}{App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'Settings::ConfReport')}{/if}
													{if !$ITEM['status']}
														{assign var="HELP_TEXT" value=\App\Language::translateEncodeHtml('LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY)), 'Settings::ConfReport')}
														{if !empty($HELP_TEXT)}
															<a href="#" class="js-popover-tooltip float-right"
															   data-js="popover"
															   data-trigger="focus hover" data-placement="right"
															   data-content="{$HELP_TEXT}">
																<span class="fas fa-info-circle"></span>
															</a>
														{/if}
													{/if}
												</td>
												<td>
													{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'Settings::ConfReport')}{/if}
												</td>
											</tr>
										{/foreach}
										</tbody>
									</table>
								{/if}
							</div>
						</div>
					</div>
					<div class="form-button-nav fixed-bottom button-container p-1 bg-light">
						<div class="text-center w-100">
							<a class="btn btn-lg c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php"
							   role="button">
								<span class="fas fa-lg fa-arrow-circle-left mr-2"></span>
								{App\Language::translate('LBL_BACK', 'Install')}
							</a>
							<button type="submit" class="btn btn-lg c-btn-block-xs-down btn-primary">
								{App\Language::translate('LBL_NEXT', 'Install')}
								<span class="fas fa-lg fa-arrow-circle-right ml-2"></span>
							</button>
						</div>
					</div>
				</form>
			</div>
		</main>
	</div>
{/strip}
