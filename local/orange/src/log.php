<?php

declare(strict_types=1);

namespace dmyers\orange;

use Closure;
use dmyers\orange\exceptions\InvalidValue;
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
		'filepath' => null,
		'permissions' => 0644,
		'threshold' => 0,
	];

	protected $handler = null;
	protected $isPhpFile = false;
	protected $enabled = false;
	protected $lineFormatter = null;

	protected $psrLevels = [
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
			return str_pad('[' . date('Y-m-d H:i:s') . ']', 22, ' ', STR_PAD_RIGHT) . $level . ': ' . $message . PHP_EOL;
		};

		/* merge config */
		$this->config = array_replace($this->config, $config);

		$this->handler = $this;

		$dir = dirname($this->config['filepath']);

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		if (!is_writable($dir)) {
			throw new FolderNotWritable($dir);
		}

		$this->isPhpFile = (pathinfo($this->config['filepath'], PATHINFO_EXTENSION) === 'php');

		if (isset($this->config['line_formatter'])) {
			if (!$this->config['line_formatter'] instanceof Closure) {
				throw new invalidConfigurationValue('line_formatter must be a closure');
			}

			$this->lineFormatter = $this->config['line_formatter'];
		}

		if (isset($this->config['monolog'])) {
			if (!is_a($this->config['monolog'], '\Monolog\Logger')) {
				throw new invalidConfigurationValue('monolog must be instance \Monolog\Logger');
			}

			$this->handler = &$this->config['monolog'];
		}

		if (isset($this->config['threshold'])) {
			if (!is_int($this->config['threshold'])) {
				throw new invalidConfigurationValue('threshold must be an integer');
			}

			$this->changeThreshold($this->config['threshold']);
		}

		$this->info('Log Class Initialized');
	}

	public function changeThreshold(int $threshold): self
	{
		$this->config['threshold'] = $threshold;

		$this->enabled = ($this->config['threshold'] > 0);

		return $this;
	}

	public function getThreshold(): int
	{
		return $this->config['threshold'];
	}

	public function isEnabled(): Bool
	{
		return $this->enabled;
	}

	public function writeLog($level, $msg)
	{
		if ($this->enabled) {
			/* normalize */
			$level = strtoupper($level);

			/* does this log level even exist? */
			/* bitwise PSR 3 Mode */
			if (array_key_exists($level, $this->psrLevels) && $this->config['threshold'] & $this->psrLevels[$level]) {
				$this->handler->$level($msg);
			}
		}
	}

	public function __call($name, $arguments)
	{
		$level = strtoupper($name);

		if (!array_key_exists($level, $this->psrLevels)) {
			throw new InvalidValue($name);
		}

		$this->internalWrite($level, $arguments[0]);
	}

	protected function internalWrite(string $level, string $msg)
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

		file_put_contents($this->config['filepath'], $write, FILE_APPEND | LOCK_EX);

		if ($isNew) {
			chmod($this->config['filepath'], $this->config['permissions']);
		}
	}
} /* End of Class */
