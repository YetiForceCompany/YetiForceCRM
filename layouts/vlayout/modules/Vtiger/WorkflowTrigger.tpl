{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
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
				<div class="pull-right cancelLinkContainer" style="margin-top:0px;">
					<a class="btn btn-default cancelLink" type="reset" style="margin: auto;" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_EXECUTE', $MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
</div>
