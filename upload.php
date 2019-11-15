<?php
$ext_arr=array('ipa','apk');

function  Directory($dir){    
	return   is_dir($dir)  or  Directory(dirname($dir))  and   mkdir($dir , 0777);
}
function getFileExt($filename){
	return strtolower(substr(strrchr($filename, '.'), 1));
}

if($_SERVER['REQUEST_METHOD']!='POST'){
@exit('Hello World!');
}


$ext_name=getFileExt($_FILES['file']['name']);
in_array($ext_name,$ext_arr) || @exit('文件不允许上传');


$app=array();
if($ext_name == 'ipa'){
	include_once 'IpaParser.php';	
	$main = new IpaParser;
	$main->parse($_FILES["file"]['tmp_name']);
	$app['package']=$main->getPackage();
	$app['version']=$main->getVersion();
	$app['name']=$main->getAppName();
}elseif($ext_name == 'apk'){
	include_once 'ApkParser.php';
	$main = new ApkParser;
	$main->open($_FILES["file"]['tmp_name']);
	$app['package']=$main->getPackage();
	$app['version']=$main->getVersionName();
	$app['name']=$main->getAppName();
}else{
	exit();
}

$md5=md5_file($_FILES["file"]["tmp_name"]);

$upload_dir='./upload/'.$app['package'].date("/Ym/d/");
$app_name='./upload/'.$app['package'].'/app.'.$ext_name;
Directory($upload_dir) || @exit('创建上传文件夹失败');

$upload_file=$upload_dir.$md5.'.'.$ext_name;

move_uploaded_file($_FILES["file"]["tmp_name"],$upload_file) || exit('上传文件失败');

if(file_exists($app_name)){
	unlink($app_name) && copy($upload_file,$app_name);
}else{
	copy($upload_file,$app_name);
}

$app_url='https://'.$_SERVER['SERVER_NAME'].substr($upload_file,1);
$last_app_url='https://'.$_SERVER['SERVER_NAME'].substr($app_name,1);

if($ext_name == 'ipa'){
	$temp_file='manifest.plist';
	$fp = fopen($temp_file,'r');
	$str=fread($fp,filesize($temp_file));
	$str=str_replace('{packagename}',$app['package'],$str);
	$str=str_replace('{name}',$app['name'],$str);
	$last_str=str_replace('{app_url}',$last_app_url,$str);
	$str=str_replace('{app_url}',$app_url,$str);
	fclose($fp);

	
	
	$dist=$upload_dir.$md5.'.plist';
	$last_dist=dirname($app_name).'/app.plist';
	if (!file_exists($dist)) {
		$handle = fopen($dist,'w+');
		fwrite($handle,$str);
		fclose($handle);
	}
	if (!file_exists($last_dist)) {
		$last_handle = fopen($last_dist,'w+');
        	fwrite($last_handle,$last_str);
        	fclose($last_handle);
	} 
	$last_app_url='itms-services://?action=download-manifest%26url=https://'.$_SERVER['SERVER_NAME'].substr($last_dist,1);
	//$app_url='itms-services://?action=download-manifest&url=https://'.$_SERVER['SERVER_NAME'].substr($dist,1);
	$app_url='itms-services://?action=download-manifest%26url=https://'.$_SERVER['SERVER_NAME'].substr($dist,1);

	
}

echo '最新APP地址: http://qr.topscan.com/api.php?text='.$last_app_url.'<br>';
echo '历史记录: http://qr.topscan.com/api.php?text='.$app_url;
?>
