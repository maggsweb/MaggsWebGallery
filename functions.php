<?php
//-----------------------
// FUNCTIONS
//-----------------------


/**
 * @CM
 * 
 * @param type $path
 */
function checkCreateDir($path){
    if(!is_dir($path)){
        mkdir($path);
    }
    if(!is_dir($path)){
        header("Location: system_check.php");
        exit;
    }
}


/**
 * @CM
 * 
 * @param type $imagepath
 * @return type
 */
function getOrCreateThumbnail($imagepath){
    $thumbnailpath = str_replace(GALLERY_ROOT,THUMBS_ROOT,$imagepath);
    if(file_exists($thumbnailpath)){
        return $thumbnailpath;
    }
    return createThumbnail($imagepath,$thumbnailpath);
}


/**
 * @CM
 * 
 * @param type $imagepath
 * @param type $thumbnailpath
 */
function createThumbnail($imagepath,$thumbnailpath){
    include_once 'ImageResize.php';
    $resizeObj = new ImageResize($imagepath);
    $resizeObj->resizeImage(500, 500, 'auto'); //(options: exact, portrait, landscape, auto, crop)
    $resizeObj->saveImage($thumbnailpath);
}




/**
 * @CM
 * 
 * @param type $dirname
 * @return boolean
 */
function getfirstImage($dirname) {
    $exts = array("jpg", "png", "jpeg", "gif");
    $images = scandir($dirname);
    foreach($images as $image){
        if(is_file($dirname.'/'.$image)){
            $ext = strtolower(pathinfo($dirname.'/'.$image,PATHINFO_EXTENSION));
            if(in_array($ext,$exts)){
                return $image;
            }
        }
    }
    return false;
}



/**
 * 
 * @return boolean
 */
function getCurrentDirectory(){
    
    $urlArray = explode('/',$_SERVER['REQUEST_URI']);
    $urlArray = array_filter($urlArray);    
    $urlArray = array_values($urlArray);    
    $segment = array_shift($urlArray);
    if($segment){
        return "/$segment/";
    }
    return '/';
    
}


/**
 * @CM
 * 
 * @param type $slug
 * @return boolean
 */
function getCurrentDirectoryName($slug){
    $slug = str_replace('/','',$slug);
    if($slug){
        $slug = str_replace('_',' ',$slug);
        $slug = ucfirst(strtolower($slug));
        return $slug;
    }
    return false;
}



//
//// Rotate JPG pictures
//if (preg_match("/.jpg$|.jpeg$/i", $_GET['filename'])) {
//    if (function_exists('exif_read_data') && function_exists('imagerotate')) {
//        $exif = exif_read_data($_GET['filename']);
//        $ort = $exif['IFD0']['Orientation'];
//        $degrees = 0;
//        switch ($ort) {
//            case 6: // 90 rotate right
//                $degrees = 270;
//                break;
//            case 8:    // 90 rotate left
//                $degrees = 90;
//                break;
//        }
//        if ($degrees != 0)
//            $target = imagerotate($target, $degrees, 0);
//    }
//}




//function readEXIF($file) {
//    $exif_data = "";
//    $exif_idf0 = exif_read_data($file, 'IFD0', 0);
//    $emodel = $exif_idf0['Model'];
//
//    $efocal = $exif_idf0['FocalLength'];
//    list($x, $y) = explode('/', $efocal);
//    $efocal = round($x / $y, 0);
//
//    $exif_exif = exif_read_data($file, 'EXIF', 0);
//    $eexposuretime = $exif_exif['ExposureTime'];
//
//    $efnumber = $exif_exif['FNumber'];
//    list($x, $y) = explode('/', $efnumber);
//    $efnumber = round($x / $y, 0);
//
//    $eiso = $exif_exif['ISOSpeedRatings'];
//
//    $exif_date = exif_read_data($file, 'IFD0', 0);
//    $edate = $exif_date['DateTime'];
//    if (strlen($emodel) > 0 OR strlen($efocal) > 0 OR strlen($eexposuretime) > 0 OR strlen($efnumber) > 0 OR strlen($eiso) > 0)
//        $exif_data .= "::";
//    if (strlen($emodel) > 0)
//        $exif_data .= "$emodel";
//    if ($efocal > 0)
//        $exif_data .= " | $efocal" . "mm";
//    if (strlen($eexposuretime) > 0)
//        $exif_data .= " | $eexposuretime" . "s";
//    if ($efnumber > 0)
//        $exif_data .= " | f$efnumber";
//    if (strlen($eiso) > 0)
//        $exif_data .= " | ISO $eiso";
//    return($exif_data);
//}

//function checkpermissions($file) {
//    global $messages;
//    if (substr(decoct(fileperms($file)), -1, strlen(fileperms($file))) < 4 OR substr(decoct(fileperms($file)), -3, 1) < 4)
//        $messages = "At least one file or folder has wrong permissions. Learn how to <a href='http://minigal.dk/faq-reader/items/how-do-i-change-file-permissions-chmod.html' target='_blank'>set file permissions</a>";
//}


//-----------------------
// PHP ENVIRONMENT CHECK
//-----------------------
//if (!function_exists('exif_read_data') && $display_exif == 1) {
//    $display_exif = 0;
//    $messages = "Error: PHP EXIF is not available. Set &#36;display_exif = 0; in config.php to remove this message";
//}


//function is_directory($filepath) {
//    // $filepath must be the entire system path to the file
//    if (!@opendir($filepath))
//        return FALSE;
//    else {
//        return TRUE;
//        closedir($filepath);
//    }
//}

//function padstring($name, $length) {
//    global $label_max_length;
//    if (!isset($length))
//        $length = $label_max_length;
//    if (strlen($name) > $length) {
//        return substr($name, 0, $length) . "...";
//    } else
//        return $name;
//}