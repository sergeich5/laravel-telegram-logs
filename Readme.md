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
