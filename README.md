# Installation

1. Run composer

```
composer require sergeich5/laravel-telegram-logs
```

2. Add new channel to your `config/logging.php` at `channels` stack

```php
'telegram' => [
    'driver' => 'custom',
    'via'    => \Sergeich5\LaravelTelegramLogs\TelegramLogger::class,
    'level' => 'debug',
    
    // Telegram BOT_ID and TOKEN colon separated
    'token' => 'BOT_ID:BOT_TOKEN',
    
    // Telegram Chat Id
    'chat_id' => '12345678',
    
    // int|null to send message to specific chat thread, see: https://core.telegram.org/api/threads
    'thread_id' => '123',
],
```

## Get Telegram updates

```bash
php artisan tg_logs:updates {channel_name}
```

Result:

| date                | chat       | chat_id   | from     | from_id | message_id | thread_id | data                               |
|---------------------|------------|-----------|----------|---------|------------|-----------|------------------------------------|
| 2024-07-17 10:32:38 | CHAT_NAME1 | -12345678 | UserName | 12345   | 1          | -         | Hello, world                       |
| 2024-07-17 11:26:04 | CHAT_NAME2 | -87654321 | UserName | 12345   | 2          | 5         | topic TOPIC_NAME created with id 5 |
