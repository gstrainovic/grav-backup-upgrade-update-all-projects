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

$oLog = new Logger('');
$oLog->pushHandler(new StreamHandler('grav-buu.log', Logger::DEBUG));
$oLog->pushHandler(new StreamHandler('grav-buu-no-debug.log', Logger::INFO));

function fSendmail($sError,$sPath,$sCommand){
    $sMail=
"To: g.strainovic@gmail.com
From: g.strainovic@gmail.com
Subject: Fail on Grav backup upgrade update 
path --> $sPath
command --> $sCommand
message --> $sError
";
	file_put_contents("email.tmp",$sMail);
	shell_exec("ssmtp g.strainovic@gmail.com < email.tmp");
};

function fDebug($sError,$sPath,$sCommand){
    global $oLog;
    $oLog->debug($sError,array("path",$sPath,"command",$sCommand));
}

function fWarning($sError,$sPath,$sCommand){
    global $oLog;
    $oLog->warning($sError,array("path",$sPath,"command",$sCommand));
}

function fEmergency($sError,$sPath,$sCommand){
    global $oLog;
    $oLog->emergency($sError,array("path",$sPath,"command",$sCommand));
}

foreach ($arPaths as $sPath) {
    if (chdir ($sPath)) {
        fDebug(getcwd(),$sPath,"getcwd");
        foreach ($asAr as $as) {
            $sOutput = shell_exec($as["sCommand"]);
            if (strpos($sOutput, $as["sTest"]) !== false) {
                fDebug("ok: match: ".$sOutput." with ".$as["sTest"],$sPath,$as["sCommand"]);
            } else {
                fWarning("fail: match: ".$sOutput." with ".$as["sTest"],$sPath,$as["sCommand"]);
                fSendmail($sOutput,$sPath,$as["sCommand"]);
            }
        }        
    } else {    
        fEmergency("directory not exist",$sPath,"chdir ".$sPath);
        fSendmail("directory not exist",$sPath,"chdir ".$sPath);
    }
}
?>