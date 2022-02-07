{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Password-Index -->
	<form id="PassForm" class="form-horizontal">
		<div class="o-breadcrumb widget_header form-row mb-2">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'pass'}active{/if}" href="#pass" data-toggle="tab">
						<span class="mdi mdi-lock-question mr-2 u-fs-lg"></span>{\App\Language::translate('LBL_PASSWORD_COMPLEXITY', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'pwned'}active{/if}" href="#pwnedtab" data-toggle="tab">
						<span class="mdi mdi-database-search mr-2 u-fs-lg"></span>{\App\Language::translate('LBL_PWNED_PASSWORD_PROVIDER', $QUALIFIED_MODULE)}
					</a>
				</li>
			</ul>
		</div>
		<div id="my-tab-content" class="tab-content">
			<div class="tab-pane {if $ACTIVE_TAB eq 'pass'}active{/if}" id="pass">
				<div class="alert alert-info">
					<span class="mdi mdi-information-outline mr-2 u-fs-lg float-left"></span>
					{\App\Language::translate('LBL_PASSWORD_COMPLEXITY_INFO', $QUALIFIED_MODULE)}
				</div>
				<table class="table table-bordered table-sm themeTableColor">
					<thead>
						<tr class="blockHeader">
							<th colspan="2" class="mediumWidthType">
								<span class="mdi mdi-form-textbox-password u-fs-lg mr-2"></span>
								{\App\Language::translate('LBL_Password_Header', $QUALIFIED_MODULE)}
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="u-w-37per px-2"><label class="muted float-right col-form-label u-text-small-bold">{\App\Language::translate('Minimum password length', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 px-3">
								<div class="col-5 px-0">
									<input class="form-control" type="number" name="min_length" id="min_length" title="{\App\Language::translate('Minimum password length', $QUALIFIED_MODULE)}" value="{$DETAIL['min_length']}" />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-37per px-2"><label class="muted float-right col-form-label u-text-small-bold">{\App\Language::translate('Maximum password length', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 px-3">
								<div class="col-5 px-0">
									<input class="form-control" type="number" name="max_length" id="max_length" title="{\App\Language::translate('Maximum password length', $QUALIFIED_MODULE)}" value="{$DETAIL['max_length']}" />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-37per px-2"><label class="muted float-right mb-0 col-form-label u-text-small-bold">{\App\Language::translate('Uppercase letters from A to Z', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="big_letters" title="{\App\Language::translate('Uppercase letters from A to Z', $QUALIFIED_MODULE)}" id="big_letters" {if $DETAIL['big_letters'] == 'true' }checked{/if} />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-37per px-2"><label class="muted float-right mb-0 col-form-label u-text-small-bold">{\App\Language::translate('Lowercase letters a to z', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="small_letters" title="{\App\Language::translate('Lowercase letters a to z', $QUALIFIED_MODULE)}" id="small_letters" {if $DETAIL['small_letters'] == 'true'}checked{/if} />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-37per px-2"><label class="muted float-right mb-0 col-form-label u-text-small-bold">{\App\Language::translate('Password should contain numbers', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="numbers" title="{\App\Language::translate('Password should contain numbers', $QUALIFIED_MODULE)}" id="numbers" {if $DETAIL['numbers'] == 'true'}checked{/if} />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-37per px-2"><label class="muted float-right mb-0 col-form-label u-text-small-bold">{\App\Language::translate('Password should contain special characters', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="special" title="{\App\Language::translate('Password should contain special characters', $QUALIFIED_MODULE)}" id="special" {if $DETAIL['special'] == 'true'}checked{/if} />
								</div>
							</td>
						</tr>
						<tr>
							{assign var=DEFAULT_PROVIDER_ACTIVE value=App\Extension\PwnedPassword::getDefaultProvider()->isActive()}
							<td class="u-w-37per px-2 {if !$DEFAULT_PROVIDER_ACTIVE}text-black-50{/if}">
								<label class="muted float-right mb-0 col-form-label u-text-small-bold">
									{\App\Language::translate('LBL_CHECK_PWNED_PASSWORD', $QUALIFIED_MODULE)}
									<span class="fas fa-info-circle float-right u-cursor-pointer text-primary ml-1 js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_PASSWORD_PWNED_INFO', $QUALIFIED_MODULE)}" data-placement="top"></span>
								</label>
							</td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="pwned" title="{\App\Language::translate('LBL_CHECK_PWNED_PASSWORD', $QUALIFIED_MODULE)}" id="pwned" {if $DETAIL['pwned'] == 'true'}checked{/if} {if !$DEFAULT_PROVIDER_ACTIVE}disabled{/if} />
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="table table-bordered table-sm themeTableColor">
					<thead>
						<tr class="blockHeader">
							<th colspan="2" class="mediumWidthType">
								<span class="mdi mdi-lock-reset mr-2 u-fs-lg"></span>
								{\App\Language::translate('LBL_PASSWORD_CHANGE_RULES', $QUALIFIED_MODULE)}
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="u-w-37per px-2">
								<label class="muted float-right col-form-label u-text-small-bold">
									{\App\Language::translate('LBL_PASSWORD_CHANGE_TIME', $QUALIFIED_MODULE)}
									<span class="fas fa-info-circle float-right u-cursor-pointer text-primary ml-1 js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_PASSWORD_CHANGE_TIME_DESC', $QUALIFIED_MODULE)}<br>{\App\Language::translate('LBL_PASSWORD_SETTING_WARNING', $QUALIFIED_MODULE)}" data-placement="top"></span>
								</label>
							</td>
							<td class="border-left-0">
								<div class="form-row px-3">
									<div class="col-5 px-0">
										<input class="form-control" type="number" name="change_time" id="change_time" title="{\App\Language::translate('LBL_PASSWORD_CHANGE_TIME', $QUALIFIED_MODULE)}" value="{$DETAIL['change_time']}" />
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-37per px-2">
								<label class="muted float-right col-form-label u-text-small-bold">
									{\App\Language::translate('LBL_TIME_TO_CHANGE_PASSWORD', $QUALIFIED_MODULE)}
									<span class="fas fa-info-circle float-right u-cursor-pointer text-primary ml-1 js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_TIME_TO_CHANGE_PASSWORD_DESC', $QUALIFIED_MODULE)}" data-placement="top"></span>
								</label>
							</td>
							<td class="border-left-0">
								<div class="form-row px-3">
									<div class="col-5 px-0">
										<input class="form-control" type="number" name="lock_time" id="lock_time" title="{\App\Language::translate('LBL_TIME_TO_CHANGE_PASSWORD', $QUALIFIED_MODULE)}" value="{$DETAIL['lock_time']}" />
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-37per px-2">
								<label class="muted float-right col-form-label u-text-small-bold text-right">{\App\Language::translate('LBL_TIME_TO_CHECK_PWNED', $QUALIFIED_MODULE)}</label>
							</td>
							<td class="border-left-0">
								<div class="form-row px-3">
									<div class="col-5 px-0">
										<input class="form-control" type="number" name="pwned_time" id="pwned_time" title="{\App\Language::translate('LBL_TIME_TO_CHECK_PWNED', $QUALIFIED_MODULE)}" value="{$DETAIL['pwned_time']}" />
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="tab-pane {if $ACTIVE_TAB eq 'pwned'}active{/if}" id="pwnedtab">
				{assign var=ACTIVE_PWNED_PROVIDER value=App\Config::module('Users', 'pwnedPasswordProvider')}
				<div class="alert alert-info">
					<span class="mdi mdi-information-outline mr-2 u-fs-lg float-left"></span>
					{\App\Language::translate('LBL_PASSWORD_PWNED_ALERT', $QUALIFIED_MODULE)}
				</div>
				<div class="js-config-table table-responsive" data-js="container">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_NAME', $QUALIFIED_MODULE)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_URL', $QUALIFIED_MODULE)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_ACTIVE', $QUALIFIED_MODULE)}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=\App\Extension\PwnedPassword::getProviders() item=ITEM key=KEY}
								{assign var=ACTIVE value=!$ITEM->isActive()}
								<tr {if $ACTIVE}disabled{/if}>
									<th scope="row">
										{\App\Language::translate($KEY, $QUALIFIED_MODULE)}
										{if $KEY === 'YetiForce'}<span class="yfi-premium color-red-600 ml-2"></span>{/if}
										{if isset($ITEM->infoUrl)}
											<a href="{$ITEM->infoUrl}" rel="noreferrer noopener" target="_blank" class="float-right">
												<span class="mdi mdi-link-variant"></span>
											</a>
										{/if}
									</th>
									<td>{$ITEM->url}</td>
									<td class="text-center">
										<input name="pwnedProvider" class="pwnedProvider" value="{$KEY}" type="radio" {if $ACTIVE}disabled {/if}{if $ACTIVE_PWNED_PROVIDER eq $KEY}checked {/if}>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</form>
	<!-- /tpl-Settings-Password-Index -->
{/strip}
