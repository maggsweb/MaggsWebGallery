<?php

error_reporting(E_ALL);
//error_reporting(0);

ini_set("memory_limit", "512M");

define('DEBUG',1);

if(DEBUG) {
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    if (file_exists('dumpr.php'))
        include 'dumpr.php';
}

include 'functions.php';

define('GALLERY_ROOT', 'photos');
define('THUMBS_ROOT', '_thumbs');

checkCreateDir(GALLERY_ROOT);
checkCreateDir(THUMBS_ROOT);

//============================================================================================================
$currentDir = getCurrentDirectory();
$currentDirName = getCurrentDirectoryName($currentDir);

$dirs = scanDirectories();
$files = scanImages($currentDir);

//============================================================================================================
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Gallery</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.css"/>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
        <link href="/styles.css" rel="stylesheet" type="text/css"/>
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

<?php /* ---------------------------------------------------------------------------------------- */ ?>

<?php if ($currentDir == '/') { ?>

    <div id="directory-nav">
        <div class="container">
            <div class="row">
<?php foreach ($dirs as $dir) { //dumpr($dir); ?>
                <div class="col-xs-12 col-sm-4 col-lg-3">
                    <a href="/<?= $dir['path'] ?>/">
                        <div class="dirimage greyscale" style="background-image:url('<?= $dir['thumbpath'] ?>');">
                            <h4><?= $dir['name'] ?></h4>
                            <h5><?= $dir['numimages'] ?> image<?=$dir['numimages']==1?'':'s'?></h5>
                        </div>
                    </a>
                </div>
<?php } ?>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

<?php } else { ?>

    <div id="mini-directory-nav">
        <div class="grey">
            <div class="container">
                <div class="scroll">
<?php foreach ($dirs as $dir) { //dumpr($dir); dumpr($currentDir); ?>
                    <div class="minidirectory">
                        <!--<h4><?= ucfirst($dir['name']) ?></h4>-->
                        <a href="/<?= $dir['path'] ?>/">
                            <div class="minidirimage greyscale <?= $currentDir == "/{$dir['path']}/" ? 'selected' : ''; ?>" style="background-image:url('<?= $dir['thumbpath'] ?>');"></div>
                        </a>
                    </div>
<?php } ?>
                </div>
            </div>
        </div>
    </div>


<?php } ?>

<?php /* ---------------------------------------------------------------------------------------- */ ?>

<?php if (count($files)) { ?>
    <div class="container">

        <h2><?= $currentDirName ?> <span><?= count($files) ?> image<?=count($files)==1?'':'s'?></span></h2>

<?php /* Output an ordered list of hidden image links */ ?>
<?php foreach ($files as $image) { ?>
        <a data-fancybox="gallery" href="<?= $image['path'] ?>" id="image<?= $image['order'] ?>"></a>
<?php } ?>

<?php /* Output a `columned` list of images */ ?>
        <div id="images">
            <div class="row">
<?php
                $cols = 4;
                $col = 1;
                $imageColumns = array();
                $i = 1;
                foreach ($files as $file) {
                    $imageColumns[$col][$i] = $file;
                    $col++;
                    $i++;
                    $col = $col > 4 ? 1 : $col;
                }
                foreach ($imageColumns as $column => $imageArray) { ?>
                <div class="col-xs-4 col-md-3">
<?php foreach ($imageArray as $image) { ?>
                    <div class="image" data-trigger="<?= $image['order'] ?>">
                        <img src="<?= $image['thumb'] ?>"/>
                    </div>
<?php } ?>
                </div>
<?php } ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (DEBUG) {    //Debug stuff
    //-----------------------
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $starttime);
    ?>
    <p>This page was created in <?= $totaltime ?> seconds</p>
    <?php dumpr($currentDir, '$currentDir'); ?>
    <?php dumpr($currentDirName, '$currentDirName'); ?>
    <?php dumpr($dirs, '$dirs'); ?>
    <?php dumpr($files, '$files'); ?>
<?php } ?>

<script src="//code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.js"></script>
<script>

    jQuery(document).ready(function () {

//            $(window).scroll(function() {
//                if ($(this).scrollTop()>50) {
//                    $('#mini-directory-nav').fadeIn();
//                    $('#directory-nav').fadeOut();
//                } else {
//                  $('#mini-directory-nav').fadeOut();
//                  $('#directory-nav').fadeIn();
//                }
//            });

        $('.image').on('click', function () {
            $image = $(this).attr('data-trigger');
            $('#image' + $image).trigger('click');
        });

    });

</script>

</body>

</html>
