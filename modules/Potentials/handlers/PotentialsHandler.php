<?php
class PotentialsHandler extends VTEventHandler {
	public function handleEvent($handlerType, $entityData){
		if($handlerType != 'vtiger.entity.aftersave.final') {
			return false;
		}
		$moduleName = $entityData->getModuleName();
		/*
		if ($moduleName == 'Potentials') {
			Potentials_Record_Model::recalculatePotentials( $entityData->getId() );
		}
		*/
		if ($moduleName == 'Invoice') {
			Potentials_Record_Model::recalculatePotentials( $entityData->get('potentialid') );
		}
	}
}
