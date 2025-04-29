<?php

namespace alex\form\lib\services;

use alex\form\lib\rest\B24Client;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

class B24Service
{
    private const DEFAULT_LEAD_TITLE = 'Заявка с сайта';

    /**
     * @var B24Client
     */
    private B24Client $client;

    /**
     * Инициализирует сервис для работы с Bitrix24 API
     */
    public function __construct()
    {
        $webhookUrl = Option::get('alex.form', 'BITRIX_24_WEBHOOK_URL');

        if (empty($webhookUrl)) {
            throw new \RuntimeException(
                Loc::getMessage('ALEX_FORM_WEBHOOK_NOT_CONFIGURED')
            );
        }

        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException(
                "Некорректный URL вебхука: ".$webhookUrl
            );
        }

        $this->client = new B24Client($webhookUrl);
    }

    /**
     * Создает лид в Bitrix24
     *
     * @param array $leadData
     *
     * @return int
     */
    public function createLead(array $leadData): int
    {
        try {
            $response = $this->client->sendLead(
                $this->prepareLeadData($leadData)
            );

            return (int)$response['result'];

        } catch (ArgumentException $e) {
            throw new \RuntimeException(
                Loc::getMessage('ALEX_FORM_LEAD_CREATION_ERROR'),
                0,
                $e
            );
        }
    }

    /**
     * Подготавливает данные лида для отправки в Bitrix24
     *
     * @param array $data Входные данные
     *
     * @return array Подготовленные данные
     */
    private function prepareLeadData(array $data): array
    {
        return [
            'TITLE' => $data['TITLE'] ?? self::DEFAULT_LEAD_TITLE,
            'NAME' => $this->prepareName($data['NAME'] ?? ''),
            'EMAIL' => $this->prepareEmail($data['EMAIL'] ?? ''),
            'COMMENTS' => $this->prepareComments($data['COMMENTS'] ?? ''),
            'SOURCE_ID' => 'WEB',
            'SOURCE_DESCRIPTION' => 'Форма на сайте'
        ];
    }

    /**
     * Подготавливает параметр имени для лида
     *
     * @param string $name
     *
     * @return string
     */
    private function prepareName(string $name): string
    {
        return trim($name);
    }

    /**
     * Подготавливает параметр email для лида
     */
    private function prepareEmail(string $email): array
    {
        return empty($email) ? [] : [['VALUE' => $email, 'VALUE_TYPE' => 'WORK']];
    }

    /**
     * Подготавливает комментарии для лида
     */
    private function prepareComments(string $comments): string
    {
        return mb_substr(trim($comments), 0, 500); // Ограничение длины
    }
}