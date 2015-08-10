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
			<div class="modal-header">
				<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
				<h3 class="modal-title">{vtranslate('LBL_SELECT_REPLACE_TREE_ITEM', $QUALIFIED_MODULE)}</h3>
			</div>
			<form class="form-horizontal" method="post" action="javascript:;">
				<div class="modal-body">	
					<div id="treePopupContainer" class="paddingLeftRight10px">
						<div class="paddingLeftRight10px">
							<div class="contentsBackground">
								<div id="treePopupContents"></div>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
</div>

