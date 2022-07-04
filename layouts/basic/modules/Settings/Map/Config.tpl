{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
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
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Coordinates'}active{/if}" href="#Coordinates" data-toggle="tab">
						<span class="fas fa-globe mr-2"></span>{\App\Language::translate('LBL_COORDINATES', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'Routing'}active{/if}" href="#Routing" data-toggle="tab">
						<span class="fas fa-route mr-2"></span>{\App\Language::translate('LBL_ROUTING', $QUALIFIED_MODULE)}
					</a>
				</li>
			</ul>
		</div>
		{function NOTE KEY=''}
			{if $KEY === 'YetiForce'}
				<span class="btn js-popover-tooltip" data-content="{\App\Language::translate('LBL_PAID_FUNCTIONALITY', 'Settings::YetiForce')}">
					<span class="yfi-premium color-red-600"></span>
				</span>
			{else}
				<span class="btn js-popover-tooltip" data-content="{\App\Language::translate('LBL_PROVIDER_NOT_VERIFIED', 'Settings::Map')}">
					<span class="fas fa-triangle-exclamation color-red-600"></span>
				</span>
			{/if}
		{/function}
		<div id="my-tab-content" class="tab-content">
			<div class="tab-pane {if $ACTIVE_TAB eq 'TileLayer'}active{/if}" id="TileLayer">
				<div class="alert alert-info">
					<span class="mdi mdi-information-outline u-fs-2em mr-2 float-left"></span>
					{\App\Language::translate('LBL_TILE_LAYER_INFO_1', $QUALIFIED_MODULE)}<br>
					{\App\Language::translateArgs('LBL_TILE_LAYER_INFO_2', $QUALIFIED_MODULE,'config/Modules/OpenStreetMap.php')}
					<a rel="noreferrer noopener" target="_blank" href="https://wiki.openstreetmap.org/wiki/Tile_servers"> https://wiki.openstreetmap.org/wiki/Tile_servers</a>
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
										<th scope="row">
											{\App\Language::translate($KEY, $QUALIFIED_MODULE)}
											{NOTE KEY=$KEY}
										</th>
										<td>{$ITEM}</td>
										<td class="text-center">
											<input name="tileLayerServers" value="{$KEY}" type="radio" {if $ACTIVE_TILE_LAYER eq $ITEM}checked{/if}>
										</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</form>
			</div>
			<div class="tab-pane {if $ACTIVE_TAB eq 'Coordinates'}active{/if}" id="Coordinates">
				<div class="alert alert-info">
					<span class="mdi mdi-information-outline u-fs-2em mr-2 float-left"></span>
					{\App\Language::translate('LBL_COORDINATES_INFO_1', $QUALIFIED_MODULE)}<br>
					{\App\Language::translateArgs('LBL_COORDINATES_INFO_2', $QUALIFIED_MODULE,'config/Modules/OpenStreetMap.php')}
					<a rel="noreferrer noopener" target="_blank" href="https://wiki.openstreetmap.org/wiki/Search_engines"> https://wiki.openstreetmap.org/wiki/Search_engines</a>
					<br>{\App\Language::translate('LBL_AVAILABLE_DRIVERS', $QUALIFIED_MODULE)}: {implode(',',App\Map\Coordinates::getDrivers())}
				</div>
				<form class="js-validation-form">
					<div class="js-config-table table-responsive" data-js="container">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th class="text-center" scope="col">
										{\App\Language::translate('LBL_PROVIDER_NAME', $QUALIFIED_MODULE)}
									</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_API_URL', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_DRIVER', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_ACTIVE', $QUALIFIED_MODULE)}</th>
								</tr>
							</thead>
							<tbody>
								{foreach from=\App\Config::module('OpenStreetMap', 'coordinatesServers') item=ITEM key=KEY}
									<tr>
										<th scope="row">
											{\App\Language::translate($KEY, $QUALIFIED_MODULE)}
											{NOTE KEY=$KEY}
											{if isset($ITEM['docUrl'])}
												<a href="{$ITEM['docUrl']}" class="float-right u-cursor-pointer js-popover-tooltip" data-placement="top" data-content="{$ITEM['docUrl']}" data-js="popover"><span class="fas fa-info-circle"></span></a>
											{/if}
										</th>
										<td>{$ITEM['apiUrl']}</td>
										<td>{$ITEM['driverName']}</td>
										<td class="text-center">
											<input name="coordinatesServer" value="{$KEY}" type="radio" {if $ACTIVE_COORDINATE eq $KEY}checked{/if}>
										</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</form>
			</div>
			<div class="tab-pane {if $ACTIVE_TAB eq 'Routing'}active{/if}" id="Routing">
				<div class="alert alert-info">
					<span class="mdi mdi-information-outline u-fs-2em mr-2 float-left"></span>
					{\App\Language::translate('LBL_ROUTING_INFO_1', $QUALIFIED_MODULE)}<br>
					{\App\Language::translateArgs('LBL_ROUTING_INFO_2', $QUALIFIED_MODULE,'config/Modules/OpenStreetMap.php')}
					<a rel="noreferrer noopener" target="_blank" href="https://wiki.openstreetmap.org/wiki/Routing/online_routers"> https://wiki.openstreetmap.org/wiki/Routing/online_routers</a>
					<br>{\App\Language::translate('LBL_AVAILABLE_DRIVERS', $QUALIFIED_MODULE)}: {implode(',',App\Map\Routing::getDrivers())}
				</div>
				<form class="js-validation-form">
					<div class="js-config-table table-responsive" data-js="container">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_NAME', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_API_URL', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_DRIVER', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_ACTIVE', $QUALIFIED_MODULE)}</th>
								</tr>
							</thead>
							<tbody>
								{foreach from=\App\Config::module('OpenStreetMap', 'routingServers') item=ITEM key=KEY}
									<tr>
										<th scope="row">
											{\App\Language::translate($KEY, $QUALIFIED_MODULE)}
											{NOTE KEY=$KEY}
											{if isset($ITEM['docUrl'])}
												<a href="{$ITEM['docUrl']}" class="float-right u-cursor-pointer js-popover-tooltip" data-placement="top" data-content="{$ITEM['docUrl']}" data-js="popover"><span class="fas fa-info-circle"></span></a>
											{/if}
										</th>
										<td>{$ITEM['apiUrl']}</td>
										<td>{$ITEM['driverName']}</td>
										<td class="text-center">
											<input name="coordinatesServer" value="{$KEY}" type="radio" {if $ACTIVE_ROUTING eq $KEY}checked{/if}>
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
{/strip}
