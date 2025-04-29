<?php
namespace alex\form;

use alex\form\lib\services\B24Service;
use alex\form\lib\FieldMapper;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Config\Option;

/**
 * Класс, отправляющий данные с формы в Б24
 */
class FormHandler
{
    /**
     * Событие для отправки данных с формы в B24
     *
     * @param int $formId
     * @param int $resultId
     *
     * @return void
     */
    public static function onAfterResultAdd(int $formId, int $resultId): void
    {
        try {
            $targetFormId = (int)Option::get('alex.form', 'TARGET_FORM_ID');
            if ($formId !== $targetFormId) {
                var_dump('lox');
                return;
            }

            $formData = self::getFormData($resultId);
            $fieldMapper = new FieldMapper(self::getFieldMapping());
            $leadData = $fieldMapper->mapToLeadData($formData);

            $b24Service = new B24Service();
            $b24Service->createLead($leadData);
        } catch (\Exception $e) {
            file_put_contents(
                $_SERVER['DOCUMENT_ROOT'].'/upload/alex_form_errors.log',
                date('Y-m-d H:i:s')." - ".$e->getMessage()."\n".$e->getTraceAsString()."\n\n",
                FILE_APPEND
            );
        }
    }

    /**
     * Маппинг ответов формы
     *
     * @return string[]
     */
    private static function getFieldMapping(): array
    {
        return [
            'NAME' => 'SIMPLE_QUESTION_340',
            'PHONE' => 'SIMPLE_QUESTION_997',
            'EMAIL' => 'SIMPLE_QUESTION_896',
            'COMMENTS' => 'SIMPLE_QUESTION_102'
        ];
    }

    /**
     * Получает данные с формы
     *
     * @throws LoaderException
     * @throws SystemException
     */
    private static function getFormData(int $resultId): array
    {
        if (!Loader::includeModule('form')) {
            throw new SystemException(Loc::getMessage('ALEX_FORM_MODULE_NOT_INSTALLED'));
        }

        $arAnswers = $arQuestions = [];
        \CFormResult::GetDataByID(
            $resultId,
            [],
            $arQuestions,
            $arAnswers,
        );
        $result = [];

        foreach ($arAnswers as $fieldId => $answers) {
            foreach ($answers as $answer) {
                if (!empty($answer['USER_TEXT'])) {
                    $result[$fieldId] = $answer['USER_TEXT'];
                } elseif (!empty($answer['USER_DATE'])) {
                    $result[$fieldId] = $answer['USER_DATE'];
                }
            }
        }

        return $result;
    }
}