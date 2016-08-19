{strip}
	{assign var=status value=$SAVED_DATA['status']}
	<div class="row">
		<div class="col-md-12 padding-bottom1per">
			<h5 class="padding-bottom1per"><strong>{vtranslate('Select status', 'DataAccess')}:</strong></h5>
			<select multiple name="status" class="marginLeftZero col-md-6 select2">
				{foreach item=item key=key from=$CONFIG['status']}
					<option value="{$item}" {if $item == $status} selected {/if}{if $item != null && is_array($status) && in_array( $item, $status)} selected {/if} >{vtranslate($item, 'ProjectTask')}</option>
				{/foreach}
			</select>
		</div>
	</div>
{/strip}
