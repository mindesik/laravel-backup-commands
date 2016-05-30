<?php

namespace Hzone\BackupCommands\Console\Commands;

use Illuminate\Console\Command;
use Config;
use Symfony\Component\Console\Output\OutputInterface;

class BackupDatabaseCommand extends Command
{
    protected $name = 'backup-commands:database';
    protected $description = 'Backup the database(s)';
    protected $signature    = 'backup-commands:database {--database=all}';
    protected $job = [
        'path' => null,
        'hash' => null,
        'time' => null,
    ];
    protected $databases = [];
    protected $dir = null;

    public function __construct()
    {
        parent::__construct();
        $this->job['path'] = Config::get('backupcmds.backupDir');
        $this->job['time'] = time();
        $this->job['hash'] = str_random(8);
        $this->databases = Config::get('backupcmds.databases');
    }

    public function fire()
    {
        $this->verbosityLevel = $this->getOutput()->getVerbosity();
        
        if ($this->verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->info('Backing Up the Databases:');
        }
        
        if (!$this->_makeBackupDir()) {
            $this->error('EMERGENCY EXIT!');
            return false;
        }
        
        if (!empty($this->databases)) {
            if ($this->option('database') == 'all') {
                foreach ($this->databases as $db) {
                    $this->_runDump(Config::get('database.connections.'.$db), $db);
                }
            } else {
                if (in_array($this->option('database'), $this->databases)) {
                    $this->_runDump(Config::get('database.connections.' . $this->option('database')), $this->option('database'));
                } else {
                    $this->error('DATABASE ' . $this->option('database'). ' IS NOT FOUND !');
                    return false;
                }
            }
        }
        
        if ($this->verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->info("\nDone Database backup\n");
        }
    }

    protected function _runDump($cfg, $alias)
    {
        $filename = 'DB_' . $alias . '_' . date('Y-m-d_H-i-s', $this->job[ 'time' ]) . '_' . $this->job['hash'] . '.sql';
        
        if ($this->verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->info("\n" . $alias);
            $this->comment('--------------------------------------');
            $this->comment('Database host: ' . $cfg[ 'host' ]);
            $this->comment('Database name: ' . $cfg[ 'database' ]);
        }
        
        if (!empty($cfg['schema'])) {
            if ($this->verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE) {
                $this->comment('Database schema: ' . $cfg[ 'schema' ]);
            }
        }
        
        $cmd = [];
        $cmd[] = 'cd '.$this->job['path'];
        
        switch ($cfg['driver']) {
            case 'pgsql':
                $cmd[] = 'PGPASSWORD="' . $cfg['password'] . '" pg_dump -h ' . $cfg['host'] . ' -U ' . $cfg['username'] . ' -f ' . $filename . ' ' . $cfg[ 'database' ];
            break;
            case 'mysql':
                $cmd[] = 'mysqldump -u' . $cfg['username'] . ' -p' . $cfg['password'] . ' ' . $cfg['database'] . ' > ' . $filename;
            break;
        }
        
        if ($this->verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->comment('======================================');
        }
        
        $cmd[] = 'tar -zcvf '.$filename.'.tar.gz '.$filename;
        $cmd[] = 'rm '.$filename;
        
        if (!empty($cmd)) {
            if (substr(php_uname(), 0, 7) == 'Windows') {    // not implemented yet archiver support
                pclose(popen('start /B '. implode(' && ', $cmd), 'r'));
            } else {
                exec(implode(' && ', $cmd) . ' > /dev/null &');
            }
        }
        
        if ($this->verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->comment(' > ' . $filename.'.tar.gz');
        }
        
        return $cmd;
    }

    protected function _makeBackupDir()
    {
        $dir = $this->job['path'];
        
        if (is_dir($dir)) {
            return true;
        }
        
        if (is_file($dir)) {
            $this->error('Backup directory name is occupied by filename!');
            return false;
        }
        
        if (is_link($dir)) {
            $this->error('Backup directory name is occupied by symbolic link!');
            return false;
        }
        
        return mkdir($dir, 0777);
    }
}
