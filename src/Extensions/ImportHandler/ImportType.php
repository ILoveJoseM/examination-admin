<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 11:05 PM
 */

namespace JoseChan\Examination\Admin\Extensions\ImportHandler;


class ImportType
{
    const BANK_QUESTION = 1;
    const PAGER_QUESTION = 2;
    const EXAMINEE = 3;
    protected $typeHandler = [
        self::BANK_QUESTION => BankQuestionImport::class,
        self::PAGER_QUESTION => PaperQuestionImport::class,
        self::EXAMINEE => ExamineeImport::class
    ];

    /**
     * @param $type
     * @return AbstractHandler
     */
    public function getTypeHandler($type): ?AbstractHandler
    {
        return isset($this->typeHandler[$type]) ? app($this->typeHandler[$type]) : null;
    }
}
