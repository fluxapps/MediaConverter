<?php

require_once './Services/Cron/classes/class.ilCronJobResult.php';

/**
 * Class ilMediaConverterResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMediaConverterResult extends ilCronJobResult {

	/**
	 * @param      $status  int
	 * @param      $message string
	 * @param null $code    string
	 */
	public function __construct($status, $message, $code = NULL) {
		$this->setStatus($status);
		$this->setMessage($message);
		$this->setCode($code);
	}
}

?>