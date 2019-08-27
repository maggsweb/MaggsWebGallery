<?php

// CONFIGURABLE OPTIONS ======================================================================================

$debug = (bool)preg_match("/.local/", $_SERVER['SERVER_NAME']);

/**
 *
 */
$HTMLtitle = 'Gallery';

/**
 * Top Level Directory that store images
 * -------------------------------------
 * The directory name will be used for ordering and title
 */
$rootImageDirectory = 'photos';

/**
 * Top Level Directory for automated thumbnail creation
 * ----------------------------------------------------
 * This directory can be deleted, and it will regenerate on demand
 */
$rootThumbnailDirectory = '_thumbs';

/**
 * Maximum Image Size Width
 * ------------------------
 * Original images over this size width will be resized to this width
 */
$maxImageSizeWidth = 2000;


//============================================================================================================

error_reporting($debug);

ini_set("memory_limit", "-1");

define('DEBUG',$debug);
define('GALLERY_ROOT', $rootImageDirectory);
define('THUMBS_ROOT', $rootThumbnailDirectory);

include 'functions.php';
if(DEBUG) include 'dump.php';

checkCreateDir(GALLERY_ROOT);
checkCreateDir(THUMBS_ROOT);

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

//============================================================================================================
$currentDir = getCurrentDirectory();
$currentDirName = getCurrentDirectoryName($currentDir);

$dirs = scanDirectories();
$files = scanImages($currentDir,$maxImageSizeWidth);

//============================================================================================================
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?=$HTMLtitle?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.css"/>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="/styles.css" type="text/css"/>
    </head>
<body>

    <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div id="logo">
                <a href="/" title="MaggsWeb">
                    <div id="logoLeft">MaggsWeb</div>
                    <div id="logoRight"><?=$HTMLtitle?></div>
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
<?php foreach ($dirs as $dir) { //dump($dir); //dump($currentDir); ?>
                    <div class="minidirectory">
                        <a href="/<?= $dir['path'] ?>/">
                            <div class="minidirimage greyscale <?= $currentDir == "/{$dir['path']}/" ? 'selected' : ''; ?>" style="background-image:url('<?= $dir['thumbpath'] ?>');" data-title="<?= $dir['name'] ?>"></div>
                        </a>
                    </div>
<?php } ?>
                    <div class="minidirectory" id="currentdirname" data-current="<?= $currentDirName ?>"><?= $currentDirName ?></div>
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

    <div id="footer">
        <?php $gitBranch = getGitBranch(); ?>
        &copy; <?=$HTMLtitle?> <?=date('Y')?> <span class="right">[GIT: <?=$gitBranch['branch']?> - <?=date('jS F Y',$gitBranch['date'])?>]</span>
    </div>


<?php
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $endtime = $mtime[1] + $mtime[0];
    $totaltime = ($endtime - $starttime);
?>
<div id="debug">
    <p>This page was created in <?= $totaltime ?> seconds</p>
    <?php if (DEBUG) {    //Debug stuff
        dump($currentDir, '$currentDir');
        dump($currentDirName, '$currentDirName');
        dump($dirs, '$dirs');
        dump($files, '$files');
    } ?>
</div>


<script src="//code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.js"></script>
<script src="/javascript.js" type="text/javascript"></script>

</body>

</html>
