{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=MAX_ACTIVITES value=$SAVED_DATA['maxActivites']}
	{assign var=LOCK_SAVE value=$SAVED_DATA['lockSave']}
	{assign var=MESSAGE value=$SAVED_DATA['message']}
	{assign var=STATUS value=$SAVED_DATA['status']}
	{assign var=STATUS_TYPE value=$SAVED_DATA['statusType']}
	{if !is_array($STATUS)}{assign var=STATUS value=[$STATUS]}{/if}
	<hr>
	<label>{vtranslate('LBL_SELECT_OPTION_TO_SEARCH', 'DataAccess')}:</label>
	<div class="form-group text-center">
		<div class="radio-inline">
			<label>
				<input type="radio" name="statusType" data-hide="getFromPanelMapp"  data-show="createOwnMapp" id="optionsRadios1" value="0" {if !$STATUS_TYPE}checked{/if}>
				{vtranslate('LBL_SET_CUSTOM_CONDITIONS', 'DataAccess')}
			</label>
		</div>
		<div class="radio-inline">
			<label>
				<input type="radio" name="statusType" data-hide="createOwnMapp"  data-show="getFromPanelMapp" id="optionsRadios2" value="1"{if $STATUS_TYPE eq 1} checked{/if}>
				{vtranslate('LBL_CURRENT_EVENTS', 'DataAccess')}
			</label>
		</div>
		<div class="radio-inline">
			<label>
				<input type="radio" name="statusType" data-hide="createOwnMapp"  data-show="getFromPanelMapp" id="optionsRadios2" value="2"{if $STATUS_TYPE eq 2} checked{/if}>
				{vtranslate('LBL_PAST_EVENTS', 'DataAccess')}
			</label>
		</div>
	</div>
	<div class="form-group marginLeftZero marginRightZero statusContainer{if $STATUS_TYPE} hide{/if}">
		<label for="status" class="">{vtranslate('LBL_SELECT_STATUS', 'DataAccess')}:</label>
		<select multiple id="status" name="status" class="form-control select2">
			{foreach item=ITEM from=Calendar_Module_Model::getComponentActivityStateLabel()}
				<option value="{$ITEM}" {if in_array($ITEM, $STATUS)}selected {/if}>{vtranslate($ITEM, 'Calendar')}</option>
			{/foreach}
		</select>
	</div>		
	<hr><br>
	<div class="row">
		<div class="col-md-4 padding-bottom1per">
			<label class="padding-bottom1per"><strong>{vtranslate('LBL_MAXIMUM_NUMBER_EVENTS_PER_DAY', 'DataAccess')}:</strong></label>
			<input type="text" name="maxActivites" class="marginLeftZero col-md-6 form-control" value="{$MAX_ACTIVITES}">
		</div>
		<div class="col-md-12 padding-bottom1per checkbox">
			<label for="lockSave" class="">
				<input type="checkbox" name="lockSave" id="lockSave" class="" value="1" {if $LOCK_SAVE eq 1}checked="checked"{/if}>
				{vtranslate('LBL_LOCKS_SAVE', 'DataAccess')}
			</label >
		</div>
		<div class="marginLeftZero col-md-12 padding-bottom1per">
			<h5 class="padding-bottom1per"><strong>{vtranslate('Message', 'DataAccess')}:</strong></h5>
			<input type="text" name="message" class="marginLeftZero col-md-6 form-control" value="{$MESSAGE}">
		</div>
	</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	var statusContainer = jQuery('.statusContainer');
	$('[name="statusType"]').on('change', function(){
		if ($(this).val() != 0){
			statusContainer.addClass('hide').find('select').prop('disabled', true);
		} else {
			statusContainer.removeClass('hide').find('select').prop('disabled', false);
		}
	});
});
</script>
{/strip}
