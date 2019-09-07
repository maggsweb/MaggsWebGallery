<?php

//-----------------------
// FUNCTIONS
//-----------------------

/**
 * @param $GALLERY_ROOT
 * @param $THUMBS_ROOT
 *
 * @return array|bool
 */
function scanDirectories($GALLERY_ROOT, $THUMBS_ROOT)
{
    //Bomb out if GALLERY_ROOT not set
    if (!$GALLERY_ROOT) {
        return false;
    }

    $dirs = [];

    // DIRECTORIES
    $files = scandir($GALLERY_ROOT);
    if ($files) {
        foreach ($files as $file) {
            if ($file == '.') {
                continue;
            }
            if ($file == '..') {
                continue;
            }

            // Rename files/folders and save
            if (!preg_match('/^[A-Za-z0-9_\-\.]+$/', $file)) {
                $newfile = preg_replace('/[^A-Za-z0-9_\-\.]+/', '_', $file);
                rename($GALLERY_ROOT.'/'.$file, $GALLERY_ROOT.'/'.$newfile);
            }

            if (is_dir($GALLERY_ROOT.'/'.$file)) {
                checkCreateDir($THUMBS_ROOT.'/'.$file);

                unset($firstimage);

                $image = getLastImage($GALLERY_ROOT.'/'.$file);
                $thumbnail = getOrCreateThumbnail($GALLERY_ROOT.'/'.$file.'/'.$image, $GALLERY_ROOT, $THUMBS_ROOT);
                $directoryName = buildNameFromDirectory($file);

                if ($thumbnail) {
                    $dirs[] = [
                        'name'      => $directoryName,
                        'path'      => $file,
                        'thumbpath' => '/'.$THUMBS_ROOT.'/'.$file.'/'.$image,
                        'numimages' => count(glob($GALLERY_ROOT.'/'.$file.'/*.*')),
                    ];
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
 *
 * @return array|bool
 */
function scanImages($currentDir, $GALLERY_ROOT, $THUMBS_ROOT, $RESIZE_IMAGES, $MAX_IMAGE_SIZE)
{
    //Bomb out if GALLERY_ROOT not set
    if (!$GALLERY_ROOT) {
        return false;
    }

    $files = [];

    if ($currentDir) {
        $images = scandir($GALLERY_ROOT.$currentDir);
        if ($images) {

            // Process Images
            foreach ($images as $k => $file) {
                $fullpath = $GALLERY_ROOT.$currentDir.$file;

                // Remove Non-Image files
                if (!is_image($fullpath)) {
                    unset($images[$k]);
                }

                // Resize images if set
                if ($RESIZE_IMAGES && is_image($fullpath)) {
                    $s = getimagesize($fullpath);
                    if ($s[0] > $MAX_IMAGE_SIZE) {  // Width > 2000px
                        resizeOriginalImage($fullpath);
                    }
                }
            }

            // Build an array of Images, creating the thumbnail image if it doesn't exist
            foreach ($images as $k => $file) {
                getOrCreateThumbnail($GALLERY_ROOT.$currentDir.$file, $GALLERY_ROOT, $THUMBS_ROOT);

                $files[] = [
                    'name'  => $file,
                    'order' => $k,
                    'path'  => '/'.$GALLERY_ROOT.$currentDir.$file,
                    'thumb' => '/'.$THUMBS_ROOT.$currentDir.$file,
                ];
            }
        }
    }

    return $files;
}

/**
 * @param string $path
 */
function checkCreateDir($path)
{
    if ($path) {
        if (!is_dir($path)) {
            mkdir($path);
        }
    }
}

/**
 * @param $imagepath
 * @param $GALLERY_ROOT
 * @param $THUMBS_ROOT
 *
 * @return mixed|void
 */
function getOrCreateThumbnail($imagepath, $GALLERY_ROOT, $THUMBS_ROOT)
{
    $thumbnailpath = str_replace($GALLERY_ROOT, $THUMBS_ROOT, $imagepath);
    if (file_exists($thumbnailpath)) {
        return $thumbnailpath;
    }

    return createThumbnail($imagepath, $thumbnailpath);
}

/**
 * @param string $imagepath
 * @param string $thumbnailpath
 */
function createThumbnail($imagepath, $thumbnailpath)
{
    //include_once 'ImageResize.php';
    if (is_file($imagepath)) {
        $resizeObj = new ImageResize($imagepath);
        $resizeObj->resizeImage(500, 500, 'auto'); //(options: exact, portrait, landscape, auto, crop)
        $resizeObj->saveImage($thumbnailpath);
    }
}

/**
 * @param $originalImagePath
 */
function resizeOriginalImage($originalImagePath)
{
    if (is_file($originalImagePath)) {
        $resizeObj = new ImageResize($originalImagePath);
        $resizeObj->resizeImage(2000, 1000, 'auto'); //(options: exact, portrait, landscape, auto, crop)
        $resizeObj->saveImage($originalImagePath);
    }
}

/**
 * @param string $dirname
 *
 * @return bool
 */
function getLastImage($dirname)
{
    $exts = ['jpg', 'png', 'jpeg', 'gif'];
    $images = scandir($dirname);
    $lastImage = false;
    foreach ($images as $image) {
        if (is_file($dirname.'/'.$image)) {
            $ext = strtolower(pathinfo($dirname.'/'.$image, PATHINFO_EXTENSION));
            if (in_array($ext, $exts)) {
                $lastImage = $image;
            }
        }
    }

    return $lastImage;
}

/**
 * @param string $path
 *
 * @return bool
 */
function is_image($path)
{
    $exts = ['jpg', 'png', 'jpeg', 'gif'];
    if (is_file($path)) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, $exts);
    }

    return false;
}

/**
 * @return bool
 */
function getCurrentDirectory()
{
    $urlArray = explode('/', $_SERVER['REQUEST_URI']);
    $urlArray = array_filter($urlArray);
    $urlArray = array_values($urlArray);
    $segment = array_shift($urlArray);
    if ($segment) {
        return "/$segment/";
    }

    return '/';
}

/**
 * @param type $slug
 *
 * @return bool
 */
function getCurrentDirectoryName($slug)
{
    if ($slug == '/') {
        return 'HOME';
    }

    $slug = str_replace('/', '', $slug);
    if ($slug) {
        $slug = str_replace('_', ' ', $slug);
        $slug = ucwords(strtolower($slug));

        return $slug;
    }

    return false;
}

/**
 * @param $file
 *
 * @return mixed|string
 */
function buildNameFromDirectory($file)
{
    $name = str_replace(['_', '-'], ' ', $file);
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

    public function __construct($fileName)
    {
        // *** Open up the file
        $this->image = $this->openImage($fileName);

        // *** Get width and height
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    //# --------------------------------------------------------

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

    //# --------------------------------------------------------

    public function resizeImage($newWidth, $newHeight, $option = 'auto')
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

    //# --------------------------------------------------------

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

        return ['optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight];
    }

    //# --------------------------------------------------------

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
            } elseif ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            } else {
                // *** Sqaure being resized to a square
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            }
        }

        return ['optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight];
    }

    //# --------------------------------------------------------

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

        return ['optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight];
    }

    //# --------------------------------------------------------

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

    //# --------------------------------------------------------

    public function saveImage($savePath, $imageQuality = '90')
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

    //# --------------------------------------------------------
}

/**
 * Dumpr - Dump any resource with syntax highlighting, indenting and variable type information to the screen in a very intuitive format.
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *      http://www.opensource.org/licenses/lgpl-license.php
 *
 * @author    Jari Berg Jensen &lt;jari@razormotion.com&gt;
 *           http://www.razormotion.com/software/dumpr/
 * @license   LGPL
 *
 * @version  1.7
 * Modified  2005/03/31
 *
 * Changes since Revision 1.0
 *     Revision 1.0
 *         - Initial release
 *     Revision 1.1
 *         - Added syntax highlighting
 *     Revision 1.2
 *         - Improved the regular expressions
 *     Revision 1.3
 *         - Fixed a few syntax highlighting bugs
 *     Revision 1.4
 *         - Fixed minor bugs
 *     Revision 1.5
 *         - Expanded numbers (ie. int(10) => int(2) 10, float(128.64) => float(6) 128.64 etc.)
 *         - Removed the extra line paddings in the output
 *     Revision 1.6
 *         - Added a simple regular expression to pretty the output (html source)
 *     Revision 1.7
 *        - Added syntax highlighting for types (ie. type of (mysql...), type of (stream) etc.)
 *        - Added syntax highlighting for resource(..)
 */

/**
 * Dump any resource with syntax highlighting, indenting and variable type information.
 *
 * @param mixed $data
 * @param bool  $return
 *
 * @return string
 */
function dump($data, $label = '', $return = false)
{
    $debug = debug_backtrace();
    $callingFile = $debug[0]['file'];
    $callingFileLine = $debug[0]['line'];

    ob_start();
    var_dump($data);
    $c = ob_get_contents();
    ob_end_clean();

    $c = preg_replace("/\r\n|\r/", "\n", $c);
    $c = str_replace("]=>\n", '] = ', $c);
    $c = preg_replace('/= {2,}/', '= ', $c);
    $c = preg_replace("/\[\"(.*?)\"\] = /i", '[$1] = ', $c);
    $c = preg_replace('/  /', '    ', $c);
    $c = preg_replace('/""(.*?)"/i', '"$1"', $c);

    //$c = htmlspecialchars($c, ENT_NOQUOTES);

    // Expand numbers (ie. int(10) => int(2) 10, float(128.64) => float(6) 128.64 etc.)
    // ORIGINAL
    //$c = preg_replace("/(int|float)\(([0-9\.]+)\)/ie", "'$1('.strlen('$2').') <span class=\"number\">$2</span>'", $c);
    // @CM
    //$c = preg_replace("/(int|float)\(([0-9\.]+)\)/i", "$1(".strlen('$2').") <span class=\"number\">$2</span>", $c);
    $c = preg_replace("/(int|float)\(([0-9\.]+)\)/i", '$1() <span class="number">$2</span>', $c);

    // Syntax Highlighting of Strings. This seems cryptic, but it will also allow non-terminated strings to get parsed.
    $c = preg_replace("/(\[[\w ]+\] = string\([0-9]+\) )\"(.*?)/sim", '$1<span class="string">"', $c);
    $c = preg_replace("/(\"\n{1,})( {0,}\})/sim", '$1</span>$2', $c);
    $c = preg_replace("/(\"\n{1,})( {0,}\[)/sim", '$1</span>$2', $c);
    $c = preg_replace("/(string\([0-9]+\) )\"(.*?)\"\n/sim", "$1<span class=\"string\">\"$2\"</span>\n", $c);

    $regex = [
        // Numberrs
        'numbers' => ['/(^|] = )(array|float|int|string|resource|object\(.*\)|\&amp;object\(.*\))\(([0-9\.]+)\)/i', '$1$2(<span class="number">$3</span>)'],
        // Keywords
        'null' => ['/(^|] = )(null)/i', '$1<span class="keyword">$2</span>'],
        'bool' => ['/(bool)\((true|false)\)/i', '$1(<span class="keyword">$2</span>)'],
        // Types
        'types' => ['/(of type )\((.*)\)/i', '$1(<span class="type">$2</span>)'],
        // Objects
        'object' => ['/(object|\&amp;object)\(([\w]+)\)/i', '$1(<span class="object">$2</span>)'],
        // Function
        'function' => ['/(^|] = )(array|string|int|float|bool|resource|object|\&amp;object)\(/i', '$1<span class="function">$2</span>('],
    ];

    foreach ($regex as $x) {
        $c = preg_replace($x[0], $x[1], $c);
    }

    $style = '
    /* outside div - it will float and match the screen */
    .dumpr {
        margin: 2px;
        padding: 2px;
        background-color: #fbfbfb;
        float: left;
        clear: both;
    }
    /* font size and family */
    .dumpr pre {
        color: #000000;
        font-size: 9pt;
        font-family: "Courier New",Courier,Monaco,monospace;
        margin: 0px;
        padding-top: 5px;
        padding-bottom: 7px;
        padding-left: 9px;
        padding-right: 9px;
    }
    /* inside div */
    .dumpr div {
        background-color: #fcfcfc;
        border: 1px solid #d9d9d9;
        float: left;
        clear: both;
    }
    /* syntax highlighting */
    .dumpr span.string {color: #c40000;}
    .dumpr span.number {color: #ff0000;}
    .dumpr span.keyword {color: #007200;}
    .dumpr span.function {color: #0000c4;}
    .dumpr span.object {color: #ac00ac;}
    .dumpr span.type {color: #0072c4;}
    ';

    $style = preg_replace('/ {2,}/', '', $style);
    $style = preg_replace("/\t|\r\n|\r|\n/", '', $style);
    $style = preg_replace("/\/\*.*?\*\//i", '', $style);
    $style = str_replace('}', '} ', $style);
    $style = str_replace(' {', '{', $style);
    $style = trim($style);

    $c = trim($c);
    $c = preg_replace("/\n<\/span>/", "</span>\n", $c);

    if ($label == '') {
        $line1 = '';
    } else {
        $line1 = "<strong>$label</strong> \n";
    }

    $out = "\n<!-- Dumpr Begin -->
        <style type='text/css'>$style</style>
            <div class='dumpr'>
                <div><pre>$line1 $callingFile : $callingFileLine \n$c\n</pre></div>
            </div>
            <div style='clear:both;'>&nbsp;</div>
        \n<!-- Dumpr End -->\n";

    if ($return) {
        return $out;
    } else {
        echo $out;
    }
}
