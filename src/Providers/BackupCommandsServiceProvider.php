<?php

namespace Hzone\BackupCommands\Providers;

use Illuminate\Support\ServiceProvider;
use Hzone\BackupCommands\Console\Commands\BackupDatabaseCommand;
use Hzone\BackupCommands\Console\Commands\BackupFilesCommand;

class BackupCommandsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/backupcmds.php' => config_path('backupcmds.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton('command.backup-commands.database', function () {
            return new BackupDatabaseCommand;
        });

        $this->app->singleton('command.backup-commands.files', function () {
            return new BackupFilesCommand;
        });

        $this->commands('command.backup-commands.database');

        $this->commands('command.backup-commands.files');
    }
}
