{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<select name="module" class="select2 type form-control">
			{foreach from=$MODULE_MODEL->getModulesList() item=ITEM}
				<option value="{$ITEM['tabid']}" {if $RECORD && $ITEM['tabid'] == $RECORD->get('module')} selected="" {/if}>{vtranslate($ITEM['name'], $ITEM['name'])}</option>
			{/foreach}
		</select>
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" />
	</div>
</div>
{include file='fields/Newwindow.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{assign var=FILTERS value=explode(',',$RECORD->get('filters'))}
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_AVAILABLE_FILTERS', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<select name="filters" multiple class="select2 type form-control">
			{foreach from=$MODULE_MODEL->getCustomViewList() item=ITEM}
				<option value="{$ITEM.cvid}" {if $RECORD && in_array($ITEM['cvid'],$FILTERS)} selected="" {/if} data-tabid="{$ITEM['tabid']}">{vtranslate($ITEM['viewname'], $ITEM['entitytype'])}</option>
			{/foreach}
		</select>
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_ICON_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<div class="input-group">
			<input name="icon" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('icon')}{/if}"/>
			<span class="input-group-btn">
				<button id="selectIconButton" class="btn btn-default" title="{vtranslate('LBL_SELECT_ICON',$QUALIFIED_MODULE)}" type="button"><span class="glyphicon glyphicon-info-sign"></span></button>
			</span>
		</div>
	</div>
</div>
