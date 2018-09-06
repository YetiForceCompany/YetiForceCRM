{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Log-Index">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents" id="listViewContainer">
			<p>
			<ul class="nav nav-tabs">
				{assign var=TABLE_MAPPING value=\App\Log::$tableColumnMapping}
				{foreach key=INDEX item=ITEM from=$TABLE_MAPPING}
					<li class="nav-item">
						<a class="nav-link {if $TYPE === $INDEX}active{/if}"
						   href="index.php?module=Log&parent=Settings&view=Index&type={$INDEX}"
						   data-type="{$INDEX}">{\App\Language::translate('LBL_'|cat:$INDEX|UPPER,$MODULE_NAME)}</a>
					</li>
				{/foreach}
			</ul>
			</p>
			<table class="table table-bordered js-data-table" data-js="dataTable">
				<thead>
				<tr>
					{foreach item=HEADER from=$TABLE_MAPPING[$TYPE]}
						<th>{\App\Language::translate('LBL_'|cat:$HEADER|UPPER,$MODULE_NAME)}</th>
					{/foreach}
				</tr>
				</thead>
			</table>
		</div>
	</div>
{/strip}
