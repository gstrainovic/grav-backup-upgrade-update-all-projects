<?php

require 'vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$arPaths=array("/srv/users/serverpilot/apps/gravcms/public/mijajlovic.ch/","/srv/users/serverpilot/apps/ddp/public/");

$asAr=array([
        "sCommand" => "php bin/grav backup",
        "sTest" => "Saving and compressing archive...", 
],[
        "sCommand" => "php bin/gpm self-upgrade -y",
        "sTest" => "You are already running the latest version of Grav", 
],[
        "sCommand" => "php bin/gpm update -y",
        "sTest" => "Nothing to update."
]);

// create a log channel
$oLog = new Logger('');
$oLog->pushHandler(new StreamHandler('grav-buu.log', Logger::DEBUG));

$oLog->debug('backup disabled');
$oLog->debug('Start Grav backup upgrade update of all projects');

foreach ($arPaths as $sPath) {
    $oLog->debug("try: cd to: ".$sPath);
    if (chdir ($sPath)) {
        $oLog->debug("cwd: ".getcwd());
        foreach ($asAr as $as) {
            $oLog->debug("try: ".$as["sCommand"]);
            $sOutput = shell_exec($as["sCommand"]);
            $oLog->debug("try: match: ".$sOutput." with ".$as["sTest"]);
            if (strpos($sOutput, $as["sTest"]) !== false) {
                $oLog->debug("ok: match: ".$sOutput." with ".$as["sTest"]);
            } else {
                $oLog->error("fail: match: ".$sOutput." with ".$as["sTest"]);
            }
        }        
    } else {
        $oLog->error('fail: cd to: ',array($sPath));
        echo "fail: cd to $sPath\n";
    }
}
?>