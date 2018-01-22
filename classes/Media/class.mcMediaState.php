<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class mcMediaState
 *
 * @author  Zeynep Karahan  <zk@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class mcMediaState extends ActiveRecord {
	const TABLE_NAME = 'mco_state';
	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 * @db_is_primary       true
	 */
	protected $id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        date
	 */
	protected $process_started;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $process_id
	 */
	public function setId($process_id) {
		$this->id = $process_id;
	}


	/**
	 * @return int
	 */
	public function getProcessStarted() {
		return $this->process_started;
	}


	/**
	 * @param int $process_started
	 */
	public function setProcessStarted($process_started) {
		$this->process_started = $process_started;
	}
}

?>