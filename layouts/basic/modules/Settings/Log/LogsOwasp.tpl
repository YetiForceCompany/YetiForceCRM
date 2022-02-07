{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Log-Index js-log" data-js="container">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents" id="listViewContainer">
			<p>
			<ul class="nav nav-tabs mr-0">
				{assign var=TABLE_MAPPING value=\App\Log::$owaspColumnMapping}
				{foreach key=INDEX item=ITEM from=$TABLE_MAPPING}
					<li class="nav-item">
						<a class="nav-link {if $TYPE === $INDEX}active{/if}"
							href="index.php?parent=Settings&module=Log&view=LogsOwasp&type={$INDEX}"
							data-type="{$INDEX}">{\App\Language::translate('LBL_'|cat:$INDEX|UPPER,$MODULE_NAME)}</a>
					</li>
				{/foreach}
			</ul>
			</p>
			<div class="row">
				<div class="col-lg-3 text-right">
					{\App\Language::translate('LBL_TIME_RANGE')}:
				</div>
				<div class="col-lg-6">
					<div class="input-group input-group-sm js-log-range" data-js="container">
						<div class=" input-group-prepend">
							<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
								<span class="fas fa-calendar-alt"></span>
							</span>
						</div>
						<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}"
							class="dateRangeField js-date-range-filter form-control text-center"
							data-js="val" data-date-format="{$USER_MODEL->get('date_format')}"
							value="{implode(',',\App\Fields\Date::formatRangeToDisplay([date('Y-m-d'),date('Y-m-d')]))}" />
					</div>
				</div>
				<div class="col-lg-3">
					<button type="button"
						class="btn btn-primary btn-sm js-date-range-btn"
						data-js="click">{\App\Language::translate('LBL_FILTER')}</button>
				</div>
			</div>
			<table class="table table-bordered js-data-table" data-js="dataTable"></table>
		</div>
	</div>
{/strip}
