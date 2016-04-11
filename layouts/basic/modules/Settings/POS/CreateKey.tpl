{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-content">
		<div class="modal-header row no-margin">
			<div class="col-xs-12 paddingLRZero">
				<div class="col-xs-8 paddingLRZero">
					{if $RECORD_MODEL}
						<h4>{vtranslate('LBL_TITLE_EDIT', $QUALIFIED_MODULE)}</h4>
					{else}
						<h4>{vtranslate('LBL_TITLE_ADDED', $QUALIFIED_MODULE)}</h4>
					{/if}
				</div>
				<div class="pull-right">
					<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
				</div>
			</div>
		</div>
		<div class="modal-body row">
			<div class="col-xs-12 marginBottom10px">
				<div class="col-xs-4 fieldLabel">
					{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}
				</div>
				<div class="col-xs-8">
					<select class="select2 users" {if $RECORD_MODEL} disabled {/if}>
						{foreach from=$LIST_USERS item=USER_MODEL}
							<option value="{$USER_MODEL->get('id')}"
								{if $RECORD_MODEL && $USER_MODEL->get('id') eq  $RECORD_MODEL->get('user_id')}
									selected	
								{/if}
								>
								{$USER_MODEL->getName()}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="col-xs-12">
				<div class="col-xs-4 fieldLabel">
					{vtranslate('LBL_ACTION', $QUALIFIED_MODULE)}
				</div>
				<div class="col-xs-8">
					<select multiple class="select2 actionPos">
						{foreach from=$LIST_ACTIONS key=ACTION_ID item=ACTION}
							<option value="{$ACTION_ID}" {if $RECORD_MODEL && in_array($ACTION_ID, $RECORD_MODEL->get('action'))} selected {/if}>
								{vtranslate($ACTION['label'], $QUALIFIED_MODULE)}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path}
	</div>
{/strip}
