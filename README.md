# lumen-backup-commands
Backup commands for backing up the database and project files (complete project dir).<br>
Works on \*NIX yet. Windows needs archiver support integrations.<br>
Database backup supports only PostgreSQL and MySQL. Wanna more - make issue with suggestions!<br>
Commands supports Verbosity Level 64 (-v flag) to show output (default is quiet)

## Installation

### composer

```bash
composer require h-zone/lumen-backup-commands:~0.1-dev
```

or

`composer.json`
```json
"h-zone/lumen-backup-commands": "~0.1-dev"
```

#### Optional
Optional cleanup before `composer update`
`composer.json`
```json
"scripts": {
    "pre-update-cmd": [
        "php artisan cleanup-commands:view-cache",
        "php artisan cleanup-commands:logs"
    ]
}
```
See https://github.com/h-zone/lumen-cleanup-commands

### Lumen
`bootstrap/app.php`
```php
$app->register(Hzone\BackupCommands\Providers\BackupCommandsServiceProvider::class);
```
(not tested yet)

### Laravel 5.2+
`config/app.php`
```php
'providers' => [
    //....
    Hzone\BackupCommands\Providers\BackupCommandsServiceProvider::class,
    //....
],
```
(Laravel before 5.2 is not tested)

### Usage
```sh
php artisan backup-commands:database
php artisan backup-commands:files
```
Or with output (verbosity check)
```sh
php artisan backup-commands:database -v
php artisan backup-commands:files -v
```

### Configuration
```sh
php artisan vendor:publish --provider="Hzone\BackupCommands\Providers\BackupCommandsServiceProvider" --tag="config"
```

### Scheduling
According to the https://laravel.com/docs/5.2/scheduling

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
		$schedule->command('php artisan backup-commands:database -v')
			->withoutOverlapping()
			->appendOutputTo(storage_path().'/logs/likes_cheater_scheduler_5_'.$mon.'.log')
			->dailyAt('04:00')
		;
		$schedule->command('php artisan backup-commands:files -v')
			->withoutOverlapping()
			->appendOutputTo(storage_path().'/logs/likes_cheater_scheduler_20_'.$mon.'.log')
			->weekly()
		;
	}
}

```

### BACKUP FILES BEFORE COMPOSER UPDATE
composer.json
```json
"scripts": {
    "pre-update-cmd": [
        "php artisan backup-commands:databases -v",
        "php artisan backup-commands:files -v"
    ]
}
```
