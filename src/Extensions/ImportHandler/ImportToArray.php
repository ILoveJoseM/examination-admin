<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 11:39 PM
 */

namespace JoseChan\Examination\Admin\Extensions\ImportHandler;


use Maatwebsite\Excel\Concerns\ToArray;

class ImportToArray implements ToArray{

    public function array(array $array)
    {
        return $array;
    }

}
