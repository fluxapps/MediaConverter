<?php

require_once './Services/Cron/classes/class.ilCronJob.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.ilMediaConverterStatus.php';
require_once './Services/Mail/classes/class.ilMimeMail.php';
require_once './Services/Link/classes/class.ilLink.php';
require_once './Services/Repository/classes/class.ilRepUtil.php';

/**
 * Class ilMediaConverterCron
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMediaConverterCron extends ilCronJob {

	const MAX = 3;
	const ID = 'media_conv';
	/**
	 * @var  ilMediaConverterPlugin
	 */
	protected $pl;
	/**
	 * @var  ilDB
	 */
	protected $db;
	/**
	 * @var  ilLog
	 */
	protected $ilLog;


	public function __construct() {
		global $ilDB, $ilLog;
		$this->db = $ilDB;
		$this->pl = ilMediaConverterPlugin::getInstance();
		$this->log = $ilLog;
	}


	/**
	 * @return string
	 */
	public function getId() {
		return self::ID;
	}


	/**
	 * @return bool
	 */
	public function hasAutoActivation() {
		return true;
	}


	/**
	 * @return bool
	 */
	public function hasFlexibleSchedule() {
		return true;
	}


	/**
	 * @return int
	 */
	public function getDefaultScheduleType() {
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}


	/**
	 * @return array|int
	 */
	public function getDefaultScheduleValue() {
		return 1;
	}


	/**
	 * @return ilMediaConverterResult
	 */
	public function run() {
		//
		$ilPid = new ilPid();
		$pid = getmypid();
		$user_pid_id = getmyuid();

		global $ilLog;
		//look if the maximum number of jobs are reached
		//if this is so, don't start a new job
		//else start job
		$ilPid->setPidId($pid);
		$ilPid->setPidUid($user_pid_id);
		$ilPid->create();
		$ilPid->update();
		if ($ilPid->getNumberOfPids() <= self::MAX) {
			foreach (ilObjMediaConverter::getNextPendingMediaID() as $ilObjMediaConverter) {
				$ilMediaState = new ilMediaState();
				if ($ilObjMediaConverter->getStatusConvert() == ilObjMediaConverter::STATUS_RUNNING) {
					continue;
				}

				$ilObjMediaConverter->setStatusConvert(ilObjMediaConverter::STATUS_RUNNING);
				$ilObjMediaConverter->update();

				$arr_target_mime_types = array( ilObjMediaConverter::ARR_TARGET_MIME_TYPE_W, ilObjMediaConverter::ARR_TARGET_MIME_TYPE_W );
				foreach ($arr_target_mime_types as $mime_type) {
					if (! $ilObjMediaConverter->hasConvertedMimeType($mime_type)) {
						//TODO falsche id wird vergeben
						$ilMediaState->setId($ilObjMediaConverter->getId());
						$ilMediaState->setProcessStarted(date('Y-m-d'));
						$ilMediaState->create();
						$ilMediaState->update();
						$file = $ilObjMediaConverter->getFilePath() . '/' . $_POST['title'] . '.' . substr($ilObjMediaConverter->getSuffix(), 6, 8);
						//TODO aktuell wird nur in webm konvertiert nicht in h264
						ilFFmpeg::convert($file, $mime_type, $ilObjMediaConverter->getTargetDir(), 'video' . '.' . substr($mime_type, 6, 8));
						$ilObjMediaConverter->setDateConvert(date('Y-m-d'));
						$ilObjMediaConverter->setStatusConvert(ilObjMediaConverter::STATUS_FINISHED);
						$ilObjMediaConverter->update();
						$ilMediaProcessed = new ilMediaProcessed();
						//TODO id wird aufsteigend eingetragen, statt die vorgesehene
						$ilMediaProcessed->saveConvertedFile($ilObjMediaConverter->getId(), date('Y-m-d'), substr($mime_type, 6));
						$ilMediaProcessed->update();
					}
				}
			}
		}

		return new ilMediaConverterResult(ilMediaConverterResult::STATUS_OK, 'Cron job terminated successfully.');
	}
}

?>