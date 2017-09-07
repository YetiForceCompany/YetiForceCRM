{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<div class="row padding1per contentsBackground no-margin" style="border:1px solid #ccc;box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);">
    <form class="form-horizontal" method="post" action="index.php">
        <input type="hidden" name="view" value="Step2" />
        <input type="hidden" name="module" value="{$MODULE_NAME}" />
        <input type="hidden" name="parent" value="Settings" />
        {if $TPL_ID}
        <input type="hidden" name="tpl_id" value="{$TPL_ID}" />
        {/if}
    <div class="padding1per" style="border:1px solid #ccc;">
        <label>
            <strong>{\App\Language::translate('LBL_STEP_1',$QUALIFIED_MODULE)}: {\App\Language::translate('LBL_ENTER_BASIC_INFO',$QUALIFIED_MODULE)}</strong>
        </label>
        <br />
        <div class="form-group">
            <div class="col-md-3 control-label">
                {\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
            </div>
            <div class="col-md-6 controls">
                {if $MODE eq 'edit'}
                    <input type='text' disabled='disabled' value="{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}" >
                    <input type='hidden' name='module_name' value="{$MODULE_MODEL->get('name')}" >
                {else}
					<select class="chzn-select form-control" id="moduleName" name="base_module" required="true" data-placeholder="Select Module..." {if $TPL_ID}readonly{/if}>
						{if $TPL_ID}
							<option value="{$BASE_INFO['module_name']}" {if $BASE_INFO['module_name'] eq $item} selected {/if}>{\App\Language::translate($BASE_INFO['module_name'], $BASE_INFO['module_name'])}</option>
							
						{else}
							{foreach from=$MODULE_LIST item=item key=key}
								<option value="{$item}" {if $BASE_INFO['module_name'] eq $item} selected {/if}>{\App\Language::translate($item, $item)}</option>
							{/foreach}
						{/if}
                    </select>
                {/if}
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-3 control-label">
                {\App\Language::translate('DOC_NAME', $QUALIFIED_MODULE)}<span class="redColor">*</span>
            </div>
            <div class="col-md-6 controls">
                <input type="text" name="summary" class="form-control" data-validation-engine='validate[required]' value="{$BASE_INFO['summary']}" id="summary" />
            </div>
        </div>
    </div>
    <br />
    <div class="pull-right">
        <button class="btn btn-success" id="next_step">{\App\Language::translate('NEXT', $QUALIFIED_MODULE)}</button>
        <a href="index.php?module={$MODULE_NAME}&parent=Settings&view=Index" class="cancelLink btn btn-warning">{\App\Language::translate('CANCEL', $QUALIFIED_MODULE)}</a>
    </div>
    </form>
</div> 
