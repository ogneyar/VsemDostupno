<?php

namespace app\helpers;

use yii\grid\DataColumn;

class NumberColumn extends DataColumn
{
    private $_total = 0;
    
    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        $this->_total += $value;
        return $value;
    }
    
    protected function renderFooterCellContent()
    {
        return number_format($this->_total, 2, '.', '');
    }
}