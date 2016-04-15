{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div  class="modal fade modalNotificationConfig">
		<div class="modal-dialog modal-full">
			<div class="modal-content">
				<div class="modal-header row no-margin">
					<div class="col-xs-12 paddingLRZero">
						<div class="col-xs-8 paddingLRZero">
							<h4>{vtranslate('LBL_WATCHING_MODULES', $MODULE)}</h4>
						</div>
						<div class="pull-right">
							<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
						</div>
					</div>
				</div>
				<div class="modal-body row">
					<div class="col-xs-12">
						{foreach from=$MODULE_LIST key=MODULE_ID item=MODULE_INFO}
							<div class="col-xs-3">
								<div class="checkbox">
									<label>
										<input type="checkbox" {if in_array($MODULE_ID, $WATCHING_MODULES)}checked {/if} class="watchingModule" data-name-Module="{$MODULE_INFO['name']}"> {vtranslate($MODULE_INFO['name'], $MODULE_INFO['name'])}
									</label>
								</div>								
							</div>
						{/foreach}
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
	</div>

{/strip}
