<?php
// vim: set et sw=4 ts=4 sts=4 fdm=marker ff=unix fenc=utf8 nobomb:
/**
 * Resource Compiler
 *
 * @author mingcheng<i.feelinglucky#gmail.com>
 * @date   2010-12-27
 * @link   http://www.gracecode.com/
 */

require_once "../config.inc.php";
require_once DIR_LIBRARY."/uploader.php";

/**
 * Die with JSON!!
 */
function die_with_message($message, $success) {
    header("Content-type: text/javascript");
    @unlink(FLAG_COMPILING_FILE);
    die(json_encode(array("message" => $message, "success" => !!$success)));
}

/**
 * Upload file from client.
 */
try {
    $uploader = new qqFileUploader(array("js", "css"), MAX_UPLOAD_FILE_SIZE);
    $uploaded_file = $uploader->handleUpload(DIR_TMP);
} catch (Exception $e) {
    die_with_message($e->getMessage(), false);
}

if (!$uploaded_file) {
    die_with_message($uploader->getError(), false);
}

/**
 * Get path info
 */
$uploaded_file_pathinfo = pathinfo($uploaded_file);
$original_file_pathinfo = pathinfo($uploader->getName());

$minized_file_name = $original_file_pathinfo['filename'] . MINIZED_FILE_SUFFIX . 
                                                    '.' . $original_file_pathinfo['extension'];
$minized_file = DIR_TMP.'/'.$minized_file_name;

/**
 * Get compress command
 */
switch (strtolower($original_file_pathinfo['extension'])) {
    case 'js':
        //$cmd = sprintf(CLOSURE_COMPILER_CMD, escapeshellarg($uploaded_file), escapeshellarg($minized_file));
        $cmd = sprintf(YUI_COMPRESSOR_CMD, escapeshellarg($uploaded_file), escapeshellarg($minized_file));
        break;

    case 'css':
        $cmd = sprintf(YUI_COMPRESSOR_CMD, escapeshellarg($uploaded_file), escapeshellarg($minized_file));
        break;
}

if (!isset($cmd) || !$cmd) {
    die_with_message('Can NOT found which compile cmd fit for your file-type.', false);
}


/**
 * Run command
 */
try {
    if (!function_exists("exec")) {
        throw new Exception("OOPs! Server does NOT support run scripts");
    }

    if (file_exists(FLAG_COMPILING_FILE)) {
        throw new Exception("Server is busy, pls try again later.");
    }
    // Add compiling flag for do the command once a time.
    @file_put_contents(FLAG_COMPILING_FILE, time());
    $result = @exec($cmd, $output, $return_var);
    @unlink(FLAG_COMPILING_FILE);
} catch (Exception $e) {
    die_with_message($uploader->getError(), false);
    exit;
}

if (!is_readable($minized_file) || !filesize($minized_file)) {
    //die_with_message($cmd, false);
    die_with_message("Shit, Server is out of memory.", false);
}


/**
 * Echo results
 */
echo json_encode(array (
             "file" => $uploader->getName(),
          "success" => true,
    "original_size" => filesize($uploaded_file),
     "minized_size" => filesize($minized_file),
              "url" => sprintf(URL_DOWNLOAD, urlencode($minized_file_name))
));
@unlink($uploaded_file); // Clean files
