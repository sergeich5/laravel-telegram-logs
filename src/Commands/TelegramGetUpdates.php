<?php

namespace Sergeich5\LaravelTelegramLogs\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class TelegramGetUpdates extends Command
{
    protected $signature = 'tg_logs:updates {channel}';

    protected $description = 'Telegram logsr get updates';

    function handle(): int
    {
        $channel = $this->argument('channel');

        $config = config(sprintf('logging.channels.%s', $channel));

        if (is_null($config)) {
            $this->error(sprintf('Undefined channel "%s"', $channel));
            return self::FAILURE;
        }
        if (!isset($config['token']) || is_null($config['token'])) {
            $this->error(sprintf('Token not set for channel "%s"', $channel));
            return self::FAILURE;
        }

        try {
            $updates = $this->getUpdates($config['token']);
        } catch (Exception$e) {
            $this->error(sprintf('Telegram API %s: %s', $e->getCode(), $e->getMessage()));
            return self::FAILURE;
        }

        $this->table(['date', 'chat', 'chat_id', 'from', 'from_id', 'message_id', 'thread_id', 'data'], collect($updates)->map(function ($update) {
            $data = '-';

            if (isset($update['message'])) {
                if (isset($update['message']['text']))
                    $data = $update['message']['text'];
                elseif (isset($update['message']['forum_topic_created']))
                    $data = sprintf('topic %s created with id %s', $update['message']['forum_topic_created']['name'], $update['message']['message_thread_id']);
            }

            return [
                Carbon::createFromTimestamp($update['message']['date']),
                $update['message']['chat']['title'],
                $update['message']['chat']['id'],
                $update['message']['from']['first_name'],
                $update['message']['from']['id'],
                $update['message']['message_id'],
                $update['message']['message_thread_id'] ?? '-',
                $data,
            ];
        }));

        return self::SUCCESS;
    }

    private function getUpdates(string $token)
    {
        $r = Http::get("https://api.telegram.org/bot{$token}/getUpdates");

        if (!$r->successful() || !$r->json('ok'))
            throw new Exception($r->json('description'), $r->status());

        return $r->json('result');
    }
}
