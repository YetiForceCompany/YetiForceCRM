{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="col-12">
	<hr>
</div>
{if $API_INFO["key"] }
	<div class="col-3 apiAdrress" data-api-name="{$API_NAME}">
		{\App\Language::translate('LBL_USE_OPENCAGE_GEOCODER', $MODULENAME)}:  &nbsp;&nbsp;
		<input type="checkbox" name="nominatim" class="api" {if $API_INFO.nominatim } checked {/if}/>
	</div>
	<div class="col-9">
		<button type="button" class="btn btn-danger delete" id="delete">{\App\Language::translate('LBL_REMOVE_CONNECTION', $MODULENAME)}</button>
		<button type="button" class="btn btn-success save" id="save">{\App\Language::translate('LBL_SAVE', $MODULENAME)}</button>
	</div>
{else}
	<div class="col-6 apiAdrress px-0 mr-4" data-api-name="{$API_NAME}">
		<input name="key" type="text" class="api form-control" placeholder="{\App\Language::translate('LBL_ENTER_KEY_APPLICATION', $MODULENAME)}">
	</div>
	<div class="col-2 ml-4 px-0">
		<a class="btn btn-primary" role="button" href="https://opencagedata.com/api/" target="_blank"
		   rel="noreferrer noopener">{\App\Language::translate('OpenCage Geocoder', $MODULENAME)}</a>
		<button type="button" class="btn btn-success save" id="save">{\App\Language::translate('LBL_SAVE', $MODULENAME)}</button>
	</div>
{/if}