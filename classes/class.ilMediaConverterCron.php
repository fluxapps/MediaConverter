<?php

require_once './Services/Cron/classes/class.ilCronJob.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.ilMediaConverterResult.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcPid.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMedia.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMediaState.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcProcessedMedia.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Util/class.vmFFmpeg.php';
require_once './Services/Mail/classes/class.ilMimeMail.php';
require_once './Services/Link/classes/class.ilLink.php';
require_once './Services/Repository/classes/class.ilRepUtil.php';

/**
 * Class ilMediaConverterCron
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
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
        $pid = getmypid();
        $user_pid_id = getmyuid();

        //look if the maximum number of jobs are reached
        //if this is so, don't start a new job
        //else start job
        if(mcPid::find($pid))
        {
            $mcPid = new mcPid($pid);
            $mcPid->setPidUid($user_pid_id);
            $mcPid->update();
        }
        else
        {
            $mcPid = new mcPid();
            $mcPid->setPidId($pid);
            $mcPid->setPidUid($user_pid_id);
            $mcPid->create();
        }

        if ($mcPid->getNumberOfPids() <= 3) {
            foreach (mcMedia::getNextPendingMediaID() as $media) {
                if ($media->getStatusConvert() == mcMedia::STATUS_RUNNING) {
                    continue;
                }

                $media->setStatusConvert(mcMedia::STATUS_RUNNING);
                $media->update();

                $arr_target_mime_types = array( mcMedia::ARR_TARGET_MIME_TYPE_W, mcMedia::ARR_TARGET_MIME_TYPE_M );
                foreach ($arr_target_mime_types as $mime_type) {
                    if ($media->getSuffix() != substr(6, $mime_type)) {
                        //create/update mediastate db entry
                        if($mediaState = mcMediaState::find($media->getId())){
                            $mediaState->setProcessStarted(date('Y-m-d'));
                            $mediaState->update();
                        }else{
                            $mediaState = new mcMediaState();
                            $mediaState->setId($media->getId());
                            $mediaState->setProcessStarted(date('Y-m-d'));
                            $mediaState->create();
                        }

                        //convert file to targetdir
                        $file = $media->getTempFilePath() . '/' . $media->getFilename() . '.' . $media->getSuffix();
                        vmFFmpeg::convert($file, $mime_type, $media->getTargetDir(), $media->getFilename() . '.' . substr($mime_type, 6));

                        //update media db entry
                        $media->setDateConvert(date('Y-m-d'));
                        $media->setStatusConvert(mcMedia::STATUS_FINISHED);
                        $media->update();

                        //create mediaprocessed db entry
                        $mcProcessedMedia = new mcProcessedMedia();
                        //TODO id wird aufsteigend eingetragen, statt die vorgesehene
                        $mcProcessedMedia->saveConvertedFile($media->getId(), date('Y-m-d'), substr($mime_type, 6));
                    }
                }
                //delete temp file
                $media->deleteFile();
            }
        }

        //cron result
		return new ilMediaConverterResult(ilMediaConverterResult::STATUS_OK, 'Cron job terminated successfully.');
	}
}

?>