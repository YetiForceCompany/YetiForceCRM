{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{assign var='INVENTORY_BLOKS' value=$INVENTORY_MODEL->getFields(1,[],'Settings')}
<div class="moduleBlocks inventoryBlock" data-block-id="0">
	<div class="editFieldsTable block panel panel-default">
		<div class="panel-heading">
			<div class="btn-toolbar btn-group-xs pull-right">
				<button class="btn btn-success saveFieldSequence invisible inventorySequence"  type="button">
					<strong>{vtranslate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
				</button>
				<button class="btn btn-default addInventoryField" type="button">
					<strong>{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
				</button>
			</div>
			<div class="panel-title" >
				{vtranslate('LBL_HEADLINE', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="blockFieldsList panel-body">
			<ul name="sortable1" class="connectedSortable list-unstyled">
				{foreach item=FIELD_MODEL key=NAME from=$INVENTORY_BLOKS[0]}
					<li>
						<div class="opacity editFields border1px"  data-id="{$FIELD_MODEL->get('id')}" data-column="{$FIELD_MODEL->get('columnname')}" data-sequence="{$FIELD_MODEL->get('sequence')}" data-name="{$FIELD_MODEL->getName()}">
							<a>
								<img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
							</a>&nbsp;&nbsp;
							<span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</span>
							<span class="btn-group pull-right actions">
								<a href="#" class="editInventoryField">
									<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
								</a>
								<a class="deleteInventoryField"><span title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
							</span>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>
	</div>
</div>
<div class="moduleBlocks inventoryBlock" data-block-id="1">
	<div class="editFieldsTable block panel panel-default">
		<div class="panel-heading">
			<div class="btn-toolbar btn-group-xs pull-right">
				<button class="btn btn-success saveFieldSequence invisible inventorySequence"  type="button">
					<strong>{vtranslate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
				</button>
				<button class="btn btn-default addInventoryField" type="button">
					<strong>{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
				</button>
			</div>
			<div class="panel-title" >
				{vtranslate('LBL_BASIC_VERSE', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="blockFieldsList panel-body">
			<ul name="sortable1" class="connectedSortable list-unstyled">
				{foreach item=FIELD_MODEL key=NAME from=$INVENTORY_BLOKS[1]}
					<li>
						<div class="opacity editFields border1px"  data-id="{$FIELD_MODEL->get('id')}" data-column="{$FIELD_MODEL->get('columnname')}" data-sequence="{$FIELD_MODEL->get('sequence')}" data-name="{$FIELD_MODEL->getName()}">
							<a>
								<img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
							</a>&nbsp;&nbsp;
							<span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</span>
							<span class="btn-group pull-right actions">
								<a href="#" class="editInventoryField">
									<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
								</a>
								<a class="deleteInventoryField"><span title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
							</span>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>
	</div>
</div>
<div class="moduleBlocks inventoryBlock" data-block-id="2">
	<div class="editFieldsTable block panel panel-default">
		<div class="panel-heading">
			<div class="btn-toolbar btn-group-xs pull-right">
				<button class="btn btn-success saveFieldSequence invisible inventorySequence"  type="button">
					<strong>{vtranslate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
				</button>
				<button class="btn btn-default addInventoryField" type="button">
					<strong>{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
				</button>
			</div>
			<div class="panel-title" >
				{vtranslate('LBL_ADDITIONAL_VERSE', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="blockFieldsList panel-body">
			<ul name="sortable1" class="connectedSortable list-unstyled">
				{foreach item=FIELD_MODEL key=NAME from=$INVENTORY_BLOKS[2]}
					<li>
						<div class="opacity editFields border1px"  data-id="{$FIELD_MODEL->get('id')}" data-column="{$FIELD_MODEL->get('columnname')}" data-sequence="{$FIELD_MODEL->get('sequence')}" data-name="{$FIELD_MODEL->getName()}">
							<a>
								<img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
							</a>&nbsp;&nbsp;
							<span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</span>
							<span class="btn-group pull-right actions">
								<a href="#" class="editInventoryField">
									<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
								</a>
								<a class="deleteInventoryField"><span title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
							</span>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>
	</div>
</div>
<li class="hide newLiElement">
	<div class="opacity editFields border1px" data-column="" data-id="" data-sequence="" data-name="">
		<a>
			<img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
		</a>&nbsp;&nbsp;
		<span class="fieldLabel"></span>
		<span class="btn-group pull-right actions">
			<a href="#" class="editInventoryField">
				<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
			</a>
			<a class="deleteInventoryField"><span title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
		</span>
	</div>
</li>
