<?
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.ilMediaConverterPlugin.php');
ilMediaConverterPlugin::loadAR();

/**
 * Class mcMedia
 *
 * @author      Zeynep Karahan  <zk@studer-raimann.ch>
 * @author      Fabian Schmid <fs@studer-raimann.ch>
 */
class mcMedia extends ActiveRecord {

	const ARR_TARGET_MIME_TYPE_H = "video/h.264";
	const ARR_TARGET_MIME_TYPE_W = "video/webm";
	const STATUS_WAITING = 1;
	const STATUS_RUNNING = 2;
	const STATUS_FINISHED = 5;
	const STATUS_FAILED = 9;


	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'mco_source';
	}


	/**
	 * @var int
	 *
	 * @con_has_field        true
	 * @con_fieldtype        integer
	 * @con_length           8
	 * @con_is_primary       true
	 */
	protected $id;
	/**
	 * @var string
	 *
	 * @con_has_field        true
	 * @con_fieldtype        text
	 * @con_length           256
	 */
	protected $filename = '';
	/**
	 * @var string
	 *
	 * @con_has_field    true
	 * @con_fieldtype    text
	 * @con_length       20
	 */
	protected $suffix;
	/**
	 * @var int
	 *
	 * @con_has_field        true
	 * @con_fieldtype        integer
	 * @con_length           8
	 */
	protected $trigger_obj_id;
	/**
	 * @var int
	 *
	 * @con_has_field        true
	 * @con_fieldtype        integer
	 * @con_length           8
	 */
	protected $trigger_obj_media_id;
	/**
	 * @var string
	 *
	 * @con_has_field        true
	 * @con_fieldtype        text
	 * @con_length           256
	 */
	protected $trigger_obj_type = '';
	/**
	 * @var string
	 *
	 * @con_has_field        true
	 * @con_fieldtype        int
	 * @con_length           4
	 */
	protected $status_convert = self::STATUS_WAITING;
	/**
	 * @var int
	 *
	 * @con_has_field        true
	 * @con_fieldtype        date
	 */
	protected $date_convert;
	/**
	 * @var int
	 *
	 * @con_has_field        true
	 * @con_fieldtype        date
	 */
	protected $delivery_datetime;


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
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
	}


	/**
	 * @param string $filename
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
	}


	/**
	 * @return int
	 */
	public function getTriggerObjId() {
		return $this->trigger_obj_id;
	}


	/**
	 * @param int $trigger_obj_id
	 */
	public function setTriggerObjId($trigger_obj_id) {
		$this->trigger_obj_id = $trigger_obj_id;
	}


	/**
	 * @return int
	 */
	public function getTriggerObjMediaId() {
		return $this->trigger_obj_media_id;
	}


	/**
	 * @param int $trigger_obj_media_id
	 */
	public function setTriggerObjMediaId($trigger_obj_media_id) {
		$this->trigger_obj_media_id = $trigger_obj_media_id;
	}


	/**
	 * @return string
	 */
	public function getTriggerObjType() {
		return $this->trigger_obj_type;
	}


	/**
	 * @param string $trigger_obj_type
	 */
	public function setTriggerObjType($trigger_obj_type) {
		$this->trigger_obj_type = $trigger_obj_type;
	}


	/**
	 * @return string
	 */
	public function getStatusConvert() {
		return $this->status_convert;
	}


	/**
	 * @param string $status_convert
	 */
	public function setStatusConvert($status_convert) {
		$this->status_convert = $status_convert;
	}


	/**
	 * @return int
	 */
	public function getDateConvert() {
		return $this->date_convert;
	}


	/**
	 * @param int $status_convert_change
	 */
	public function setDateConvert($status_convert_change) {
		$this->date_convert = $status_convert_change;
	}


	/**
	 * @return int
	 */
	public function getDeliveryDatetime() {
		return $this->delivery_datetime;
	}


	/**
	 * @param int $delivery_datetime
	 */
	public function setDeliveryDatetime($delivery_datetime) {
		$this->delivery_datetime = $delivery_datetime;
	}


	/**
	 * @return string
	 */
	public function getSuffix() {
		return $this->suffix;
	}


	/**
	 * @param string $suffix
	 */
	public function setSuffix($suffix) {
		$this->suffix = $suffix;
	}

	//get all videos, with the status 'waiting' to convert
	/**
	 * @return ilObjMediaConverter[]
	 */
	public static function getNextPendingMediaID() {
		return ilObjMediaConverter::where(array( 'status_convert' => self::STATUS_WAITING ))->orderBy("id")->get();
	}


	//get file from user
	public function getFile() {
		$path = $_FILES['suffix']['tmp_name'];
		$type = $_FILES['suffix']['type'];

		return $path . '/' . $_POST['title'] . '.' . substr($type, 6, 5);
	}


	/**
	 *
	 * Deliver file to Cron Job
	 * use this to upload your file, that it gets converted by this service
	 *
	 * @param string $filename             name of the file
	 * @param string $suffix               suffix of file
	 * @param int    $trigger_obj_id       id of file
	 * @param int    $trigger_obj_media_id id of object
	 * @param string $trigger_obj_type     object type of file
	 * @param int    $delivery_datetime    date of delivery time
	 *
	 * @return string
	 */
	public function uploadFile($filename, $suffix, $trigger_obj_id, $trigger_obj_media_id, $trigger_obj_type, $delivery_datetime) {
		$this->setFilename($filename);
		$this->setSuffix($suffix);
		$this->setTriggerObjId($trigger_obj_id);
		$this->setTriggerObjMediaId($trigger_obj_media_id);
		$this->setTriggerObjType($trigger_obj_type);
		$this->setDeliveryDatetime($delivery_datetime);
		$this->create();

		return true;
	}


	//TODO videos nach dem Konvertieren aus /temp löschen
	public function deleteFile() {
	}


	//temporary folder for the file, that is still not converted, call this first
	public function uploadTemp($tmp_path) {
		$file_path = $this->getFilePath();
		$this->recursiveMkdir($file_path);

		move_uploaded_file($tmp_path, $file_path . '/' . $this->getFilename() . '.' . substr($_FILES['suffix']['type'], 6, 5));
	}


	/**
	 * Call this function after uploadFile and uploadTemp to deliver it to the right directory
	 *
	 * @param string $tmp_path current path of file
	 */
	public function upload($tmp_path) {
		$file_path = $this->getTargetDir();
		$this->recursiveMkdir($file_path);
		move_uploaded_file($tmp_path, $file_path . $this->getFilename() . '.' . substr($_FILES['suffix']['type'], 6, 5));
		// move_uploaded_file($this->getFilename(),$file_path);
		//print_r("tmp_path: " . $tmp_path . " file_path: " . $file_path . " file name: " . $this->getFilename() . " suffix: " . substr($_FILES['suffix']['type'], 6, 5));
		// exit;
	}


	//get target direction from user
	public function getTargetDir() {
		///var/www/ilias_trunk/data/meinilias/xvip/Converted/
		return CLIENT_WEB_DIR . '/xvip/Converted/';
	}


	//temporary path for the file before it is converted
	public function getFilePath() {
		return CLIENT_DATA_DIR . '/temp/videos';
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	protected function recursiveMkdir($path) {
		$dirs = explode(DIRECTORY_SEPARATOR, $path);
		$count = count($dirs);
		$path = '';
		for ($i = 0; $i < $count; ++$i) {
			if ($path != '/') {
				$path .= DIRECTORY_SEPARATOR . $dirs[$i];
			} else {
				$path .= $dirs[$i];
			}
			if (! is_dir($path)) {
				ilUtil::makeDir(($path));
			}
		}

		return true;
	}


	public function hasConvertedMimeType($mime_type) {
		$query = $this->where('suffix is' . self::ARR_TARGET_MIME_TYPE_W);
		$query2 = $this->where('suffix is' . self::ARR_TARGET_MIME_TYPE_H);

		if ($query == $mime_type || $query2 == $mime_type) {
			return true;
		}
	}
}

?>