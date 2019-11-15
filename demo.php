<?php

include __DIR__."/IpaParser.php";
include __DIR__."/ApkParser.php";

#apk解析
$main = new ApkParser;
$main->open('app.apk');
echo $main->getPackage().'<br>';
echo $main->getVersionName().'<br>';
echo $main->getVersionCode().'<br>';
echo $main->getAppName().'<br>';

echo '#############ios################<br>';
#ipa解析
$main = new IpaParser;
$main->parse('app.ipa');
echo $main->getPackage().'<br>';
echo $main->getVersion().'<br>';
echo $main->getAppName().'<br>';
var_dump( $main->getPlist() );
