<?php

error_reporting(E_ALL);
//error_reporting(E_ERROR);
//error_reporting(0);

ini_set("memory_limit", "512M");

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

if(file_exists('dumpr.php')){
    include 'dumpr.php';
}

include 'functions.php';

define('GALLERY_ROOT', 'photos');
define('THUMBS_ROOT',  '_thumbs');

checkCreateDir(GALLERY_ROOT);
checkCreateDir(THUMBS_ROOT);

$dirs = $files = false;

//============================================================================================================
$currentDir     = getCurrentDirectory(); 
$currentDirName = getCurrentDirectoryName($currentDir); 

//============================================================================================================
// DIRECTORIES
$directories = scandir(GALLERY_ROOT);
if($directories){
    
    $dirs = array();

    foreach($directories as $directory){

        if (is_dir(GALLERY_ROOT.'/'.$directory)) {

            if ($directory != "." && $directory != "..") {

                checkCreateDir(THUMBS_ROOT.'/'.$directory);

                unset($firstimage);

                $firstimage = getfirstImage(GALLERY_ROOT.'/'.$directory);

                $thumbnail = getOrCreateThumbnail(GALLERY_ROOT.'/'.$directory.'/'.$firstimage);

                if ($thumbnail) {

                    $dirs[] = array(
                        "name" => $directory,
                        "path" => $directory,
                        //"imagepath" => "/" . GALLERY_ROOT.'/'.$directory . "/" . $firstimage,
                        "thumbpath" => "/" . THUMBS_ROOT.'/'.$directory . "/" . $firstimage,
                        "numimages" => count(glob(GALLERY_ROOT.'/'.$directory.'/*.*'))
                    );

                } 
            }
        }
    }

}
//dumpr($dirs);

//============================================================================================================
// IMAGES
$images = scandir(GALLERY_ROOT . $currentDir);
if ($images) {
    
    $files = array();
    
    // Remove Non-Image files
    foreach($images as $k => $file){
        if ($file == "." || $file == ".." || !preg_match("/.jpg$|.gif$|.png$/i", $file)) {
            unset($images[$k]);
        }
    }
    
    // Build an array of Images, creating the thumbnail image if it doesn't exist
    foreach($images as $k => $file){
                
        getOrCreateThumbnail(GALLERY_ROOT . $currentDir  . $file);

        $files[] = array(
            "name" => $file,
            "order" => $k,
            "path"  => '/'.GALLERY_ROOT.$currentDir.$file,
            "thumb" => '/'.THUMBS_ROOT.$currentDir.$file,
            "isize" => filesize(GALLERY_ROOT.$currentDir.$file),
            "tsize" => filesize(THUMBS_ROOT.$currentDir.$file),
        );
               
    }
} 
//dumpr($files);

//============================================================================================================
?>
<!doctype html>
<html lang="en">
  <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Gallery</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.css" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">        
        <link href="/css/styles.css" rel="stylesheet" type="text/css"/>
    </head>        
    <body>
        
         <!-- Fixed navbar -->
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div id="logo">
                    <a href="/" title="MaggsWeb">
                    <div id="logoLeft">MaggsWeb</div>
                    <div id="logoRight">Gallery</div> <!-- OPTIONAL -->
                    </a>
                </div>
            </div>
        </nav>
         
        <?php if($currentDir){  ?>
         
        <div id="mini-directory-nav">
            <div class="grey">
                <div class="container">
                    <div class="scroll">
                        <?php foreach($dirs as $dir){ //dumpr($dir); dumpr($currentDir); ?>
                        <?php $class = $currentDir == "/{$dir['path']}/" ? 'selected' : ''; ?>
                        <div class="minidirectory">
                            <!--<h4><?=ucfirst($dir['name'])?></h4>-->
                            <a href="/<?=$dir['path']?>/">
                                <div class="minidirimage greyscale <?=$class?>" style="background-image:url('<?=$dir['thumbpath']?>');"></div>
                            </a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php } else { ?> 
         
        <div id="directory-nav"> 
            <div class="container">
                <div class="row">
                    <?php foreach($dirs as $dir){ //dumpr($dir); ?>
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <a href="/<?=$dir['path']?>/">
                            <div class="dirimage greyscale" style="background-image:url('<?=$dir['thumbpath']?>');">
                                <h4><?=ucfirst($dir['name'])?></h4>
                                <h5><?=$dir['numimages']?> images</h5>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
         
        <?php } ?>
         
         
        <div class="container">
            
            <?php if($currentDirName){ ?>
            <h2><?=$currentDirName?> <span><?=count($files)?> images</span></h2>
            <?php } ?>
        
            <?php /* Output an ordered list of hidden image links */ ?>
            <?php if(count($files)){ ?>
            <?php foreach($files as $image){?>
            <a data-fancybox="gallery" href="<?=$image['path']?>" id="image<?=$image['order']?>"></a>
            <?php } ?>
            <?php } ?>

            <?php /* Output a `columned` list of images */ ?>
            <?php if(count($files)){ ?>
            <div id="images">
                <div class="row">
                <?php
                $cols = 4;
                $col = 1;
                $imageColumns = array();
                    $i=1;
                    foreach($files as $file){
                        $imageColumns[$col][$i] = $file;
                        $col++;
                        $i++;
                        $col = $col > 4 ? 1 : $col;
                    }
                    foreach($imageColumns as $column => $imageArray){  ?>
                    <div class="col-xs-4 col-md-3">
                    <?php foreach($imageArray as $image){ ?>
                        <div class="image" data-trigger="<?=$image['order']?>">
                            <img src="<?=$image['thumb']?>" />
                        </div>
                    <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

     
        <?php
        //Debug stuff
        //-----------------------
        $mtime = microtime();
        $mtime = explode(" ",$mtime);
        $mtime = $mtime[1] + $mtime[0];
        $endtime = $mtime;
        $totaltime = ($endtime - $starttime);
        ?>
            <p style="color:#fff">This page was created in <?=$totaltime?> seconds</p>
        </div>
        
        <script src="//code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.js"></script>
        <script>
        
        jQuery(document).ready(function(){
        
//            $(window).scroll(function() {
//                if ($(this).scrollTop()>50) {
//                    $('#mini-directory-nav').fadeIn();
//                    $('#directory-nav').fadeOut();
//                } else {
//                  $('#mini-directory-nav').fadeOut();
//                  $('#directory-nav').fadeIn();
//                }
//            });
            
            $('.image').on('click',function(){
               $image = $(this).attr('data-trigger');
               $('#image'+$image).trigger('click');
            });

        });
        
        </script>
        
    </body>
    
</html>
