{if $API_INFO["key"] }
	<table cellpadding="10" data-api-name="{$API_INFO.api_name}">
	<tr>
		<td colspan="2"><hr /></td>
	</tr>
	<tr>
		<td>
			{vtranslate('LBL_USE_OPENCAGE_GEOCODER', $MODULENAME)}: 
		</td>
		<td>
			<div style="text-align:center">
				<input type="checkbox" name="nominatim" class="api" {if $API_INFO.nominatim } checked {/if}/>
			</div>
		</td>
	</tr>
	<tr>
		<td >
			<div style="max-width:250px;">
				{vtranslate('LBL_MIN_LOOKUP_LENGHT', $MODULENAME)}: 
			</div>
		</td>
		<td>
			<div style="text-align:center" >
				<input name="min_lenght" type="text" class="api" value="{$API_INFO.min_lenght}" style="width:20px; margin:0 auto;">
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<button type="button" class="btn btn-danger delete" id="delete">{vtranslate('LBL_REMOVE_CONNECTION', $MODULENAME)}</button>
		</td>
		<td>
			<button type="button" class="btn btn-success save" id="save" style="margin:0 auto;">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
		</td>
	</tr>
	</table>
{else}
	<table data-api-name="{$API_INFO.api_name}">
	<tr>
		<td>
			<div class="">
				<input name="key" type="text" class="api" style="margin:0 auto;" placeholder="{vtranslate('LBL_ENTER_KEY_APPLICATION', $MODULENAME)}">
			</div>
		</td>
		<td>
			<div>
				<a class="btn btn-primary" href="https://developer.opencagedata.com/" target="_blank">{vtranslate('OpenCage Geocoder', $MODULENAME)}</a>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<button type="button" class="btn btn-success save" id="save" style="margin:0 auto;">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
		</td>
	</tr>
	</table>
{/if}