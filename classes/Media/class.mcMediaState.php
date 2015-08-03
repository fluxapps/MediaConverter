<?php
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.mconv.php');
mconv::loadActiveRecord();

/**
 * Class mcMediaState
 *
 * @author  Zeynep Karahan  <zk@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class mcMediaState extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'mco_state';
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