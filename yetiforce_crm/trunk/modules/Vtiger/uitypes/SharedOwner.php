<?php
class Vtiger_sharedOwner_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/SharedOwner.tpl';
	}
	public function getListSearchTemplateName() {
        return 'uitypes/SharedOwnerFieldSearchView.tpl';
    }

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($values) {
        if($values == NULL) return;
        foreach(Vtiger_Functions::getArrayFromValue($values) as $value){
			$userModel = Users_Record_Model::getCleanInstance('Users');
			$userModel->set('id', $value);
			$detailViewUrl = $userModel->getDetailViewUrl();
			$currentUser = Users_Record_Model::getCurrentUserModel();
			if(!$currentUser->isAdminUser()){
				return getOwnerName($value);
			}
            $displayvalue[] = "<a href=" .$detailViewUrl. ">" .getOwnerName($value). "</a>&nbsp";
        }
        $displayvalue = implode(',',$displayvalue);
        return $displayvalue;
	}
}