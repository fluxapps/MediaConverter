MediaConverter
========
ILIAS CronJob-Service which converts Video-Files to mp4.

### Installation
You need ILIAS >= 5.2 to run this plugin.

In order to install the MediaConverter plugin go into ILIAS root folder and use:

```bash
mkdir -p Customizing/global/plugins/Services/Cron/CronHook
cd Customizing/global/plugins/Services/Cron/CronHook
git clone https://github.com/studer-raimann/MediaConverter.git
```

#### Install ffmpeg
This plugin requires ffmpeg. If not yet installed (you can test it by typing 'ffmpeg' in a console), download it from: https://www.ffmpeg.org/download.html
Or, if you're using Ubuntu, you can install ffmpeg by typing the following commands in your terminal:
```bash
sudo add-apt-repository ppa:mc3man/trusty-media && sudo apt-get update
sudo apt-get install ffmpeg
```
After installing, add the path to your installation:
Either in the ilias setup under Basic Settings -> Optional Third-Party Tools -> Path to ffmpeg, write '/usr/bin/ffmpeg'
or directly into the file ilias.ini.php -> [tools] -> ffmpeg = "/usr/bin/ffmpeg"

### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Soure Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  

