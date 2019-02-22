aa<?php
class replyLineMessage{
  public $message;

  public function setMessage($mes){
    $this->message = $mes;
  }

  public function replyAuto($client,$event){
    $client->replyMessage([
        'replyToken' => $event['replyToken'],
        'messages' => [
            [
                'type' => 'text',
                // ここで返事する内容を決める
                'text' => $this->message
            ]
        ]
    ]);
  }
  public function replyStamp($client,$event){
    $client->replyMessage([
        'replyToken' => $event['replyToken'],
        'messages' => [
            [
                'type' => 'sticker',
                // ここで返事する内容を決める
                'packageId' =>1,
                'stickerId' =>1
            ]
        ]
    ]);
  }
  public function replyQuickStart($client,$event){
    $client->replyMessage([
    'replyToken'=> $event['replyToken'],
    'messages'=> [
      [
  # ここがメインのメッセージ情報
        'type'=> 'text',
        'text'=> $this->message,
  # クイックリプライボタンを表示させる情報（この例では2つのボタンを表示）を付加して一緒にsendする
        'quickReply'=> [
          'items'=> [
            [
              'type'=> 'action',
              'action'=> [
                'type'=> 'message',
                'label'=> '追加',
                'text'=> '追加'
              ]
            ],
            [
              'type'=> 'action',
              'action'=> [
                'type'=> 'message',
                'label'=> '削除',
                'text'=> '削除'
              ]
            ]
          ]
        ]
      ]
    ]]);
  }
}


 ?>
