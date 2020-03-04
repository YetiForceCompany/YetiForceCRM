{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Settings-Map-Config -->
<div>
    <div class="o-breadcrumb widget_header row mb-2">
        <div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
    </div>
    <div>
        <ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
			<li class="nav-item">
				<a class="nav-link {if $ACTIVE_TAB eq 'TileLayer'}active{/if}" href="#TileLayer" data-toggle="tab">
					<span class="fas fa-layer-group mr-2"></span>{\App\Language::translate('LBL_TILE_LAYER', $QUALIFIED_MODULE)}
				</a>
			</li>
		</ul>
    </div>
	<div id="my-tab-content" class="tab-content">
		<div class="tab-pane {if $ACTIVE_TAB eq 'TileLayer'}active{/if}" id="TileLayer">
		<div class="alert alert-info">
			<span class="mdi mdi-information-outline"></span>
			{\App\Language::translateArgs('LBL_TILE_LAYER_INFO', $QUALIFIED_MODULE,'config/Modules/OpenStreetMap.php')}
			<a rel="noreferrer noopener" target="_blank" href="https://wiki.openstreetmap.org/wiki/Tile_servers">https://wiki.openstreetmap.org/wiki/Tile_servers</a>
		</div>
			<form class="js-validation-form">
				<div class="js-config-table table-responsive" data-js="container">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_NAME', $QUALIFIED_MODULE)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_TILE_LAYER_URL_TEMPLATE', $QUALIFIED_MODULE)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_ACTIVE', $QUALIFIED_MODULE)}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=\App\Config::module('OpenStreetMap', 'tileLayerServers') item=ITEM key=KEY}
								<tr>
									<th scope="row">{\App\Language::translate($KEY, $QUALIFIED_MODULE)}</th>
									<td>{$ITEM}</td>
									<td class="text-center">
										<input name="default_provider" value="{$KEY}" type="radio" {if $DEFAULT_PROVIDER eq $ITEM}checked{/if}>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /tpl-Settings-Map-Config -->
