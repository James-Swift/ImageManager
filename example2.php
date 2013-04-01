<?php
/*
 * Copyright 2013 James Swift (Creative Commons: Attribution - Share Alike - 3.0)
 * https://github.com/James-Swift/SWDF_image_resizer
 * 
 * This file creates a way to resize images by passing settings in a URL. 
 * Examples:
 * 
 * "example.php?size=thumbnail&img=images/product.jpg"
 * "example.php?size=watermarked_big3&img=images/photos/spain.png"
 * 
 * There is some brief explaination of what is going on, but for a proper
 * dicussion, see the documentation.
 * 
 */

//Load dependencies
require("SWDF_image_resizer.php");

//Register GET variables
$size = @$_GET['size'];	//Requested output size
$img  = @$_GET['img'];	//Path (relative to "base" defined below) to image to be resized

/**
 * First, Load a new instance of the resizer.
 * 
 * You can pass configuration settings to the resizer in one of three ways:
 * 
 * 1. Pass them in an array or file to the constructor:		$resizer = new secureImageResizer(array | PATH_TO_JSON_FILE )
 * 2. Load them after initilizing with:				$resizer->loadConfig( array | PATH_TO_JSON_FILE )
 * 3. Set them one by one after initilizing:			$resizer->set( NAME, VALUE )
 * 
 * Or a combination of the three. If you use $resizer->loadConfig(), it will 
 * clear all previous settings (includeing paths and sizes) and re-initialize
 * with the new settings. Using $resizer->set() overwrites only the value it
 * replaces.
 */
$resizer=new \SWDF\secureImageResizer();

/**
 * Second, define the base (and other settings.)
 * 
 * The "base" setting must be set before doing anything else (otherwise you'll
 * throw an exception). It is the absolute filesytem path to the logical root
 * "base" of the folders where you keep your images. Usually, this is your
 * web-root (i.e. htdocs or public_html) as all images are usually stored
 * somewhere in there. However, it doesn't have to be in a publicly-accessible
 * directory. It could be for example a home directory. As long as php has 
 * write access to it, and that's where you store your images, it doesn't 
 * matter.
 * 
 * There are other optional settings you can define here as well. Examples are
 * included. See the documentation for full specification.
 */
$resizer->set("base", dirname(__FILE__) );

/** Inteligently cache resized images. This speeds up subsequent requests. If the 
 * source file is modified, the cached file is automatically refreshed. */
$resizer->set("enableCaching", true );

/** How long to keep a cached file before deleting. */
$resizer->set("cacheTime", 60*60*24 );

/** Default jpeg quality. Can be overwriteen on a size-by-size basis. */
$resizer->set("defaultJpegQuality", 90 );

/** Default jpeg quality. Can be overwriteen on a size-by-size basis. */
$resizer->set("defaultWatermarkOpacity", 10 );

/**
 * Third. Add some sizes.
 * 
 * Consult the documentation for details of avilable settings, but for now,
 * suffice it to say that you must explicitly define all sizes your website
 * can produce here, you limit when they can be used later. Give them an ID
 * to easily reuse them later. A few examples are included:
 */
$resizer->addSize(array(
	"id"=>"original",
	"method"=>"original"
));
$resizer->addSize(array(
	"id"=>"product_image",
	"method"=>"fit",
	"width"=>400,
	"height"=>800,
	"watermark"=>array(
		"img"=>"images/watermark.png",
		"scale"=>1.5,
		"opacity"=>40,
		"repeat"=>true
	)
));
$resizer->addSize(array(
	"id"=>"2x",
	"method"=>"scale",
	"scale"=>2
));
$resizer->addSize(array(
	"id"=>"200x300",
	"method"=>"fill",
	"width"=>200,
	"height"=>300,
	"quality"=>90
));


/**
 * Foruth. Define some paths.
 * 
 * The image resizer by default doesn't allow you to resize any images. You 
 * must tell it what folders it is allowed to operate in, and (optionally)
 * what sizes can be used in which folders (all sizes are allowed by default).
 * Some examples:
 */
$resizer->addPath(array(
	"path"=>"images/"
));
$resizer->addPath(array(
	"path"=>"images/products/",
	"allowSizes"=>array("product_image", "200x300")
));
$resizer->addPath(array(
	"path"=>"images/original_scans/",
	"denySizes"=>array("original","2x")
));


/**
 * Fith. Process the user's request.
 * 
 * This checks that the requested file and size exists, that the file's
 * location allows it to be resized to the requested size etc., then does
 * the actual resizing.
 */

$new_image = $resizer->resize($img, $size);

/**
 * Lastly, check it went ok, then output it.
 * 
 * There are other things you can do with the resized file. You don't have
 * to display it now. You can save it to a new permenant location for example.
 * Check the documentation for more ideas.
 */

if ($new_image!==false){
	$new_image->outputHttp();
} else {
	print "Sorry. Your request could not be processed.";
}
?>