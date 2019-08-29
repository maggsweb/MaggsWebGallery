<?php
//-----------------------
// FUNCTIONS
//-----------------------


/**
 * @param $GALLERY_ROOT
 * @param $THUMBS_ROOT
 * @return array|bool
 */
function scanDirectories($GALLERY_ROOT, $THUMBS_ROOT)
{
    //Bomb out if GALLERY_ROOT not set
    if(!$GALLERY_ROOT) return false;

    $dirs = array();

    // DIRECTORIES
    $files = scandir($GALLERY_ROOT);
    if ($files) {

        foreach ($files as $file) {

            if ($file == '.') continue;
            if ($file == '..') continue;

            // Rename files/folders and save
            if (!preg_match('/^[A-Za-z0-9_\-\.]+$/', $file)) {
                $newfile = preg_replace('/[^A-Za-z0-9_\-\.]+/', '_', $file);
                rename($GALLERY_ROOT . '/' . $file, $GALLERY_ROOT . '/' . $newfile);
            }

            if (is_dir($GALLERY_ROOT . '/' . $file)) {

                checkCreateDir($THUMBS_ROOT . '/' . $file);

                unset($firstimage);

                $image = getLastImage($GALLERY_ROOT . '/' . $file);
                $thumbnail = getOrCreateThumbnail($GALLERY_ROOT . '/' . $file . '/' . $image, $GALLERY_ROOT, $THUMBS_ROOT);
                $directoryName = buildNameFromDirectory($file);

                if ($thumbnail) {

                    $dirs[] = array(
                        "name" => $directoryName,
                        "path" => $file,
                        "thumbpath" => "/" . $THUMBS_ROOT . '/' . $file . "/" . $image,
                        "numimages" => count(glob($GALLERY_ROOT . '/' . $file . '/*.*'))
                    );

                }
            }
        }
    }
    return $dirs;
}

/**
 * @param $currentDir
 * @param $MAX_IMAGE_SIZE
 * @param $GALLERY_ROOT
 * @param $THUMBS_ROOT
 * @return array|bool
 */
function scanImages($currentDir, $GALLERY_ROOT, $THUMBS_ROOT, $RESIZE_IMAGES, $MAX_IMAGE_SIZE)
{
    //Bomb out if GALLERY_ROOT not set
    if (!$GALLERY_ROOT) return false;

    $files = array();

    if($currentDir) {

        $images = scandir($GALLERY_ROOT . $currentDir);
        if ($images) {

            // Process Images
            foreach ($images as $k => $file) {

                $fullpath = $GALLERY_ROOT . $currentDir . $file;

                // Remove Non-Image files
                if (!is_image($fullpath)) {
                    unset($images[$k]);
                }

                // Resize images if set
                if($RESIZE_IMAGES && is_image($fullpath)) {
                    $s = getimagesize($fullpath);
                    if ($s[0] > $MAX_IMAGE_SIZE) {  // Width > 2000px
                        resizeOriginalImage($fullpath);
                    }
                }
            }

            // Build an array of Images, creating the thumbnail image if it doesn't exist
            foreach ($images as $k => $file) {

                getOrCreateThumbnail($GALLERY_ROOT . $currentDir . $file, $GALLERY_ROOT, $THUMBS_ROOT);

                $files[] = array(
                    "name" => $file,
                    "order" => $k,
                    "path" => '/' . $GALLERY_ROOT . $currentDir . $file,
                    "thumb" => '/' . $THUMBS_ROOT . $currentDir . $file,
                );
            }
        }
    }
    return $files;
}

/**
 *
 * @param string $path
 */
function checkCreateDir($path){
    if($path) {
        if (!is_dir($path)) {
            mkdir($path);
        }
    }
}

/**
 * @param $imagepath
 * @param $GALLERY_ROOT
 * @param $THUMBS_ROOT
 * @return mixed|void
 */
