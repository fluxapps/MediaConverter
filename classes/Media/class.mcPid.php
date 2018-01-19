<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class mcPid
 *
 * Includes the Process Id and the the user ID of the PID
 *
 * @author  Zeynep Karahan  <zk@studer-raimann.ch>
 */
class mcPid extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'mco_pid';
	}


	/**
	 * @var int
	 *
	 * @con_has_field        true
	 * @con_fieldtype        integer
	 * @con_length           8
	 * @con_is_primary       true
	 */
	protected $pid_id;
	/**
	 * @var int
	 *
	 * @con_has_field        true
	 * @con_fieldtype        integer
	 * @con_length           8
	 */
	protected $pid_uid;


	/**
	 * @return int
	 */
	public function getPidId() {
		return $this->pid_id;
	}


	/**
	 * @param int $pid_id
	 */
	public function setPidId($pid_id) {
		$this->pid_id = $pid_id;
	}


	/**
	 * @return int
	 */
	public function getPidUid() {
		return $this->pid_uid;
	}


	/**
	 * @param int $pid_uid
	 */
	public function setPidUid($pid_uid) {
		$this->pid_uid = $pid_uid;
	}


	//count all ids and return the number of PID's
	//TODO am Ende löschen, da scheinbar die selben ids manchmal vergeben werden
	public function getNumberOfPids() {
		return mcPid::where(array( 'pid_id' => $this->getPidId() ))->count();
	}
}

?>