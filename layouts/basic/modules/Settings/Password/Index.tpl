{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="">
		<div class="clearfix">
			<form id="PassForm" class="form-horizontal">
				<div class="widget_header form-row mb-2">
					<div class="col-md-12">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
					</div>
				</div>
				<table class="table table-bordered table-sm themeTableColor">
					<thead>
						<tr class="blockHeader"><th colspan="2" class="mediumWidthType">{\App\Language::translate('LBL_Password_Header', $QUALIFIED_MODULE)}</th></tr>
					</thead>
					<tbody>
						<tr>
							<td class="u-w-30per px-2"><label class="muted float-right col-form-label u-text-small-bold">{\App\Language::translate('Minimum password length', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 px-3">
								<div class="col-5 px-0">
									<input class="form-control" type="text" name="min_length" id="min_length"  title="{\App\Language::translate('Minimum password length', $QUALIFIED_MODULE)}" value="{$DETAIL['min_length']}" />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-30per px-2"><label class="muted float-right col-form-label u-text-small-bold">{\App\Language::translate('Maximum password length', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 px-3">
								<div class="col-5 px-0">
									<input class="form-control" type="text" name="max_length" id="max_length" title="{\App\Language::translate('Maximum password length', $QUALIFIED_MODULE)}" value="{$DETAIL['max_length']}" />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-30per px-2"><label class="muted float-right mb-0 col-form-label u-text-small-bold">{\App\Language::translate('Uppercase letters from A to Z', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="big_letters" title="{\App\Language::translate('Uppercase letters from A to Z', $QUALIFIED_MODULE)}" id="big_letters" {if $DETAIL['big_letters'] == 'true' }checked{/if} />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-30per px-2"><label class="muted float-right mb-0 col-form-label u-text-small-bold">{\App\Language::translate('Lowercase letters a to z', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="small_letters" title="{\App\Language::translate('Lowercase letters a to z', $QUALIFIED_MODULE)}" id="small_letters" {if $DETAIL['small_letters'] == 'true'}checked{/if} />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-30per px-2"><label class="muted float-right mb-0 col-form-label u-text-small-bold">{\App\Language::translate('Password should contain numbers', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="numbers" title="{\App\Language::translate('Password should contain numbers', $QUALIFIED_MODULE)}" id="numbers" {if $DETAIL['numbers'] == 'true'}checked{/if} />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-30per px-2"><label class="muted float-right mb-0 col-form-label u-text-small-bold">{\App\Language::translate('Password should contain special characters', $QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0 align-middle">
								<div class="col-5 form-row align-items-center">
									<input type="checkbox" name="special" title="{\App\Language::translate('Password should contain special characters', $QUALIFIED_MODULE)}" id="special"  {if $DETAIL['special'] == 'true'}checked{/if} />
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-30per px-2">
								<label class="muted float-right col-form-label u-text-small-bold">{\App\Language::translate('LBL_PASSWORD_CHANGE_TIME', $QUALIFIED_MODULE)}</label>
							</td>
							<td class="border-left-0">
								<div class="form-row px-3">
									<div class="col-5 px-0">
										<input class="form-control" type="text" name="change_time" id="change_time"  title="{\App\Language::translate('LBL_PASSWORD_CHANGE_TIME', $QUALIFIED_MODULE)}" value="{$DETAIL['change_time']}" />
									</div>
									<div class="col-7">
										<p class="form-control-plaintext"><span class="text-danger fas fa-info-circle js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_PASSWORD_CHANGE_TIME_DESC', $QUALIFIED_MODULE)}<br>{\App\Language::translate('LBL_PASSWORD_SETTING_WARNING', $QUALIFIED_MODULE)}" data-placement="top"></span></p>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class="u-w-30per px-2">
								<label class="muted float-right col-form-label u-text-small-bold">{\App\Language::translate('LBL_TIME_TO_CHANGE_PASSWORD', $QUALIFIED_MODULE)}</label>
							</td>
							<td class="border-left-0">
								<div class="form-row px-3">
									<div class="col-5 px-0">
										<input class="form-control" type="text" name="lock_time" id="lock_time"  title="{\App\Language::translate('LBL_TIME_TO_CHANGE_PASSWORD', $QUALIFIED_MODULE)}" value="{$DETAIL['lock_time']}" />
									</div>
									<div class="col-7">
										<p class="form-control-plaintext"><span class="text-danger fas fa-info-circle js-popover-tooltip" data-js="popover" data-content="{\App\Language::translate('LBL_TIME_TO_CHANGE_PASSWORD_DESC', $QUALIFIED_MODULE)}" data-placement="top"></span></p>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
	</div>
{/strip}
