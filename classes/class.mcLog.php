<?php
require_once('./Services/Logging/classes/class.ilLog.php');

/**
 * Class mcLog
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class mcLog {

	/**
	 * @var string
	 */
	protected $path = '';
	/**
	 * @var ilLog
	 */
	protected $log;
	/**
	 * @var mcLog
	 */
	protected static $instance;


	/**
	 * @return mcLog
	 */
	public static function getInstance() {
		if (! isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	protected function __construct() {
		$this->setPath(ILIAS_LOG_DIR);
		$this->setLog(new ilLog($this->getPath(), 'converter.log'));
	}


	/**
	 * @param $message
	 */
	public function write($message) {
		$this->getLog()->write($message);
	}


	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}


	/**
	 * @return ilLog
	 */
	public function getLog() {
		return $this->log;
	}


	/**
	 * @param ilLog $log
	 */
	public function setLog($log) {
		$this->log = $log;
	}
}

?>
