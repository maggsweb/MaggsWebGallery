<?php

// CONFIGURABLE OPTIONS ======================================================================================
require("config_default.php");
include("config.php");

// Include dumpr.php if DEBUG is On and file exists
if (($DEBUG) && file_exists('dump.php')) include 'dump.php';

include 'functions.php';

//============================================================================================================

error_reporting(E_ALL);

ini_set("memory_limit", "-1");

checkCreateDir($GALLERY_ROOT);
checkCreateDir($THUMBS_ROOT);

$mtime = explode(" ", microtime());
$starttime = $mtime[1] + $mtime[0];

//============================================================================================================
$currentDir = getCurrentDirectory();
$currentDirName = getCurrentDirectoryName($currentDir);

$dirs = scanDirectories($GALLERY_ROOT, $THUMBS_ROOT);
$files = scanImages($currentDir, $GALLERY_ROOT, $THUMBS_ROOT, $RESIZE_IMAGES, $MAX_IMAGE_SIZE);

//============================================================================================================
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?=$TITLE?></title>
        <link rel="stylesheet" href="//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.css"/>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="/styles.css" type="text/css"/>
    </head>
<body>

    <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div id="logo">
                <a href="/" title="MaggsWeb">
                    <div id="logoLeft">MaggsWeb</div>
                    <div id="logoRight"><?=$TITLE?></div>
                </a>
            </div>
        </div>
    </nav>

<?php /* ---------------------------------------------------------------------------------------- */ ?>

<?php if ($currentDir && $currentDir == '/') { ?>

    <div id="directory-nav">
        <div class="container">
            <div class="row">
<?php if ($dirs && count($dirs)) { ?>
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
<?php } else { ?>
                No directories found.
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
<?php if ($dirs && count($dirs)) { ?>
<?php foreach ($dirs as $dir) { //dump($dir); //dump($currentDir); ?>
                    <div class="minidirectory">
                        <a href="/<?= $dir['path'] ?>/">
                            <div class="minidirimage greyscale <?= $currentDir == "/{$dir['path']}/" ? 'selected' : ''; ?>"
                                 style="background-image:url('<?= $dir['thumbpath'] ?>');"
                                 data-title="<?= $dir['name'] ?>"
                            ></div>
                        </a>
                    </div>
<?php } ?>
<?php } else { ?>
    No directories found.
<?php } ?>
                    <div class="minidirectory" id="currentdirname" data-current="<?= $currentDirName ?>"><?= $currentDirName ?></div>
                </div>
            </div>
        </div>
    </div>

<?php } ?>

<?php /* ---------------------------------------------------------------------------------------- */ ?>

<?php if ($files && count($files)) { ?>
    <div class="container">

        <h2><?= $currentDirName ?> <span><?= count($files) ?> image<?=count($files)==1?'':'s'?></span></h2>

<?php /* Output an ordered list of hidden image links */ ?>
<?php foreach ($files as $image) { ?>
        <a data-fancybox="gallery"
           href="<?= $image['path'] ?>"
           id="image<?= $image['order'] ?>"
           <?php /* data-caption="<a href='?rotateLeft'>LEFT</a><a href='?rotateRight'>RIGHT</a>" */ ?>
        ></a>
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


    <?php
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $endtime = $mtime[1] + $mtime[0];
    $totaltime = round(($endtime - $starttime),3);
    ?>

    <div id="footer">
        <div class="container">
            &copy; <?=$TITLE?> <?=date('Y')?>
            <span class="right">Page created in <?= $totaltime ?> seconds</span>
        </div>
    </div>

    <div id="debug">
        <?php if ($DEBUG) {    //Debug stuff
            dump($currentDir, '$currentDir');
            dump($currentDirName, '$currentDirName');
            dump($dirs, '$dirs');
            dump($files, '$files');
        } ?>
    </div>

    <script src="//code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.js"></script>
    <script src="/javascript.js" type="text/javascript"></script>

</body>

</html>
