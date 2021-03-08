<?php
namespace swa\digitalphotoframe\lib;

class FolderUtil {
	
	public static function listFiles($dir, $ext = "jpg,jpeg"){
		if(!is_dir($dir)) {
			return;
		}
		$ffs = scandir($dir);

		unset($ffs[array_search('.', $ffs, true)]);
		unset($ffs[array_search('..', $ffs, true)]);

		// prevent empty ordered elements
		if (count($ffs) < 1)
			return;

		$retVal = array();
		$allFileExt = explode(",", $ext);
		foreach($ffs as $ff){
			
			foreach($allFileExt as $currentFileExt) {
				if(strpos(strtolower($ff), $currentFileExt) && strpos($dir, "thumbs") == 0 ) {
					$retVal[] = $dir.$ff;
				}
			
				if(is_dir($dir.'/'.$ff)) {
					//$retVal = array_merge($retVal, listFiles($dir.'/'.$ff, $currentFileExt));
				}
			}
		}
		return $retVal;
	}
}
?>