{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents mt-2">
			<div class="mb-2">
				<span class="mr-2">{\App\Language::translate('LBL_SET_DEFAULT_PHONE_COUNTRY', $QUALIFIED_MODULE)}</span>
				{assign var="DEFAULT_PHONE_COUNTRY" value=\App\Config::component('Phone', 'defaultPhoneCountry')}
				<div class="btn-group btn-group-toggle"
					data-toggle="buttons">
					<label class="btn btn-sm btn-outline-primary {if $DEFAULT_PHONE_COUNTRY} active{/if}">
						<input class="js-switch js-update-get-default-phone-country" type="radio" name="defaultPhoneCountry"
							data-js="change" id="defaultPhoneCountry1" autocomplete="off" value="1"
							{if $DEFAULT_PHONE_COUNTRY}checked{/if}>
						{\App\Language::translate('LBL_DEFAULT_PHONE_FROM_PANEL', $QUALIFIED_MODULE)}
					</label>
					<label class="btn btn-sm btn-outline-primary {if !$DEFAULT_PHONE_COUNTRY} active {/if}">
						<input class="js-switch js-update-get-default-phone-country" type="radio" name="defaultPhoneCountry"
							data-js="change" id="defaultPhoneCountry2" autocomplete="off" value="0"
							{if !$DEFAULT_PHONE_COUNTRY}checked{/if}>
						{\App\Language::translate('LBL_DEFAULT_PHONE_FROM_USER', $QUALIFIED_MODULE)}
					</label>
				</div>
			</div>
			<table class="table tableRWD table-bordered table-sm listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">
						<th width="1%" class="{$WIDTHTYPE}"></th>
						<th class="{$WIDTHTYPE}">{\App\Purifier::encodeHtml(\App\Language::translate('LBL_COUNTRY_NAME',$QUALIFIED_MODULE))}</th>
						<th class="{$WIDTHTYPE}">{\App\Purifier::encodeHtml(\App\Language::translate('LBL_COUNTRY_SHORTNAME',$QUALIFIED_MODULE))}</th>
						<th class="{$WIDTHTYPE} col-md-2 text-center">
							<span class="marginRight10">
								{\App\Purifier::encodeHtml(\App\Language::translate('LBL_ACTIONS',$QUALIFIED_MODULE))}
							</span>
							<span>
								<button class="all-statuses btn btn-light btn-sm js-popover-tooltip" data-js="popover" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_COUNTRY_TOGGLE_ALL_STATUSES', $QUALIFIED_MODULE))}">
									<span class="far fa-check-square"></span>
								</button>
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=ROW  from=Settings_Countries_Record_Model::getAll()}
						<tr class="listViewEntries" data-id="{$ROW['id']}">
							<td width="1%" nowrap class="{$WIDTHTYPE}">
								<span class="fas fa-ellipsis-v" title="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE))}"></span>
							</td>
							<td nowrap class="{$WIDTHTYPE}">
								{\App\Language::translateSingleMod($ROW['name'],'Other.Country')}
							</td>
							<td nowrap class="{$WIDTHTYPE}">
								{\App\Purifier::encodeHtml($ROW['code'])}
							</td>
							<td nowrap class="{$WIDTHTYPE} actionImages">
								<span class="float-right actions">
									<button class="to-bottom btn btn-light btn-sm js-popover-tooltip" data-js="popover" data-placement="left" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_ROW_TO_BOTTOM', $QUALIFIED_MODULE))}">
										<span class="fas fa-arrow-down"></span>
									</button>
								</span>
								<span class="float-right actions">
									<button class="to-top btn btn-light btn-sm marginLeft20 js-popover-tooltip" data-js="popover" data-placement="left" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_ROW_TO_TOP', $QUALIFIED_MODULE))}">
										<span class="fas fa-arrow-up"></span>
									</button>
								</span>

								<span class="float-right actions">
									<button class="mr-1 uitype btn {if !$ROW['uitype']}btn-success{else}btn-danger{/if} btn-sm js-popover-tooltip" data-js="popover" data-uitype="{$ROW['uitype']}" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_VISIBLE_IN_COUNTRY', $QUALIFIED_MODULE))}">
										<span class="far fa-image"></span>
									</button>
								</span>
								<span class="float-right actions">
									<button class="mr-1 phone btn {if !$ROW['phone']}btn-success{else}btn-danger{/if} btn-sm js-popover-tooltip" data-js="popover" data-phone="{$ROW['phone']}" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_VISIBLE_IN_PHONE', $QUALIFIED_MODULE))}">
										<span class="fas fa-mobile-alt"></span>
									</button>
								</span>
								<span class="float-right actions">
									<button class="mr-1 status btn {if !$ROW['status']}btn-success{else}btn-danger{/if} btn-sm js-popover-tooltip" data-js="popover" data-status="{$ROW['status']}" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_COUNTRY_TOGGLE_STATUS', $QUALIFIED_MODULE))}">
										<span class="far fa-check-square"></span>
									</button>
								</span>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
