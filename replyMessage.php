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
                'text' => $this->message,
                'quickReply': {
        'items': [
          {
            'type': 'action',
            'action': {
              'type': 'message',
              'label': 'はい',
              'text': 'はい'
            }
          },
          {
            'type': 'action',
            'action': {
              'type': 'message',
              'label': 'ワン',
              'text': 'ワン'
            }
          }
        ]
      }
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
}

 ?>
