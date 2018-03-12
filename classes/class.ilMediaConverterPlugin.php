<?php
include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.ilMediaConverterCron.php';

/**
 * Class ilMediaConverterPlugin
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMediaConverterPlugin extends ilCronHookPlugin {

	/**
	 * @var ilMediaConverterPlugin
	 */
	protected static $instance;


	/**
	 * @return ilMediaConverterPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	const PLUGIN_NAME = 'MediaConverter';
	/**
	 * @var  ilMediaConverterCron
	 */
	protected static $cron_job_instance;
	/**
	 * @var ilDB
	 */
	protected $db;


	public function __construct() {
		parent::__construct();

		global $DIC;

		$this->db = $DIC->database();
	}


	/**
	 * @return ilMediaConverterCron[]
	 */
	public function getCronJobInstances() {
		$this->loadCronJobInstance();

		return array( self::$cron_job_instance );
	}


	/**
	 * @param $a_job_id
	 *
	 * @return ilMediaConverterCron
	 */
	public function getCronJobInstance($a_job_id) {
		if ($a_job_id == ilMediaConverterCron::ID) {
			$this->loadCronJobInstance();

			return self::$cron_job_instance;
		}

		return false;
	}


	/**
	 * @return string
	 */
	public function getPluginName() {
		return self::PLUGIN_NAME;
	}


	protected function loadCronJobInstance() {
		if (!isset(self::$cron_job_instance)) {
			self::$cron_job_instance = new ilMediaConverterCron();
		}
	}


	protected function beforeUninstall() {
		$this->db->dropTable(mcMedia::TABLE_NAME, false);
		$this->db->dropTable(mcPid::TABLE_NAME, false);
		$this->db->dropTable(mcProcessedMedia::TABLE_NAME, false);
		$this->db->dropTable(mcMediaState::TABLE_NAME, false);

		// TODO Delete media folder

		return true;
	}
}

?>