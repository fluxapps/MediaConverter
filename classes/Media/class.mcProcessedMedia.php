<?php
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.ilMediaConverterPlugin.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');

//ilMediaConverterPlugin::loadAR();


/**
 * Class mcProcessedMedia
 *
 * @author      Zeynep Karahan  <zk@studer-raimann.ch>
 * @author      Fabian Schmid <fs@studer-raimann.ch>
 * @author      Theodor Truffer <tt@studer-raimann.ch>
 */
class mcProcessedMedia extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'mco_processed';
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
	protected $converted_datetime;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $converted_mime_type = '';


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getConvertedDatetime() {
		return $this->converted_datetime;
	}


	/**
	 * @param int $converted_datetime
	 */
	public function setConvertedDatetime($converted_datetime) {
		$this->converted_datetime = $converted_datetime;
	}


	/**
	 * @return string
	 */
	public function getConvertedMimeType() {
		return $this->converted_mime_type;
	}


	/**
	 * @param string $converted_mime_type
	 */
	public function setConvertedMimeType($converted_mime_type) {
		$this->converted_mime_type = $converted_mime_type;
	}


	/**
	 * @param int    $id
	 * @param int    $converted_datetime
	 * @param string $converted_mime_type
	 */
	public function saveConvertedFile($id, $converted_datetime, $converted_mime_type) {
		$this->setId($id);
		$this->setConvertedDatetime($converted_datetime);
		$this->setConvertedMimeType($converted_mime_type);
        if($this->find($id) == null){
		    $this->create();
        }else{
            $this->update();
        }
	}
}

?>