function getOrCreateThumbnail($imagepath, $GALLERY_ROOT, $THUMBS_ROOT){
    $thumbnailpath = str_replace($GALLERY_ROOT,$THUMBS_ROOT,$imagepath);
    if(file_exists($thumbnailpath)){
        return $thumbnailpath;
    }
    return createThumbnail($imagepath,$thumbnailpath);
}

/**
 * @param string $imagepath
 * @param string $thumbnailpath
 */
function createThumbnail($imagepath,$thumbnailpath){
    //include_once 'ImageResize.php';
    if(is_file($imagepath)){
        $resizeObj = new ImageResize($imagepath);
        $resizeObj->resizeImage(500, 500, 'auto'); //(options: exact, portrait, landscape, auto, crop)
        $resizeObj->saveImage($thumbnailpath);
    }
}

/**
 * @param $originalImagePath
 */
function resizeOriginalImage($originalImagePath){
    if(is_file($originalImagePath)){
        $resizeObj = new ImageResize($originalImagePath);
        $resizeObj->resizeImage(2000, 1000, 'auto'); //(options: exact, portrait, landscape, auto, crop)
        $resizeObj->saveImage($originalImagePath);
    }
}

/**
 * @param string $dirname
 * @return boolean
 */
function getLastImage($dirname) {
    $exts = array("jpg", "png", "jpeg", "gif");
    $images = scandir($dirname);
    $lastImage = false;
    foreach($images as $image){
        if(is_file($dirname.'/'.$image)){
            $ext = strtolower(pathinfo($dirname.'/'.$image,PATHINFO_EXTENSION));
            if(in_array($ext,$exts)){
                $lastImage = $image;
            }
        }
    }
    return $lastImage;
}

/**
 * @param string $path
 * @return boolean
 */
function is_image($path){
    $exts = array("jpg", "png", "jpeg", "gif");
    if(is_file($path)){
        $ext = strtolower(pathinfo($path,PATHINFO_EXTENSION));
        return in_array($ext,$exts);
    }
    return false;
}

/**
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
 * @param type $slug
 * @return boolean
 */
function getCurrentDirectoryName($slug){

    if($slug=='/') return 'HOME';

    $slug = str_replace('/','',$slug);
    if($slug){
        $slug = str_replace('_',' ',$slug);
        $slug = ucwords(strtolower($slug));
        return $slug;
    }
    return false;
}

/**
 * @param $file
 * @return mixed|string
 */
