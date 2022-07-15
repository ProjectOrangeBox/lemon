<?php

declare(strict_types=1);

namespace dmyers\orange;

use Closure;
use dmyers\orange\exceptions\FolderNotWritable;
use dmyers\orange\exceptions\invalidConfigurationValue;

class Log
{
	const EMERGENCY = 1;
	const ALERT = 2;
	const CRITICAL = 4;
	const ERROR = 8;
	const WARNING = 16;
	const NOTICE = 32;
	const INFO = 64;
	const DEBUG = 128;
	const ALL = 255;

	protected $config = [
		'permissions' => 0644,
		'threshold' => 0,
	];

	protected $monolog = null;
	protected $isPhpFile = false;
	protected $enabled = false;
	protected $lineFormatter = null;

	protected $psr_levels = [
		'NONE'			=> 0,
		'EMERGENCY' => 1,
		'ALERT'     => 2,
		'CRITICAL'  => 4,
		'ERROR'     => 8,
		'WARNING'   => 16,
		'NOTICE'    => 32,
		'INFO'      => 64,
		'DEBUG'     => 128,
	];

	public function __construct(array $config)
	{
		/* defaults */
		$this->config['filepath'] = __ROOT__ . '/var/logs/' . date('Y-m-d') . '-log.txt';

		$this->lineFormatter = function (string $level, string $message): string {
			return str_pad($level, 10, ' ', STR_PAD_RIGHT) . str_pad(date('Y-m-d H:i:s'), 20, ' ', STR_PAD_RIGHT) . $message . PHP_EOL;
		};

		/* merge config */
		$this->config = array_replace($this->config, $config);

		$this->construct();

		$this->writeLog('info', 'Orange Log Class Initialized');
	}

	protected function construct(): void
	{
		$dir = dirname($this->config['filepath']);

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		if (!is_writable($dir)) {
			throw new FolderNotWritable($dir);
		}

		$this->isPhpFile = (pathinfo($this->config['filepath'], PATHINFO_EXTENSION) === 'php');

		if (isset($this->config['line_formatter'])) {
			if ($this->config['line_formatter'] instanceof Closure) {
				$this->lineFormatter = $this->config['line_formatter'];
			} else {
				throw new invalidConfigurationValue('line_formatter must be a closure');
			}
		}

		if (isset($this->config['threshold'])) {
			if (is_int($this->config['threshold'])) {
				$this->config['threshold'] = $this->config['threshold'];
			} else {
				throw new invalidConfigurationValue('threshold must be an integer');
			}
		}

		if (isset($this->config['monolog'])) {
			if ($this->config['monolog'] instanceof \Monolog\Logger) {
				$this->monolog = &$this->config['monolog'];
			} else {
				throw new invalidConfigurationValue('monolog must be instance \Monolog\Logger');
			}
		}

		$this->enabled = ($this->config['threshold'] > 0);
	}

	public function isEnabled(): Bool
	{
		return $this->enabled;
	}

	public function writeLog($level, $msg): bool
	{
		if (!$this->enabled) {
			return false;
		}

		/* normalize */
		$level = strtoupper($level);

		/* bitwise PSR 3 Mode */
		if ((!array_key_exists($level, $this->psr_levels)) || (!($this->config['threshold'] & $this->psr_levels[$level]))) {
			return false;
		}

		return ($this->monolog) ? $this->monologWriteLog($level, $msg) : $this->internalWriteLog($level, $msg);
	}

	protected function monologWriteLog(string $level, string $msg): bool
	{
		/* route to monolog */
		switch ($level) {
			case 'EMERGENCY': // 1
				$this->monolog->addEmergency($msg);
				break;
			case 'ALERT': // 2
				$this->monolog->addAlert($msg);
				break;
			case 'CRITICAL': // 4
				$this->monolog->addCritical($msg);
				break;
			case 'ERROR': // 8
				$this->monolog->addError($msg);
				break;
			case 'WARNING': // 16
				$this->monolog->addWarning($msg);
				break;
			case 'NOTICE': // 32
				$this->monolog->addNotice($msg);
				break;
			case 'INFO': // 64
				$this->monolog->addInfo($msg);
				break;
			case 'DEBUG': // 128
				$this->monolog->addDebug($msg);
				break;
		}

		return true;
	}

	protected function internalWriteLog(string $level, string $msg): bool
	{
		$write = '';
		$isNew = false;

		if (!file_exists($this->config['filepath'])) {
			$isNew = true;

			/* Only add protection to php files */
			if ($this->isPhpFile) {
				$write .= "<?php exit(); ?>\n\n";
			}
		}

		/* closure */
		$write .= ($this->lineFormatter)($level, $msg);

		$bytes = file_put_contents($this->config['filepath'], $write, FILE_APPEND | LOCK_EX);

		if ($isNew) {
			chmod($this->config['filepath'], $this->config['permissions']);
		}

		return is_int($bytes);
	}
} /* End of Class */