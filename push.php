<?php

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('IuFN7z/T5jyFM8qt9e9O8HbSSrUiyt0lx0dK27HOgh0HSh0ZU2N0iCKwr3dZ6kCpmn3Htzcwvz17O74nR1NwKKI3iHEQrc78Vgp6NChh2FR5dTlMH/FZ512x57+6qujaMljp6WXv3fl0ueXKeUsQJwdB04t89/1O/w1cDnyilFU=');
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => 'c4aecec47f9be6f95339cee7918bab2d']);

$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
$response = $bot->pushMessage('U32f0bcd0627e9db8576fa0816d58db10', $textMessageBuilder);

echo $response->getHTTPStatus() . ' ' . $response->getRawBody();

?>
