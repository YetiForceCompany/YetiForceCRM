<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */

class ReportRunQueryDependencyMatrix
{
    protected $matrix = [];
    protected $computedMatrix = null;

    public function setDependency($table, array $dependents)
    {
        $this->matrix[$table] = $dependents;
    }

    public function addDependency($table, $dependent)
    {
        if (isset($this->matrix[$table]) && !in_array($dependent, $this->matrix[$table])) {
            $this->matrix[$table][] = $dependent;
        } else {
            $this->setDependency($table, [$dependent]);
        }
    }

    public function getDependents($table)
    {
        $this->computeDependencies();

        return isset($this->computedMatrix[$table]) ? $this->computedMatrix[$table] : [];
    }

    protected function computeDependencies()
    {
        if ($this->computedMatrix !== null) {
            return;
        }

        $this->computedMatrix = [];
        foreach ($this->matrix as $key => $values) {
            $this->computedMatrix[$key] = $this->computeDependencyForKey($values);
        }
    }

    protected function computeDependencyForKey($values)
    {
        $merged = [];
        foreach ($values as $value) {
            $merged[] = $value;
            if (isset($this->matrix[$value])) {
                $merged = array_merge($merged, $this->matrix[$value]);
            }
        }

        return $merged;
    }
}
