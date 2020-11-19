{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-Log-LogsViewer -->
<div class="js-logs-container" data-js="container">
	<div class="o-breadcrumb widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	{assign var=TABLE_MAPPING value=\App\Log::$logsViewerColumnMapping}
	<div class="contents" id="listViewContainer">
		<ul class="nav nav-tabs mr-0">
			{foreach key=INDEX item=ITEM from=$TABLE_MAPPING}
				<li class="nav-item">
					<a type="button" class="nav-link {if $TYPE === $INDEX} active {/if}"
						data-type="{$INDEX}">{\App\Language::translate($ITEM['label'], $MODULE_NAME)}</a>
				</li>
			{/foreach}
		</ul>
		<div class="row mt-2">
			{foreach key=NAME_FIELD item=TYPE_FIELD from=$TABLE_MAPPING[$TYPE]['filter']}
				{include file=\App\Layout::getTemplatePath('Filter/'|cat:$TYPE_FIELD|cat:'.tpl', $MODULE_NAME) NAME_FIELD=$NAME_FIELD TYPE_FIELD=$TYPE_FIELD}
			{/foreach}
			<div class="col-lg-3">
				<button type="button" class="btn btn-primary btn-sm js-date-btn" data-js="click"> <span class="fas fa-filter mr-2"></span> {\App\Language::translate('LBL_FILTER')}</button>
			</div>
		</div>
		<table class="table table-bordered js-data-table" data-js="dataTable"></table>
	</div>
</div>
<!-- /tpl-Settings-Log-LogsViewer -->
{/strip}
