<?php

namespace Sergeich5\LaravelTelegramLogs;

use Exception;
use Illuminate\Support\Facades\Http;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;

/**
 * Class TelegramHandler
 * @package App\Logging
 */
class TelegramHandler extends AbstractProcessingHandler
{
    private array $config;
    private string $botToken;
    private int $timeout;
    private string $domain;
    private string $chatId;
    private ?string $threadId;

    /**
     * TelegramHandler constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $level = Logger::toMonologLevel($config['level']);

        parent::__construct($level, true);

        $this->config = $config;
        $this->botToken = $this->getConfigValue('token');
        $this->timeout = $this->getConfigValue('timeout') ?? 25;
        $this->domain = $this->getConfigValue('domain') ?? 'https://api.telegram.org/';
        $this->chatId = $this->getConfigValue('chat_id');
        $this->threadId = $this->getConfigValue('thread_id');
    }

    /**
     * @param LogRecord $record
     */
    public function write(LogRecord $record): void
    {
        if (!$this->botToken || !$this->chatId) {
            throw new \InvalidArgumentException('Bot token or chat id is not defined for Telegram logger');
        }

        // trying to make request and send notification
        try {
            $textChunks = str_split($this->formatText($record), 4096);

            foreach ($textChunks as $textChunk) {
                $this->sendMessage($textChunk);
            }
        } catch (Exception $exception) {
            \Log::channel('single')->error($exception->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("%message% %context% %extra%\n", null, false, true);
    }

    /**
     * @param $record
     * @return string
     */
    private function formatText($record): string
    {
        return sprintf("#%s (#%s): %s", config('app.name'), $record['level_name'], $record['message']);
    }

    /**
     * @param string $text
     */
    private function sendMessage(string $text): void
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => trim($text),
            'parse_mode' => 'HTML',
        ];

        if (isset($this->threadId))
            $params['message_thread_id'] = $this->threadId;

        Http::timeout($this->timeout)
            ->get($this->domain . 'bot' . $this->botToken . '/sendMessage', $params);
    }

    /**
     * @param string $key
     * @param string $defaultConfigKey
     * @return string
     */
    private function getConfigValue($key, $defaultConfigKey = null): ?string
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return config($defaultConfigKey ?: "telegram-logger.$key");
    }
}
