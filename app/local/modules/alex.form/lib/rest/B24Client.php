<?php

namespace alex\form\lib\rest;

use Bitrix\Main\ArgumentException;

/**
 * Клиент для отправки данных в Б24
 */
class B24Client
{
    private const CRM_ADD_LEAD = 'crm.lead.add';

    public function __construct(private string $webhookUrl)
    {
        $this->webhookUrl = rtrim($webhookUrl, '/') . '/';
    }

    /**
     * Отправка лида в Bitrix24
     *
     * @param array $leadData
     *
     * @throws ArgumentException
     *
     * @return array
     */
    public function sendLead(array $leadData): array
    {
        $url = $this->webhookUrl . self::CRM_ADD_LEAD;
        $payload = json_encode(['fields' => $leadData]);

        $this->logToFile('/upload/b24_api_requests.log', [
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $url,
            'data' => $leadData
        ]);

        $ch = curl_init($url);

        $response = $this->getCurlResponse($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $this->logToFile('/upload/b24_api_responses.log', [
            'timestamp' => date('Y-m-d H:i:s'),
            'http_code' => $httpCode,
            'curl_error' => $curlError,
            'response' => $response
        ]);

        if ($curlError) {
            throw new ArgumentException('cURL Error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new ArgumentException('HTTP Error: ' . $httpCode);
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ArgumentException('JSON decode error: ' . json_last_error_msg());
        }

        if (isset($result['error'])) {
            throw new ArgumentException('B24 API Error: ' . $result['error_description'] . ' (' . $result['error'] . ')');
        }

        return $result;
    }

    /**
     * Возвращает ответ запроса
     *
     * @param $ch
     *
     * @return bool|string
     */
    private function getCurlResponse($ch): bool|string
    {
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 10,
        ]);

        return curl_exec($ch);
    }

    /**
     * Записывает логи в конкретный файл
     *
     * @param string $relativePath
     * @param array $data
     *
     * @return void
     */
    private function logToFile(string $relativePath, array $data): void
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
        file_put_contents($path, print_r($data, true) . "\n\n", FILE_APPEND);
    }
}
