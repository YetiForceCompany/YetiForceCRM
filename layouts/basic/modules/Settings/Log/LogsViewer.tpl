{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-Log-LogsViewer -->
<div class="js-logs-container" data-js="container">
	<div class="o-breadcrumb widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	{assign var=TABLE_MAPPING value=\App\Log::$logsViewerColumnMapping}
	<div class="contents" id="listViewContainer">
		<ul class="nav nav-tabs mr-0">
			{foreach key=INDEX item=ITEM from=$TABLE_MAPPING}
				<li class="nav-item">
					<a type="button" class="nav-link {if $TYPE === $INDEX} active {/if}"
						data-type="{$INDEX}">{\App\Language::translate($ITEM['label'], $QUALIFIED_MODULE)}</a>
				</li>
			{/foreach}
		</ul>
		<div class="row mt-2">
			<form class="js-filter-form" data-js="container">
				{foreach key=NAME_FIELD item=TYPE_FIELD from=$TABLE_MAPPING[$TYPE]['filter']}
					{include file=\App\Layout::getTemplatePath('Filter/'|cat:$TYPE_FIELD|cat:'.tpl', $QUALIFIED_MODULE) NAME_FIELD=$NAME_FIELD TYPE_FIELD=$TYPE_FIELD}
				{/foreach}
			</form>
			<div class="col-lg-3">
				<button type="button" class="btn btn-primary btn-sm js-date-btn" data-js="click"> <span class="fas fa-filter mr-2"></span> {\App\Language::translate('LBL_FILTER')}</button>
			</div>
		</div>
		<table class="table table-sm table-striped display text-center mt-2 js-data-table" data-js="dataTable">
			<thead>
				<tr>
					{foreach key=NAME item=ITEM from=$TABLE_MAPPING[$TYPE]['columns']}
						<th data-name="{$NAME}" data-orderable="1">{\App\Language::translate($ITEM['label'], $QUALIFIED_MODULE)}</th>
					{/foreach}
				</tr>
			</thead>
		</table>
	</div>
</div>
<!-- /tpl-Settings-Log-LogsViewer -->
{/strip}
