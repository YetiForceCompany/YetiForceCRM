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
	<main class="main-container">
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
									<tr {if !$ITEM['status']}class="table-danger font-weight-bold"{/if}>
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
									<tr {if !$ITEM['status']}class="table-danger font-weight-bold"{/if}>
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
						</div>
					</div>
					<div class="offset2">
						<div>
							{assign var="STABILITY_CONF" value=$STABILITY_CONF}
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
								{foreach from=$STABILITY_CONF key=key item=item}
									<tr {if $item.incorrect}class="table-danger font-weight-bold"{/if}>
										<td>
											<span>{$key}</span>
											{if isset($item.help)}
												<a class="js-popover-tooltip float-right"
												   tabindex="0"
												   role="button"
												   title="{App\Language::translate('LBL_SHOW_INVENTORY_ROW')}"
												   data-trigger="focus"
												   data-js="popover" data-placement="top"
												   data-content="{App\Language::translate($item.help, 'Settings::ConfReport')}">
													<span class="sr-only">{App\Language::translate('LBL_SHOW_INVENTORY_ROW')}</span>
													<span class="fas fa-info-circle"></span>
												</a>
											{/if}
										</td>
										<td>
											<span>{App\Language::translate($item.recommended, 'Settings::ConfReport')}</span>
										</td>
										<td>
											<span>{App\Language::translate($item.current, 'Settings::ConfReport')}</span>
										</td>
									</tr>
								{/foreach}
								</tbody>
							</table>
							{if $DB_CONF}
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
									{foreach from=$DB_CONF key=key item=item}
										<tr {if $item['status']}class="table-danger font-weight-bold"{/if}>
											<td>
												<span>{App\Language::translate($key, 'Settings::ConfReport')}</span>
												{if isset($item.help)}
													<a class="js-popover-tooltip float-right"
													   tabindex="0"
													   role="button"
													   title="{App\Language::translate('LBL_SHOW_INVENTORY_ROW')}"
													   data-js="popover"
													   data-trigger="focus"
													   data-placement="right"
													   data-content="{\App\Language::translateEncodeHtml($item.help, 'Settings::ConfReport')}">
														<span class="sr-only">{App\Language::translate('LBL_SHOW_INVENTORY_ROW')}</span>
														<span class="fas fa-info-circle"></span></a>
												{/if}
											</td>
											{if $item['recommended'] === false}
												<td colspan="2">
													<span>{$item['current']}</span>
												</td>
											{else}
												<td><span>{$item['recommended']}</span></td>
												<td><span>{$item['current']}</span></td>
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
												<span class="marginRight5px">{App\Language::translate($key, 'Settings::ConfReport')}</span>
											</td>
											<td width="23%">
												<span class="marginRight5px">{App\Language::translate($item.path, 'Settings::ConfReport')}</span>
											</td>
											<td width="23%">
												<span class="marginRight5px">
													{if $item.permission eq 'FailedPermission'}
														{App\Language::translate('LBL_FAILED_PERMISSION', 'Settings::ConfReport')}
													{else}
														{App\Language::translate('LBL_TRUE_PERMISSION', 'Settings::ConfReport')}
													{/if}
												</span>
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
						<a class="btn c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php"
						   role="button">
							<span class="fas fa-arrow-circle-left mr-1"></span>
							{App\Language::translate('LBL_BACK', 'Install')}
						</a>
						<button type="submit" class="btn c-btn-block-xs-down btn-primary">
							<span class="fas fa-arrow-circle-right mr-1"></span>
							{App\Language::translate('LBL_NEXT', 'Install')}
						</button>
					</div>
				</div>
			</form>
		</div>
	</main>
{/strip}
