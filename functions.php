<?php

function getSiteFromUrl($url) {
    foreach (getSites() as $site) {
        if (strpos($url, $site) !== false) {
            return $site;
        }
    }
    return false;
}

function mb_ucfirst($string, $encoding = 'UTF-8') {
    $strlen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strlen - 1, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
}

function getCoordinates($address) {
    $address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern
    $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";
    $response = file_get_contents($url);
    $json = json_decode($response, TRUE); //generate array object from the response from the web
    return ($json['results'][0]['geometry']['location']['lat'] . "," . $json['results'][0]['geometry']['location']['lng']);
}

/**
 * @param $imagickOptions ImagickOptions data to process the image
 */
function getNewImage($imagickOptions) {
    $overlayImage = $imagickOptions->src;
    $originalImage = $imagickOptions->dest;

    // Create image instances
    $dest = imagecreatefromjpeg($originalImage);
    $src = imagecreatefromjpeg($overlayImage);

    // Copy
    //    imagecopy($dest, $src, 10, 410, 0, 0, 100, 27);
    imagecopy($dest, $src,$imagickOptions->dst_x,$imagickOptions->dst_y);

    // save new image and destroy resource
    imagejpeg($dest, $originalImage);
    imagedestroy($dest);
    imagedestroy($src);
}

function getImagePath($filename, $local = true) {
    return $local
            ? getcwd() . "/images/$filename"
            : $_SERVER['HTTP_REFERER'] . "/images/$filename";
}

// save image from url and get link to saved image on server
function saveImage($link, $name) {
    $input = $link;
    $output = __DIR__ . "/images/$name";
    file_put_contents($output, file_get_contents($input));

    return filesize($output);
}


function dd($data, $isDie = false) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    if ($isDie) die;
}