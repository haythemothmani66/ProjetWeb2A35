<?php
$key = 'AIzaSyC6z6nBYSMeTZe3zdrfYOdq6QFYssXRkFc';
$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models?key=' . $key);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$data = json_decode($response, true);
if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        if (isset($model['supportedGenerationMethods']) && in_array('generateContent', $model['supportedGenerationMethods'])) {
            echo $model['name'] . "\n";
        }
    }
} else {
    echo "Error or no models found\n";
    echo $response;
}
?>