function buildNameFromDirectory($file){
    $name = str_replace(array('_','-'),' ',$file);
    $name = ucwords($name);
    return $name;
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


//function readEXIF($file) {
//    $exif_data = "";
//    $exif_idf0 = exif_read_data ($file,'IFD0' ,0 );
//    $emodel = $exif_idf0['Model'];
//
//    $efocal = $exif_idf0['FocalLength'];
//    list($x,$y) = explode('/', $efocal);
//    $efocal = round($x/$y,0);
//
//    $exif_exif = exif_read_data ($file,'EXIF' ,0 );
//    $eexposuretime = $exif_exif['ExposureTime'];
//
//    $efnumber = $exif_exif['FNumber'];
//    list($x,$y) = explode('/', $efnumber);
//    $efnumber = round($x/$y,0);
//
//    $eiso = $exif_exif['ISOSpeedRatings'];
//
//    $exif_date = exif_read_data ($file,'IFD0' ,0 );
//    $edate = $exif_date['DateTime'];
//    if (strlen($emodel) > 0 OR strlen($efocal) > 0 OR strlen($eexposuretime) > 0 OR strlen($efnumber) > 0 OR strlen($eiso) > 0) $exif_data .= "::";
//    if (strlen($emodel) > 0) $exif_data .= "$emodel";
//    if ($efocal > 0) $exif_data .= " | $efocal" . "mm";
//    if (strlen($eexposuretime) > 0) $exif_data .= " | $eexposuretime" . "s";
//    if ($efnumber > 0) $exif_data .= " | f$efnumber";
//    if (strlen($eiso) > 0) $exif_data .= " | ISO $eiso";
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



//======================================================================================================================
//======================================================================================================================
//======================================================================================================================
//======================================================================================================================
//======================================================================================================================
//======================================================================================================================
//======================================================================================================================

class ImageResize
{

    // *** Class variables
    private $image;
    private $width;
    private $height;
    private $imageResized;

    function __construct($fileName)
    {
        // *** Open up the file
        $this->image = $this->openImage($fileName);

        // *** Get width and height
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);

    }

    ## --------------------------------------------------------

    private function openImage($file)
    {
        // *** Get extension
        $extension = strtolower(strrchr($file, '.'));

        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                $img = @imagecreatefromjpeg($file);
                break;
            case '.gif':
                $img = @imagecreatefromgif($file);
                break;
            case '.png':
                $img = @imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }
        return $img;
    }

    ## --------------------------------------------------------

    public function resizeImage($newWidth, $newHeight, $option = "auto")
    {

        // *** Get optimal width and height - based on $option
        $optionArray = $this->getDimensions($newWidth, $newHeight, $option);

        $optimalWidth = $optionArray['optimalWidth'];
        $optimalHeight = $optionArray['optimalHeight'];

        // *** Resample - create image canvas of x, y size
        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);

        // Added @CM 2015-12-11 to handle transparent PNGs
        imagealphablending($this->imageResized, false);
        imagesavealpha($this->imageResized, true);
        $transparent = imagecolorallocatealpha($this->imageResized, 255, 255, 255, 127);
        imagefilledrectangle($this->imageResized, 0, 0, $this->width, $this->height, $transparent);
        // End

        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

        // *** if option is 'crop', then crop too
        if ($option == 'crop') {
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }
    }

    ## --------------------------------------------------------

    private function getDimensions($newWidth, $newHeight, $option)
    {

        switch ($option) {
            case 'exact':
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    ## --------------------------------------------------------

    private function getSizeByFixedHeight($newHeight)
    {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    private function getSizeByFixedWidth($newWidth)
    {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    private function getSizeByAuto($newWidth, $newHeight)
    {
        if ($this->height < $this->width) {
            // *** Image to be resized is wider (landscape)
            $optimalWidth = $newWidth;
            $optimalHeight = $this->getSizeByFixedWidth($newWidth);
        } elseif ($this->height > $this->width) {
            // *** Image to be resized is taller (portrait)
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight = $newHeight;
        } else {
            // *** Image to be resizerd is a square
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            } else {
                // *** Sqaure being resized to a square
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            }
        }

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    ## --------------------------------------------------------

    private function getOptimalCrop($newWidth, $newHeight)
    {

        $heightRatio = $this->height / $newHeight;
        $widthRatio = $this->width / $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth = $this->width / $optimalRatio;

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    ## --------------------------------------------------------

    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
    {
        // *** Find center - this will be used for the crop
        $cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
        $cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

        $crop = $this->imageResized;
        //imagedestroy($this->imageResized);
        // *** Now crop from center to exact requested size
        $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($this->imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);
    }

    ## --------------------------------------------------------

    public function saveImage($savePath, $imageQuality = "90")
    {

        // *** Get extension
        $extension = strrchr($savePath, '.');
        $extension = strtolower($extension);

        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->imageResized, $savePath, $imageQuality);
                }
                break;

            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->imageResized, $savePath);
                }
                break;

            case '.png':
                /* This doesn't work for transparent PNG's */
                // *** Scale quality from 0-100 to 0-9
                $scaleQuality = round(($imageQuality / 100) * 9);
                // *** Invert quality setting as 0 is best, not 9
                $invertScaleQuality = 9 - $scaleQuality;
                if (imagetypes() & IMG_PNG) {
                    imagepng($this->imageResized, $savePath, $invertScaleQuality);
                }
                break;

            default:
                // *** No extension - No save.
                break;
        }

        imagedestroy($this->imageResized);
    }

    ## --------------------------------------------------------
}
