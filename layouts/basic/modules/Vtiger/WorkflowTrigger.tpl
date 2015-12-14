{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title">{vtranslate('LBL_WORKFLOWS_TRIGGER', $MODULE)}</h3>
				</div>
				<div class="modal-body">
					{foreach key=KEY item=WORKFLOW from=$WORKFLOWS}
						<div class="row" data-workflow_id="{$WORKFLOW->id}">
							<div class="col-md-1">
								<input type="checkbox"  id="wf_{$WORKFLOW->id}" value="{$WORKFLOW->id}"/>
							</div>
							<div class="col-md-11">
								<label for="wf_{$WORKFLOW->id}">{vtranslate({$WORKFLOW->description},$QUALIFIED_MODULE)}</label>
							</div>
						</div>
					{/foreach}
				</div>
				<div class="modal-footer">
					<div class="pull-right cancelLinkContainer">
						<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_EXECUTE', $MODULE)}</strong></button>
						<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong></button>
					</div>
					<div class="row">
						{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}
						<div class="col-md-5">
							<select class="chzn-select form-control" title="{vtranslate('LBL_USER', $MODULE)}" name="user">
								{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
									<option value="{$OWNER_ID}" {if $USER_MODEL->getId() eq $OWNER_ID} selected {/if}>{$OWNER_NAME}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
