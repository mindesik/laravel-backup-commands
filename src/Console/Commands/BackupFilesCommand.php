<?php namespace Hzone\BackupCommands\Console\Commands;
use Illuminate\Console\Command;
use Config;
use Symfony\Component\Console\Output\OutputInterface;
class BackupFilesCommand extends Command
{
	protected $name			= 'backup-commands:files';
	protected $description	= 'Backup Project Files';
	protected $job		  = [
		'path'		=> null,
		'hash'		=> null,
		'time'		=> null,
		'exclude'	=> [],
	];

	public function __construct()
	{
		parent::__construct();
		$this->job['path'] = Config::get('backupcmds.backupDir');
		$this->job['exclude'] = Config::get('backupcmds.exclude');
		$this->job['time'] = time();
		$this->job['hash'] = str_random(8);
	}

	public function fire()
	{
		$this->verbosityLevel = $this->getOutput()->getVerbosity();
		if( $this->verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE)
		{
			$this->info( 'Backing Up the Files' );
		}
		$filename	= 'FILES_' . date( 'Y-m-d_H-i-s', $this->job[ 'time' ] ) . '_' . $this->job['hash'] . '.tar.gz';
		$cmd		= [];
		$cmd[]		= 'cd '.base_path();
		$c			= 'tar -czvf ' . $this->job['path'] . '/' . $filename . ' -C ' . dirname( base_path() ) . ' ' . basename( base_path() );
		if ( !empty( $this->job['exclude'] ) )
		{
			foreach( $this->job['exclude'] as $e )
			{
				$c .= " --exclude='" . basename( $e ) . "'";
			}
		}
		$cmd[] = $c;
		if ( !empty( $cmd ) )
		{
			if (substr(php_uname(), 0, 7) == "Windows")
			{	// not implemented yet archiver support
				pclose(popen("start /B ". implode( ' && ', $cmd ), "r"));
			}
			else
			{
				exec( implode( ' && ', $cmd ) . " > /dev/null &");
			}
		}
		if( $this->verbosityLevel >= OutputInterface::VERBOSITY_VERBOSE)
		{
			$this->comment( " > " . $filename.'.tar.gz' );
			$this->info( "Done Files backup\n" );
		}
	}
	protected function _makeBackupDir()
	{
		$dir = $this->job['path'];
		if ( is_dir( $dir ) )
		{
			return true;
		}
		if ( is_file( $dir ) )
		{
			$this->error('Backup directory name is occupied by filename!');
			return false;
		}
		if ( is_link( $dir ) )
		{
			$this->error('Backup directory name is occupied by symbolic link!');
			return false;
		}
		return mkdir( $dir, 0777 );
	}
}
