<?php
  // composerでinstallしたライブラリを一括で読み込む
  require_once __DIR__ . '/vendor/autoload.php';

    // アクセストークンを使いCurlHTTPClientをインスタンス化
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('SpxbSN0cnRLPkmTFt7eUYv5cUlcHA41QDdJsqfLjvoqNESUUUfZn7WrjN05tqdCWmn3Htzcwvz17O74nR1NwKKI3iHEQrc78Vgp6NChh2FRR+zkFE5W0hWMetdpaZO+JVjwuto0kYFzXVF2Bftm/QQdB04t89/1O/w1cDnyilFU=');

    //CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => 'c4aecec47f9be6f95339cee7918bab2d']);

    // LINE Messaging APIがリクエストに付与した署名を取得
    $signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

    //署名をチェックし、正当であればリクエストをパースし配列へ、不正であれば例外処理
    $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);

    foreach ($events as $event) {
        // メッセージを返信
        $response = $bot->replyMessage(
            $event->getReplyToken(), new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($event->getText())  
        );
    }
