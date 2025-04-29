<?php

namespace alex\form\lib;

class FieldMapper
{
    private array $mapping;

    /**
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Подготавливает данные для отправки в B24
     *
     * @param array $formData
     *
     * @return string[]
     */
    public function mapToLeadData(array $formData): array
    {
        $leadData = ['TITLE' => 'Заявка с сайта ' . date('d.m.Y H:i')];

        foreach ($this->mapping as $b24Field => $formField) {
            if (isset($formData[$formField])) {
                $leadData[$b24Field] = $formData[$formField];
            }
        }

        return $leadData;
    }
}
