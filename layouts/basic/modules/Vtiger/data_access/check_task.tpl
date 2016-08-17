{strip}
	{assign var=status value=$SAVED_DATA['status']}
	{assign var=name value=$SAVED_DATA['name']}
	{assign var=message value=$SAVED_DATA['message']}
	<div class="row">
		<div class="col-md-12 padding-bottom1per">
			<h5 class="padding-bottom1per"><strong>{vtranslate('Select status', 'DataAccess')}:</strong></h5>
			<select multiple name="status" class="marginLeftZero col-md-6 select2">
				{foreach item=item key=key from=$CONFIG['status']}
					<option value="{$item}" {if $item == $status} selected {/if}{if $item != null && is_array($status) && in_array( $item, $status)} selected {/if} >{vtranslate($item, 'Calendar')}</option>
				{/foreach}
			</select>
		</div>
		<div class="marginLeftZero col-md-12 padding-bottom1per">
			<h5 class="padding-bottom1per"><strong>{vtranslate('Subject tasks', 'DataAccess')}:</strong></h5>
			<input type="text" name="name" class="marginLeftZero col-md-6 " value="{$name}">
		</div>
		<div class="marginLeftZero col-md-12 padding-bottom1per">
			<h5 class="padding-bottom1per"><strong>{vtranslate('Message if the task does not exist', 'DataAccess')}:</strong></h5>
			<input type="text" name="message" class="marginLeftZero col-md-6 " value="{$message}">
		</div>
	</div>
{/strip}
