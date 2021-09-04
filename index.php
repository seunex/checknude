<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$url = "http://localhost:8080/sync"; //Nudenet API URL ()
$image1 = base64_encode(file_get_contents('./storage/sample2.jpeg')); //sample.jpeg is an uncensored image we are passing
$images_to_check = array(
    '1.jpeg' => $image1
);
$data = array('data' => $images_to_check);
$payload = json_encode($data);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result1 = curl_exec($ch);
//print_r($result1);


$options = array(
    'http' => array(
        'method' => 'POST',
        'content' => $payload,
        'header' => "Content-Type: application/json\r\n" .
            "Accept: application/json\r\n"
    )
);
$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$result2 = json_decode($result, true);

if (is_array($result2)) {
    //$unwanted = array('EXPOSED_BREAST_F', 'EXPOSED_GENITALIA_M', 'EXPOSED_GENITALIA_F');
    $unwanted = array('EXPOSED_GENITALIA_M', 'EXPOSED_GENITALIA_F');
    foreach ($result2 as $prediction) {
        if (!is_array($prediction)) continue;
        foreach ($prediction as $img_key => $suggestions) {

            foreach ($suggestions as $k => $suggestion) {
                //print_r($suggestion);
                $label = $suggestion['label'];
                $score = $suggestion['score'];
                if ($score > 0.6 && in_array($label, $unwanted)) {
                    //$im = $images_to_check[$img_key];
                    if ($k == 0) {
                        $im = './storage/sample2.jpeg';
                    } else {
                        $im = "./storage/image_path_5.jpg";
                        if(!file_exists($im)) $im = './storage/sample2.jpeg';
                    }
                    $img = imagecreatefromjpeg($im); // or imagecreatefromjpeg(), etc.

                    $image = imagecreatetruecolor(100, 100);

                    // set background to white
                    //$white = imagecolorallocate($image, 255, 255, 255);
                    //imagefill($image, 0, 0, $white);
                    // Set a colour for the sides of the rectangle
                    $color = imagecolorallocate($img, 0, 0, 0); // for a white rectangle
                    $x = $suggestion['box'][0];
                    $y = $suggestion['box'][1];
                    $x2 = $suggestion['box'][2];
                    $y2 = $suggestion['box'][3];
                    // Draw an 8x8 recangle at coordinates ($x, $y) from top left
                    //imagerectangle($img, $x,$y,$x2,$y2,$color);
                    imagefilledrectangle($img, $x,$y,$x2,$y2,$color);
                    //imagefill($img, 0, 0, $color);
                    // Save the image
                    $new_image = "./storage/image_path_5.jpg";
                    imagejpeg($img, $new_image); // or imagejpeg(), etc.
                }
            }
        }
    }
}
echo '<pre>',print_r($result2),'</pre>';