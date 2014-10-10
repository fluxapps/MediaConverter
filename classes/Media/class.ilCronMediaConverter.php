<?php

//$cron = new ilCronMediaConverter($_SERVER['argv']);
//$cron::run();

require_once('/var/www/ilias_trunk/Services/MediaConverter/classes/class.ilObjMediaConverter.php');
require_once('/var/www/ilias_trunk/Services/MediaConverter/classes/class.ilMediaProcessed.php');
require_once('/var/www/ilias_trunk/Services/MediaObjects/classes/class.ilFFmpeg.php');
require_once('/var/www/ilias_trunk/Services/MediaConverter/classes/class.ilMediaState.php');
require_once('/var/www/ilias_trunk/Services/MediaConverter/classes/class.ilPid.php');

//TODO To delete
// /usr/bin/php /var/www/ilias_trunk/Services/MediaConverter/classes/class.ilCronMediaConverter.php cron homer meinilias mit sudo

/**
 * Class ilCronMediaConverter
 *
 *
 * @deprecated
 * @author      Zeynep Karahan  <zk@studer-raimann.ch>
 */
class ilCronMediaConverter {

	const MAX = 3; //provisorische Lösung zum Testen (wird in der ILIAS Administration festgelegt)

	//    /**
	//     * @param array $data
	//     */
	//    //TODO läuft nicht parallel mit dem Videoportal im Browser. Muss zunächst auskommentiert werden
	//    function __construct($data)
	//    {
	//        $_COOKIE['ilClientId'] = $data[3];
	//        $_POST['username'] = $data[1];
	//        $_POST['password'] = $data[2];
	//        $this->initILIAS();
	//
	//        global $ilDB, $ilUser, $ilCtrl;
	//
	//        /**
	//         * @var $ilDB   ilDB
	//         * @var $ilUser ilObjUser
	//         * @var $ilCtrl ilCtrl
	//         */
	//        $this->db = $ilDB;
	//        $this->user = $ilUser;
	//        $this->ctrl = $ilCtrl;
	//    }
	//
	//
	//    public function initILIAS()
	//    {
	//        //chdir(substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/Services')));
	//        chdir(strstr(__FILE__, 'Services', true));
	//        require_once('./include/inc.ilias_version.php');
	//        require_once('./Services/Component/classes/class.ilComponent.php');
	//        if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.2.999')) {
	//            require_once './Services/Context/classes/class.ilContext.php';
	//            ilContext::init(ilContext::CONTEXT_WEB);
	//            require_once './Services/Init/classes/class.ilInitialisation.php';
	//            ilInitialisation::initILIAS();
	//        } else {
	//            $_GET['baseClass'] = 'ilStartUpGUI';
	//            require_once('./include/inc.get_pear.php');
	//            require_once('./include/inc.header.php');
	//        }
	//        require_once('Services/MediaConverter/classes/class.ilObjMediaConverter.php');
	//        require_once('Services/MediaConverter/classes/class.ilMediaProcessed.php');
	//        require_once('Services/MediaObjects/classes/class.ilFFmpeg.php');
	//        require_once('Services/MediaConverter/classes/class.ilMediaState.php');
	//        require_once('Services/MediaConverter/classes/class.ilPid.php');
	//    }

	/**
	 * Convert files with any formats, which are supported by FFMpeg into webm and h.264(mp4)
	 *
	 * TODO konvertiert in einem Schritt mehrmals und macht mehrere Einträge in der DB, trotzdem wird Zieldatei nicht im Zielpfad gespeichert
	 * TODO am Ende wird das Video in dem DBTabelle vom Portal nicht mit dem Zielformat eingetragen
	 */
	static function run() {
		//ilMediaProcessed::installDB();
		//ilMediaState::installDB();
		// ilObjMediaConverter::installDB();
		//ilPid::installDB();

		// tail -f /var/iliasdata/ilias_trunk/ilias.log im Terminal

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
	}
}

?>