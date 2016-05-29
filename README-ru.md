# lumen-backup-commands
Команды для бэкапа БД и файлов проекта.<br>
Работает пока только на *никсе, ввиду отсуствия необходимых компонент в windows.<br>
Однако, если есть tar-архиватор в системе, то работать должно в любом случае.<br>
Бэкап БД поддерживает только PostgreSQL и MySQL. Хотите больше - пишите в issue предложение.<br>
Команды поддерживают уровень Verbosity 64 (флаг -v, что означает, что команда будет производить вывод результата своих действий)

## Установка

### composer

```bash
composer require h-zone/lumen-backup-commands:~0.1-dev
```

или

`composer.json`
```json
"h-zone/lumen-backup-commands": "~0.1-dev"
```

### Lumen
Смотреть здесь - https://github.com/h-zone/lumen-backup-commands

### Laravel 5.2+
`config/app.php`
```php
'providers' => [
    //....
    Hzone\BackupCommands\Providers\BackupCommandsServiceProvider::class,
    //....
],
```

### Опубликование конфига
```sh
php artisan vendor:publish --provider="Hzone\BackupCommands\Providers\BackupCommandsServiceProvider" --tag="config"
```

### Как использовать
```sh
php artisan backup-commands:database
php artisan backup-commands:database --database=all
php artisan backup-commands:database --database=dbalias
php artisan backup-commands:files
```
Или с выводом на экран (проверка verbosity)
```sh
php artisan backup-commands:database -v
php artisan backup-commands:database -v --database=all
php artisan backup-commands:database -v --database=dbalias
php artisan backup-commands:files -v
```

### Планирование задач
Согласно документации - https://laravel.com/docs/5.2/scheduling
Бэкап Баз данных / Конкретной БД / Файлов проекта

app/Console/Kernel.php
```php
<?php namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
class Kernel extends ConsoleKernel
{
	protected $commands = [
		Hzone\BackupCommands\Console\Commands\BackupDatabaseCommand::class,
		Hzone\BackupCommands\Console\Commands\BackupFilesCommand::class,
	];
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('backup-commands:database -v')
			->withoutOverlapping()
			->appendOutputTo(storage_path().'/logs/backup-database-commands_'.date('Y-M-D_H-i-s').'.log')
			->dailyAt('04:00');
		$schedule->command('backup-commands:files -v')
			->withoutOverlapping()
			->appendOutputTo(storage_path().'/logs/backup-files-commands_'.date('Y-M-D_H-i-s').'.log')
			->weekly();

		/**
		 * ЕСЛИ ВАМ НЕОБХОДИМО БЭКАПИТЬ КОНКРЕТНУЮ БД В КОНКРЕТНОЕ ВРЕМЯ
		 * ИСПОЛЬЗУЙТЕ --database=dbalias ОПЦИЮ
		 * 
		 * $schedule->command('backup-commands:database -v --database=dbalias1')
		 * 	->withoutOverlapping()
		 * 	->appendOutputTo(storage_path().'/logs/backup-database-commands_dbalias1_'.date('Y-M-D_H-i-s').'.log')
		 * 	->dailyAt('04:00');
		 *
		 * $schedule->command('backup-commands:database -v --database=dbalias2')
		 * 	->withoutOverlapping()
		 * 	->appendOutputTo(storage_path().'/logs/backup-database-commands_dbalias2_'.date('Y-M-D_H-i-s').'.log')
		 * 	->monthly();
		 * 
		 */
		 }
}

```

### Бэкап файлов перед обновлением через композер
composer.json
```json
"scripts": {
    "pre-update-cmd": [
        "php artisan backup-commands:databases -v",
        "php artisan backup-commands:files -v"
    ]
}
```

#### Очистка
Опционально можно зачиститься от логов/файлов кеша перед обновлением через композер
`composer.json`
```json
"scripts": {
    "pre-update-cmd": [
        "php artisan cleanup-commands:view-cache",
        "php artisan cleanup-commands:logs"
    ]
}
```
Смотреть здесь - https://github.com/h-zone/lumen-cleanup-commands
