{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Log-LogsViewer -->
	<div class="js-logs-container" data-js="container">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="contents" id="listViewContainer">
			<ul class="nav nav-tabs mr-0 mb-2">
				{foreach key=INDEX item=ITEM from=\App\Log::$logsViewerColumnMapping}
					<li class="nav-item">
						<a class="nav-link {if $TYPE === $INDEX} active {/if}" href="index.php?parent=Settings&module=Log&view=LogsViewer&type={$INDEX}" data-type="{$INDEX}">
							{if isset($ITEM['icon'])}
								<span class="{$ITEM['icon']} mr-2"></span>
							{/if}
							{\App\Language::translate($ITEM['label'], $ITEM['labelModule'])}
						</a>
					</li>
				{/foreach}
			</ul>
			<form class="js-filter-form" data-js="container">
				<table class="table table-sm table-striped display text-center mt-2 js-data-table w-100" data-js="dataTable">
					<thead>
						<tr>
							{foreach key=NAME item=ITEM from=$MAPPING['columns']}
								<th data-name="{$NAME}" data-orderable="1">{\App\Language::translate($ITEM['label'], $QUALIFIED_MODULE)}</th>
							{/foreach}
						</tr>
						<tr>
							{assign var=FILTER value=$MAPPING['filter']}
							{foreach item=NAME_COLUMN from=array_keys($MAPPING['columns'])}
								<td>
									{if in_array($NAME_COLUMN, array_keys($FILTER))}
										{include file=\App\Layout::getTemplatePath('Filter/'|cat:$FILTER[$NAME_COLUMN]|cat:'.tpl', $QUALIFIED_MODULE) NAME_FIELD=$NAME_COLUMN TYPE_FIELD=$FILTER[$NAME_COLUMN] QUALIFIED_MODULE=$QUALIFIED_MODULE}
									{/if}
								</td>
							{/foreach}
						</tr>
					</thead>
				</table>
			</form>
		</div>
	</div>
	<!-- /tpl-Settings-Log-LogsViewer -->
{/strip}
