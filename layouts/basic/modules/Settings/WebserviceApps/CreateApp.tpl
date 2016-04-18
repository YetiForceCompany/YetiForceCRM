{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-content validationEngineContainer">
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
					{vtranslate('LBL_APP_NAME', $QUALIFIED_MODULE)}
				</div>
				<div class="col-xs-8">
					<input type="text" name="name" data-validation-engine="validate[required]" value="{if $RECORD_MODEL}{$RECORD_MODEL->getName()}{/if}" class="form-control">
				</div>
			</div>
			<div class="col-xs-12 marginBottom10px">
				<div class="col-xs-4 fieldLabel">
					{vtranslate('LBL_ADDRESS_URL', $QUALIFIED_MODULE)}
				</div>
				<div class="col-xs-8">
					<input type="text" name="addressUrl" value="{if $RECORD_MODEL}{$RECORD_MODEL->get('acceptable_url')}{/if}" class="form-control">
				</div>
			</div>
			<div class="col-xs-12 marginBottom10px">
				<div class="col-xs-4 fieldLabel">
					{vtranslate('Status', $QUALIFIED_MODULE)}
				</div>
				<div class="col-xs-8">
					<input type="checkbox" {if $RECORD_MODEL && $RECORD_MODEL->get('status') eq 1}checked{/if} name="status">
				</div>
			</div>
			<div class="col-xs-12 marginBottom10px">
				<div class="col-xs-4 fieldLabel">
					{vtranslate('LBL_TYPE_SERVER', $QUALIFIED_MODULE)}
				</div>
				<div class="col-xs-8">
					<select class="select2 typeServer" {if $RECORD_MODEL} disabled {/if}>
						{foreach from=$TYPES_SERVERS item=TYPE}
							<option value="{$TYPE}"
								{if $RECORD_MODEL && $TYPE eq  $RECORD_MODEL->get('type')}
									selected	
								{/if}
								>
								{$TYPE}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path}
	</div>
{/strip}
