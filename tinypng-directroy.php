<?php

/*
 *  TinyPNG images in a directory v1
 *
 *  Kamal Albatarni - http://kamal-albatarni.info/
 */

// set your api information here.
define( KEY ,"enter your key" );
define( URL , "https://api.tinypng.com/shrink" );

function tiny_directory($path,$output)
{
    // if the output directory not exist, let's create one !
    if(!file_exists($output))
    {
        mkdir($output);
    }
    if ($handle = opendir($path)) {
        while (false !== ($fileName = readdir($handle))) {
            $extension = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
            // check if the file already compressed
            // we don't need to process it again!
            if($extension == 'png' && !file_exists($output. '/'. $fileName))
            {
                tiny_this_file($path . '/' . $fileName, $output);
            }

        }
        closedir($handle);
    }

}


function tiny_this_file($input,$output)
{

    // new file name is the same of the old file name.
    $new_file_name = basename($input);
    $output .= '/'.$new_file_name;
    $options = array(
        "http" => array(
            "method" => "POST",
            "header" => array(
                "Content-type: image/png",
                "Authorization: Basic " . base64_encode("api:".KEY)
            ),
            "content" => file_get_contents($input)
        ),
        "ssl" => array(
            /* Uncomment below if you have trouble validating our SSL certificate.
               Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
             "cafile" => __DIR__ . "/cacert.pem",
            "verify_peer" => true
        )
    );

    $result = fopen(URL, "r", false, stream_context_create($options));
    if ($result) {
        /* Compression was successful, retrieve output from Location header. */
        foreach ($http_response_header as $header) {
            if (substr($header, 0, 10) === "Location: ") {
                file_put_contents($output, fopen(substr($header, 10), "rb", false));
                echo basename($input) . " was successfully compressed ! \n";
            }
        }
    } else {
        /* Something went wrong! */
        print("we could not compress ". $input.  "\n");
    }
}


// setting default values...
// if the input path is not passed from shell script will be the current directory
$path = (isset($argv[1]))? $argv[1] : getcwd();
$path= 'images';
// if the output path is not passed, we will create a directory named converted
// and put the files there.
$output = (isset($argv[2])) ? $argv[2] : 'converted';
tiny_directory($path,$output);


?>
