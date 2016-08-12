{strip}
{assign var=WHAT1 value=$SAVED_DATA['what1']}
{assign var=WHERE1 value=$SAVED_DATA['where1']}
{assign var=WHAT2 value=$SAVED_DATA['what2']}
{assign var=WHERE2 value=$SAVED_DATA['where2']}
{assign var=INFO0 value=$SAVED_DATA['info0']}
{assign var=INFO1 value=$SAVED_DATA['info1']}
{assign var=INFO2 value=$SAVED_DATA['info2']}
{assign var=LOCKSAVE value=$SAVED_DATA['locksave']}
{assign var=VAL2FIELD value=$SAVED_DATA['val2field']}
<div class="col-md-12">
	<div class="checkbox">
		<label class="">
			<input type="checkbox" id="val2field"  name="val2field" class="" value="1"{if $VAL2FIELD eq 1}checked{/if}><strong>{vtranslate('LBL_VALIDATION_TWO_FIELDS', 'DataAccess')}</strong>
		</label>
	</div>
	<br>
	<div class="row">
		<div class="col-md-6">
				<div class="form-group marginLeftZero marginRightZero">
					<label for="what" class="">{vtranslate('Select a field from which the value is to be checked', 'DataAccess')}:</label>
					<select id="what" name="what1" class="form-control select2">
						{foreach item=item key=key from=$CONFIG['fields_mod']}
							<option value="{$item[1]}" {if $item[1] == $WHAT1} selected {/if} >{vtranslate($item[2], $item[0])}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group marginLeftZero marginRightZero">
					<label for="where" class="">{vtranslate('Select the fields to be verified', 'DataAccess')}:</label>
					<select multiple id="where" name="where1" class="form-control select2">
						{foreach item=item key=key from=$CONFIG['fields']}
							{if $last_value neq $item[3]}
								<optgroup label="{vtranslate($item[3], $item[3])}">
							{/if}
							{assign var=selected_val value=$item[1]|cat:"="|cat:$item[2]|cat:"="|cat:$item[4]}
							<option value="{$selected_val}" {if (is_array($WHERE1))?(in_array( $selected_val, $WHERE1)): ($WHERE1 == $selected_val) }selected {/if}>{vtranslate($item[0], $item[3])}</option>
							{assign var=last_value value=$item[3]}
							{if $last_value neq $item[3]}
								</optgroup>
							{/if}
						{/foreach}
					</select>
				</div>

			<div class="form-group marginLeftZero marginRightZero">
				<label for="info0" class="">{vtranslate('LBL_MESSAGE_LOCK0', 'DataAccess')}:</label >
				<input type="text" name="info0" id="info0" class="form-control" value="{$INFO0}">
			</div>
			<div class="form-group marginLeftZero marginRightZero">
				<label for="info1" class="">{vtranslate('LBL_MESSAGE_LOCK1', 'DataAccess')}:</label >
				<input type="text" name="info1" id="info1" class="form-control" value="{$INFO1}">
			</div>
			<div class="form-group marginLeftZero marginRightZero messakgeInfo2{if $VAL2FIELD neq 1} hide{/if}">
				<label for="info2" class="">{vtranslate('LBL_MESSAGE_LOCK2', 'DataAccess')}:</label >
				<input type="text" name="info2" id="info2" class="form-control" value="{$INFO2}">
			</div>
			<div class="form-group marginLeftZero marginRightZero">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="searchTrash"  name="searchTrash" value="1"{if $SAVED_DATA['searchTrash'] eq 1}checked{/if}><strong>{vtranslate('LBL_SEARCH_TRASH', 'DataAccess')}</strong>
				</label>
			</div>
			</div>
			<div class="form-group marginLeftZero marginRightZero">
				<label  class="">{vtranslate('LBL_LOCKS_SAVE', 'DataAccess')}:</label >
				<div class="radio">
					<label>
						<input type="radio" name="locksave" id="locksave1" value="0" {if $LOCKSAVE eq 0}checked{/if}>{vtranslate('LBL_LOCKS_SAVE_LABEL1', 'DataAccess')}
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="locksave" id="locksave2" value="1" {if $LOCKSAVE eq 1}checked{/if}>{vtranslate('LBL_LOCKS_SAVE_LABEL2', 'DataAccess')}
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="locksave" id="locksave3" value="2" {if $LOCKSAVE eq 2}checked{/if}>{vtranslate('LBL_LOCKS_SAVE_LABEL3', 'DataAccess')}
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="locksave" id="locksave4" value="3" {if $LOCKSAVE eq 3}checked{/if}>{vtranslate('LBL_LOCKS_SAVE_LABEL4', 'DataAccess')}
					</label>
				</div>
			</div>
		</div>
		<div class="col-md-6 {if $VAL2FIELD neq 1}hide{/if} val2fieldBlock">
				<div class="form-group marginLeftZero marginRightZero">
					<label for="what2" class="">{vtranslate('Select a field from which the value is to be checked', 'DataAccess')}:</label>
					<select {if $VAL2FIELD neq 1}disabled{/if} id="what2" name="what2" class="form-control select2">
						{foreach item=item key=key from=$CONFIG['fields_mod']}
							<option value="{$item[1]}" {if $item[1] == $WHAT2} selected {/if} >{vtranslate($item[2], $item[0])}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group marginLeftZero marginRightZero">
					<label for="where2" class="">{vtranslate('Select the fields to be verified', 'DataAccess')}:</label>
					<select {if $VAL2FIELD neq 1}disabled{/if} multiple id="where2" name="where2" class="form-control select2">
						{foreach item=item key=key from=$CONFIG['fields']}
							{if $last_value neq $item[3]}
								<optgroup label="{vtranslate($item[3], $item[3])}">
							{/if}
							{assign var=selected_val value=$item[1]|cat:"="|cat:$item[2]|cat:"="|cat:$item[4]}
							<option value="{$selected_val}" {if (is_array($WHERE2))?(in_array( $selected_val, $WHERE2)): ($WHERE2 == $selected_val) }selected {/if}>{vtranslate($item[0], $item[3])}</option>
							{assign var=last_value value=$item[3]}
							{if $last_value neq $item[3]}
								</optgroup>
							{/if}
						{/foreach}
					</select>
				</div>
		</div>
	</div>
</div>
{/strip}
<script type="text/javascript">
jQuery(document).ready(function() {
	$('#val2field').change(function(){
		$('.val2fieldBlock select').select2('destroy');
		if ($(this).attr("checked") == "checked"){
			$('.val2fieldBlock').removeClass('hide');
			$('.val2fieldBlock select').removeAttr('disabled');
			$('#locksave3').removeAttr('disabled');
			$('.messakgeInfo2').removeClass('hide');
		} else {
			$('.val2fieldBlock').addClass('hide');
			$('.val2fieldBlock select').attr('disabled', 'disabled');
			$('#locksave3').attr('disabled', 'disabled');
			$('.messakgeInfo2').addClass('hide');
		}
		app.showSelect2ElementView($('.val2fieldBlock select'));
	});
});
</script>
