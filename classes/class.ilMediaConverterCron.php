<?php

require_once './Services/Cron/classes/class.ilCronJob.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.ilMediaConverterResult.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcPid.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMedia.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMediaState.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcProcessedMedia.php';
require_once './Services/Mail/classes/class.ilMimeMail.php';
require_once("./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Util/class.mcFFmpeg.php");
require_once './Services/Link/classes/class.ilLink.php';
require_once './Services/Repository/classes/class.ilRepUtil.php';
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.mcLog.php');

/**
 * Class ilMediaConverterCron
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMediaConverterCron extends ilCronJob {

	const MAX_PID = 3;
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
		try {
			$pid = getmypid();
			$user_pid_id = getmyuid();
			// look if the maximum number of jobs are reached: if this is so, don't start a new job
			if (mcPid::find($pid)) {
				$mcPid = new mcPid($pid);
				$mcPid->setPidUid($user_pid_id);
				$mcPid->update();
			} else {
				$mcPid = new mcPid();
				$mcPid->setPidId($pid);
				$mcPid->setPidUid($user_pid_id);
				$mcPid->create();
			}

			if ($mcPid->getNumberOfPids() > self::MAX_PID) {
				return new ilMediaConverterResult(ilMediaConverterResult::STATUS_NO_ACTION, 'Number of PIDs exceeds defined maxima of ' . self::MAX_PID);
			}

			foreach (mcMedia::getNextPendingMediaID() as $media) {
				if ($media->getStatusConvert() == mcMedia::STATUS_RUNNING) {
					mcLog::getInstance()->write('Skipping already running task');
					continue;
				}
				mcLog::getInstance()->write('Convert new Item: ' . $media->getFilename());
				$media->setStatusConvert(mcMedia::STATUS_RUNNING);
				$media->update();
				foreach (mcMedia::getSupportedMimeTypes() as $mime_type) {
					$mime_suffix = substr($mime_type, 6);
					// Do not convert if the source type of the video is
					if ($media->getSuffix() == $mime_suffix) {
						continue;
					}
					//create/update mediastate db entry
					if ($mediaState = mcMediaState::find($media->getId())) {
						$mediaState->setProcessStarted(date('Y-m-d'));
						$mediaState->update();
					} else {
						$mediaState = new mcMediaState();
						$mediaState->setId($media->getId());
						$mediaState->setProcessStarted(date('Y-m-d'));
						$mediaState->create();
					}
					mcLog::getInstance()->write('Convert type: ' . $mime_type);
					//convert file to targetdir
					$file = $media->getTempFilePath() . '/' . $media->getFilename() . '.' . $media->getSuffix();
					if (!is_file($file)) {
						mcLog::getInstance()->write('Temporary file of source video not found, skipping converting: ' . $file);
						continue;
					}
					try {
						vmFFmpeg::convert($file, $mime_type, $media->getTargetDir(), $media->getFilename() . '.' . $mime_suffix);
						mcLog::getInstance()->write('Convertion succeeded of file ' . $media->getFilename());
					} catch (ilFFmpegException $e) {
						$media->setStatusConvert(mcMedia::STATUS_FAILED);
						$media->update();
						mcLog::getInstance()->write('Convertion of Item failed: ' . $media->getFilename());
						mcLog::getInstance()->write('Exception message: ' . $e->getMessage());
						continue;
					}
					//update media db entry
					$media->setDateConvert(date('Y-m-d'));
					$media->setStatusConvert(mcMedia::STATUS_FINISHED);
					$media->update();

					//create mediaprocessed db entry
					$mcProcessedMedia = new mcProcessedMedia();
					//TODO id wird aufsteigend eingetragen, statt die vorgesehene
					$mcProcessedMedia->saveConvertedFile($media->getId(), date('Y-m-d'), $mime_suffix);
				}
				//delete temp file
				mcLog::getInstance()->write('Deleting temporary File: ' . $media->getTempFilePath());
				$media->deleteFile();
			}

			//cron result
			return new ilMediaConverterResult(ilMediaConverterResult::STATUS_OK, 'Cron job terminated successfully.');
		} catch (Exception $e) {
			//cron result
			return new ilMediaConverterResult(ilMediaConverterResult::STATUS_CRASHED, 'Cron job crashed: ' . $e->getMessage());
		}
	}
}
