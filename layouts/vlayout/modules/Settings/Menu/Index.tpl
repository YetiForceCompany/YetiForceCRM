{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
<div class="container-fluid menuContainer" style="margin-top:10px;">
	<div class="row-fluid">
		<div class="span7">
			<h3>{vtranslate('LBL_MENU_BUILDER', $QUALIFIED_MODULE)}</h3>{vtranslate('LBL_MENU_BUILDER_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
		<div class="span5">
			<div class="pull-right">
				<select class="select2 span3" name="roleMenu">
					<option value="0" {if $ROLEID eq 0} selected="" {/if}>{vtranslate('LBL_DEFAULT_MENU', $QUALIFIED_MODULE)}</option>
					{foreach item=ROLE key=KEY from=Settings_Roles_Record_Model::getAll()}
						<option value="{$KEY}" {if $ROLEID === $KEY} selected="" {/if}>{vtranslate($ROLE->getName())}</option>
					{/foreach}
				</select>
			</div>
			<div class="pull-right">
				<a class="btn addMenu"><strong>{vtranslate('LBL_ADD_MENU', $QUALIFIED_MODULE)}</strong></a>
			</div>
		</div>
	</div>
	<hr>
	<div class="treeMenuContainer">
		<input type="hidden" id="treeLastID" value="{$LASTID}" />
		<input type="hidden" name="tree" id="treeValues" value='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($DATA))}' />
		<div id="treeContent"></div>
	</div>
</div>
<div class="modal deleteAlert hide" style="width: 400px;">
    <div class="modal-header contentsBackground">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>{vtranslate('LBL_REMOVE_TITLE', $QUALIFIED_MODULE)}</h3>
    </div>
	<div class="modal-body">
		<p>{vtranslate('LBL_REMOVE_DESC', $QUALIFIED_MODULE)}</p>
	</div>
	<div class="modal-footer">
		<div class="pull-left">
			<a class="btn btn-danger" data-dismiss="modal">{vtranslate('LBL_REMOVE', $QUALIFIED_MODULE)}</a>
		</div>
		<div class="pull-right">
			<a class="btn btn-inverse" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
		</div>
	</div>
</div>