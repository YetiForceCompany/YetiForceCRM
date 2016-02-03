{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=MAX_ACTIVITES value=$SAVED_DATA['maxActivites']}
	{assign var=LOCK_SAVE value=$SAVED_DATA['lockSave']}
	{assign var=MESSAGE value=$SAVED_DATA['message']}
	<div class="row">
		<div class="col-md-12 padding-bottom1per">
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
{/strip}
