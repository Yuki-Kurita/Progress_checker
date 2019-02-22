<?php
// HTTPヘッダを設定
$channelToken = 'IuFN7z/T5jyFM8qt9e9O8HbSSrUiyt0lx0dK27HOgh0HSh0ZU2N0iCKwr3dZ6kCpmn3Htzcwvz17O74nR1NwKKI3iHEQrc78Vgp6NChh2FR5dTlMH/FZ512x57+6qujaMljp6WXv3fl0ueXKeUsQJwdB04t89/1O/w1cDnyilFU=';
$headers = [
	'Authorization: Bearer ' . $channelToken,
	'Content-Type: application/json; charset=utf-8',
];

// POSTデータを設定してJSONにエンコード
$post = [
	'to' => 'U32f0bcd0627e9db8576fa0816d58db10',
	'messages' => [
		[
			'type' => 'text',
			'text' => 'hello world',
		],
	],
];
$post = json_encode($post);

// HTTPリクエストを設定
$ch = curl_init('https://api.line.me/v2/bot/message/push');
$options = [
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_HTTPHEADER => $headers,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_BINARYTRANSFER => true,
	CURLOPT_HEADER => true,
	CURLOPT_POSTFIELDS => $post,
];
curl_setopt_array($ch, $options);

// 実行
$result = curl_exec($ch);

// エラーチェック
$errno = curl_errno($ch);
if ($errno) {
	return;
}

// HTTPステータスを取得
$info = curl_getinfo($ch);
$httpStatus = $info['http_code'];

$responseHeaderSize = $info['header_size'];
$body = substr($result, $responseHeaderSize);

// 200 だったら OK
echo $httpStatus . ' ' . $body;
?>
