<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/14/23
 * Time: 5:06 PM
 */

namespace JoseChan\Examination\Admin;


class FormException extends \Exception
{
    protected $field;

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param $field
     * @return FormException
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }


}
