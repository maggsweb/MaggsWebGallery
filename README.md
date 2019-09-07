[![StyleCI](https://github.styleci.io/repos/169919219/shield?branch=master)](https://github.styleci.io/repos/169919219)

# Maggsweb Gallery
Simple, automated, photo gallery

<hr>

Either leave the default configuration:

    $TITLE = 'Gallery';         // Browser title
    $DEBUG = false;             // Debug mode On/Off
    $GALLERY_ROOT = 'images';   // Root level directory that store images eg: 'photos'
    $THUMBS_ROOT = 'thumbs';    // Root level directory '_thumbs'
    $RESIZE_IMAGES = true;      // Resize gallery images (requires MAX_IMAGE_SIZE)
    $MAX_IMAGE_SIZE = 2000;     // Max width (px) to resize gallery images

or create an environment specific configuration using by creating a `config.php` file in the root.


### $TITLE

**$TITLE** is used for the Header and Browser title and in the footer

### $DEBUG

**$DEBUG** turns page debugginh mode On/Off

### $GALLERY_ROOT

**$GALLERY_ROOT** is the default root directory to store directories of images.

The directory name will be used for the Album title and URL link.

### $THUMBS_ROOT

**$THUMBS_ROOT** is used to store the automatically created thumbnails.

This directory structure is generated and populated automatically.
It will mimic $GALLERY_ROOT and can be deleted at any time.

### $RESIZE_IMAGES

**$RESIZE_IMAGES** is a flag that resizes (and overwrites) images inside $GALLERY_ROOT.
This settign must be used in conjunction with $MAX_IMAGE_SIZE.

### $MAX_IMAGE_SIZE

**$MAX_IMAGE_SIZE** is the maximum width, in pixels, that images will be resized to if $RESIZE_IMAGES is set to true.

 

