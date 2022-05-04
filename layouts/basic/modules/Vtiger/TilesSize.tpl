{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-TileSize -->
	<div class="d-inline-block mr-sm-1 mb-1 mb-sm-0 c-btn-block-sm-down">
		<button class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
			<span class="fas fa-th-large"></span>
			<span class="textHolder ml-2 js-tile-dropdown-title"> {App\Language::translate('LBL_TILE_'|cat:$TILE_SIZE|upper, $MODULE_NAME)}</span>
			<span class="caret"></span>
		</button>
		<div class="dropdown-menu js-selected-tile-size" data-selected-tile-size="{$TILE_SIZE}">
			<a class="dropdown-item js-tile-size" data-tile-size="very_small" href="#">{\App\Language::translate('LBL_TILE_VERY_SMALL', $MODULE_NAME)}</a>
			<a class="dropdown-item js-tile-size" data-tile-size="small" href="#">{\App\Language::translate('LBL_TILE_SMALL', $MODULE_NAME)}</a>
			<a class="dropdown-item js-tile-size" data-tile-size="medium" href="#"> {\App\Language::translate('LBL_TILE_MEDIUM', $MODULE_NAME)}</a>
			<a class="dropdown-item js-tile-size" data-tile-size="big" href="#"> {\App\Language::translate('LBL_TILE_BIG', $MODULE_NAME)}</a>
		</div>
	</div>
	<!-- /tpl-Base-TileSize -->
{/strip}
