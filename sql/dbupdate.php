<#1>
<?php
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMedia.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcProcessedMedia.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMediaState.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcPid.php');

mcMedia::updateDB();
mcProcessedMedia::updateDB();
mcMediaState::updateDB();
mcPid::updateDB();
?>