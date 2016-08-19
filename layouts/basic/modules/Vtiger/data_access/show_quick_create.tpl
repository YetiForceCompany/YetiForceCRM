{strip}
{assign var=modules value=$SAVED_DATA['modules']}
<div class="row">
	<div class="col-md-12 padding-bottom1per">
		<h5 class="padding-bottom1per"><strong>{vtranslate('LBL_SELECT_OPTION', 'DataAccess')}:</strong></h5>
		<select name="modules" class="marginLeftZero col-md-6 select2">
			{foreach item=item key=key from=$CONFIG['modules']}
				<option value="{$key}" {if $key == $modules} selected {/if} >{vtranslate($item, $key)}</option>
			{/foreach}
		</select>
	</div>
</div>
{/strip}
