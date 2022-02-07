{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="currencyUpdateContainer">
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		{if $CURRNUM lt 2}
			<div class="alert alert-danger  marginTop10 marginBottom10px marginRight15 marginLeft15">
				<strong>{\App\Language::translate('LBL_WARNING', $QUALIFIED_MODULE)}</strong> {\App\Language::translate('MSG_ONE_CURRENCY', $QUALIFIED_MODULE)}
			</div>
		{/if}
		{if !\App\RequestUtil::isNetConnection()}
			<div class="alert alert-danger marginTop10 marginBottom10px marginRight15 marginLeft15">
				<strong>{\App\Language::translate('LBL_WARNING', $QUALIFIED_MODULE)}</strong> {\App\Language::translate('MSG_NO_NET_CONN', $QUALIFIED_MODULE)}
			</div>
		{/if}
		<form class="form-horizontal" method="post" action="index.php?module={$MODULENAME}&view=Index&parent=Settings">
			<table class="table table-bordered currencyTable">
				<tr>
					<th class="blockHeader" colspan="4">{\App\Language::translate('LBL_SELECT_BANK', $QUALIFIED_MODULE)}</th>
				</tr>
				<tr>
					<td class="fieldLabel">
						<span class="float-right"><strong>{\App\Language::translate('LBL_BANK', $QUALIFIED_MODULE)}:</strong></span>
					</td>
					<td class="fieldValue">
						<div class="row">
							<div class="col-md-5">
								<select name="bank" id="bank" class="select2 form-control">
									{foreach from=$BANK item=key}
										<option value="{$key.id}" {if $key.active eq '1'}selected{/if} data-name="{$key.bank_name}">{\App\Language::translate($key.bank_name, $QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-md-7 btn-toolbar justify-content-end">
								{*<button class="btn btn-success float-right" name="save" type="submit"><strong>{\App\Language::translate('LBL_SET_DEFAULT_BANK', $QUALIFIED_MODULE)}</strong></button>*}
								{if count($SUPPORTED_CURRENCIES) gt 0}
									<button class="btn btn-info"
										id="supportedCurrencies"
										title="{\App\Language::translate('LBL_CURRENCIES_SUPPORTED', $QUALIFIED_MODULE)}"
										type="button"><span class="fas fa-info-circle"></span></button>
								{/if}
								{if count($UNSUPPORTED_CURRENCIES) gt 0}
									<button class="btn btn-danger ml-1"
										id="unsupportedCurrencies"
										title="{\App\Language::translate('LBL_CURRENCIES_UNSUPPORTED', $QUALIFIED_MODULE)}"
										type="button"><span class="fas fa-exclamation-triangle"></span></button>
								{/if}
							</div>
						</div>
					</td>
				</tr>
				<tr id="infoBlock" class="d-none">
					<td colspan="4">
						<div class="alert alert-info">
							<h4>{\App\Language::translate('LBL_CURRENCIES_SUPPORTED', $QUALIFIED_MODULE)}:</h4>
							<span id='infoSpan'>
								{foreach from=$SUPPORTED_CURRENCIES key=NAME item=CODE}
									<p><strong>{\App\Language::translate($NAME, $QUALIFIED_MODULE)}</strong> - {$CODE}</p>
								{/foreach}
							</span>
						</div>
					</td>
				</tr>
				<tr id="alertBlock" class="d-none">
					<td colspan="4">
						<div class="alert alert-danger">
							<h4>{\App\Language::translate('LBL_CURRENCIES_UNSUPPORTED', $QUALIFIED_MODULE)}:</h4>
							<span id='alertSpan'>
								{foreach from=$UNSUPPORTED_CURRENCIES key=NAME item=CODE}
									<p><strong>{\App\Language::translate($NAME, $QUALIFIED_MODULE)}</strong> - {$CODE}</p>
								{/foreach}
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<th class="blockHeader" colspan="4">{\App\Language::translate('LBL_HISTORY', $QUALIFIED_MODULE)}</th>
				</tr>
				<tr>
					<td class="fieldLabel">
						<span class="float-right"><strong>{\App\Language::translate('LBL_CAL_DATE', $QUALIFIED_MODULE)}:</strong></label>
					</td>
					<td class="fieldValue">
						<div class="input-group">
							<div class=" input-group-prepend">
								<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
									<span class="fas fa-calendar-alt"></span>
								</span>
							</div>
							<input id="datepicker" type="text" class="form-control dateField" name="duedate" data-date-format="{$USER_MODEL->get('date_format')}" value="{$DATE}" />
							<span class="input-group-append">
								<button class="btn btn-success" name="download" value="download" type="submit">{\App\Language::translate('LBL_SHOW', $QUALIFIED_MODULE)}</button>
							</span>
						</div>
					</td>
				</tr>
			</table>
			<p></p>
			<div class="alert alert-info alert-block">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<h4>{\App\Language::translate('LBL_INFORMATIONS', $QUALIFIED_MODULE)}:</h4>
				<p><strong>{\App\Language::translate('LBL_MAINCURRENCY', $QUALIFIED_MODULE)}:</strong> {\App\Language::translate($MAINCURR['currency_name'], $QUALIFIED_MODULE)}, <strong>{\App\Language::translate('LBL_CODE', $QUALIFIED_MODULE)}:</strong> {$MAINCURR['currency_code']}, <strong>{\App\Language::translate('LBL_SYMBOL', $QUALIFIED_MODULE)}</strong>: {$MAINCURR['currency_symbol']}</p>
			</div>
			<table class="table table-bordered tableRWD">
				<thead>
					<tr>
						<th class="blockHeader">{\App\Language::translate('LBL_CURRENCY_NAME', $QUALIFIED_MODULE)}</td>
						<th class="blockHeader">{\App\Language::translate('LBL_CURRENCY_SYMBOL', $QUALIFIED_MODULE)}</td>
						<th class="blockHeader">{\App\Language::translate('LBL_COURSE', $QUALIFIED_MODULE)}</td>
						<th class="blockHeader">{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</td>
						<th class="blockHeader">{\App\Language::translate('LBL_ACTUAL_DATE_OF_COURSE', $QUALIFIED_MODULE)}</td>
					</tr>
				</thead>
				{foreach from=$HISTORIA item=key}
					<tr>
						<td>{\App\Language::translate($key.currency_name, 'Settings:Currency')} ({$key.currency_code})</td>
						<td>
							{if $USER_MODEL->get('currency_symbol_placement') eq '$1.0'}
								{$key.currency_symbol}
							{/if}
							1
							{if $USER_MODEL->get('currency_symbol_placement') eq '1.0$'}
								{$key.currency_symbol}
							{/if}
						</td>
						<td>
							{if $USER_MODEL->get('currency_symbol_placement') eq '$1.0'}
								{$MAINCURR['currency_symbol']}
							{/if}
							{number_format($key.exchange, 4, $USER_MODEL->get('currency_decimal_separator'), $USER_MODEL->get('currency_grouping_separator'))}
							{if $USER_MODEL->get('currency_symbol_placement') eq '1.0$'}
								{$MAINCURR['currency_symbol']}
							{/if}
						</td>
						<td>{DateTimeField::convertToUserFormat($key.fetch_date)}</td>
						<td>{DateTimeField::convertToUserFormat($key.exchange_date)}</td>
					</tr>
				{/foreach}
			</table>

		</form>
	</div>
{/strip}
