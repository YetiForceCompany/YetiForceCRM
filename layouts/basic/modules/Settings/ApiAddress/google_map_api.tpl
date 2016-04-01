{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<hr>
{if $API_INFO["key"] }
	<div class="col-xs-3 apiAdrress" data-api-name="{$API_NAME}">
		{vtranslate('LBL_USE_GOOGLE_GEOCODER', $MODULENAME)}: &nbsp;&nbsp;
		<input type="checkbox" name="nominatim" class="api" {if $API_INFO.nominatim } checked {/if}/>
	</div>
	<div class="col-xs-9">
		<button type="button" class="btn btn-danger delete" id="delete">{vtranslate('LBL_REMOVE_CONNECTION', $MODULENAME)}</button>
		<button type="button" class="btn btn-success save" id="save" >{vtranslate('LBL_SAVE', $MODULENAME)}</button>
	</div>
{else}
	<div class="col-xs-6 apiAdrress paddingLRZero" data-api-name="{$API_NAME}">
		<input name="key" type="text" class="api form-control" placeholder="{vtranslate('LBL_ENTER_KEY_APPLICATION', $MODULENAME)}">
	</div>
	<div class="col-xs-6">
		<a class="btn btn-primary" href="https://code.google.com/apis/console/?noredirect" target="_blank">{vtranslate('Google Geocoder', $MODULENAME)}</a>
		<button type="button" class="btn btn-success save" id="save">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
	</div>
{/if}
