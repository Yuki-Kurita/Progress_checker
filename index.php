t<?php
session_start();
require_once('./LINEBotTiny.php');
require 'general.php';
require 'replyMessage.php';
// DB設定
$dsn = 'pgsql:host=ec2-23-23-184-76.compute-1.amazonaws.com;port=5432;dbname=d1nbjabuc7kqdn';
$pdo = new PDO($dsn,'tffvdgllqmmidv','88118400b9ed9597af723501e34cb088a37c8031a2fcfcb79a894293fb778694');

$channelAccessToken = 'IuFN7z/T5jyFM8qt9e9O8HbSSrUiyt0lx0dK27HOgh0HSh0ZU2N0iCKwr3dZ6kCpmn3Htzcwvz17O74nR1NwKKI3iHEQrc78Vgp6NChh2FR5dTlMH/FZ512x57+6qujaMljp6WXv3fl0ueXKeUsQJwdB04t89/1O/w1cDnyilFU=';
$channelSecret = 'c4aecec47f9be6f95339cee7918bab2d';
// flag設定
if(!isset($_SESSION['addFlag'])){$_SESSION['addFlag'] = false;}
if(!isset($_SESSION['addSecondFlag'])){$_SESSION['addSecondFlag'] = false;}
if(!isset($_SESSION['deleteFlag'])){$_SESSION['deleteFlag'] = false;}
if(!isset($_SESSION['editFlag'])){$_SESSION['editFlag'] = false;}
if(!isset($_SESSION['editSecondFlag'])){$_SESSION['editSecondFlag'] = false;}
if(!isset($_SESSION['editThirdFlag'])){$_SESSION['editThirdFlag'] = false;}
if(!isset($_SESSION['editNameFlag'])){$_SESSION['editNameFlag'] = false;}
if(!isset($_SESSION['editProgressFlag'])){$_SESSION['editProgressFlag'] = false;}
if(!isset($_SESSION['editDeadlineFlag'])){$_SESSION['editDeadlineFlag'] = false;}
// インスタンス生成
$client = new LINEBotTiny($channelAccessToken, $channelSecret);
$reply = new replyLineMessage();
$postDB = new PostgreSqlDB();

// webhookイベントオブジェクトをパースする
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            $source = $event['source'];
            $user_id = $source['userId'];
            switch ($message['type']) {
                case 'text':
                    // タスク追加時の処理4 入力された目標をDBに追加
                    if($_SESSION['addThirdFlag']){
                      $goal = preg_replace('/[^0-9]/','',$message['text']);
                      $flag = $postDB->addGoalDB($user_id,$goal,$_SESSION['addTaskName'],$pdo);
                      if($flag){
                        $reply->setMessage('タスクの目標を設定したよ！');
                        $reply->replyAuto($client,$event);
                        $_SESSION['addThirdFlag'] = false;
                      }else{
                        $reply->setMessage('目標に数値を含ませて！'."\n".'<例>勉強の場合 : 200ページ'."\n".'運動の場合 : 100km'."\n".'アプリを開発する場合 : 100%');
                        $reply->replyAuto($client,$event);
                      }
                    }
                    // タスク追加時の処理3 入力された期限をDBに追加
                    elseif($_SESSION['addSecondFlag']){
                      $flag = $postDB->addDeadlineDB($user_id,$message['text'],$_SESSION['addTaskName'],$pdo);
                      // timestamp型になっていてDBを更新できれば以下の処理を行う
                      if($flag){
                        $reply->setMessage('タスクの期限を設定したよ！続いて目標を数値で設定してね！'."\n".'<例>勉強の場合 : 200ページ'."\n".'運動の場合 : 100km'."\n".'アプリを開発する : 100%');
                        $reply->replyAuto($client,$event);
                        $_SESSION['addSecondFlag'] = false;
                        $_SESSION['addThirdFlag'] = true;
                      }else{
                        $reply->setMessage('入力の仕方を変えてみて！'."\n".'<例>日だけ指定する場合 : 2019-02-26'."\n".'時間も指定する場合 : 2019-02-26 13:00:00');
                        $reply->replyAuto($client,$event);
                      }
                    }
                    // タスク追加時の処理2 入力されたタスク名をDBに追加
                    elseif($_SESSION['addFlag']){
                      // 追加のキャンセル
                      if(strpos($message['text'],'やめる')!==false){
                        $_SESSION['addFlag'] = false;
                        $reply->setMessage('タスクの追加をやめたよ');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      $flag = $postDB->addTaskDB($message['text'],$user_id, $pdo);
                      // task追加成功時の処理
                      if($flag){
                        $reply->setMessage('「'.$message['text'].'」でタスクを追加したよ！'."\n".'続いてタスクの期限を入力してね！'."\n".'<例>日だけ指定する場合 : 2019-02-26'."\n".'時間も指定する場合 : 2019-02-26 13:00:00');
                        $reply->replyAuto($client,$event);
                        $_SESSION['addTaskName'] = $message['text'];
                        $_SESSION['addSecondFlag'] = true;
                      }
                      else{
                        $reply->setMessage('タスクの追加に失敗したよ');
                        $reply->replyAuto($client,$event);
                      }
                      $_SESSION['addFlag'] = false;
                    }

                    // タスク削除時の処理2
                    elseif($_SESSION['deleteFlag']){
                      // 削除時に確認と入力された場合
                      if(strpos($message['text'],'確認')!==false){
                        $tasks = $postDB->showTaskDB($user_id,$pdo);
                        $reply->setMessage('あなたのタスクは'.$tasks."\n".'だよ。何を削除する？'."\n".'削除をやめる場合は「やめる」と入力してね。');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      // 削除のキャンセル
                      elseif(strpos($message['text'],'やめる')!==false){
                        $_SESSION['deleteFlag'] = false;
                        $reply->setMessage('タスクの削除をやめたよ');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      $count = $postDB->checkTaskDB($user_id,$message['text'],$pdo);
                      // task削除成功時の処理
                      if($count !== 0){
                        // データの削除
                        $flag = $postDB->deleteTaskDB($message['text'],$pdo);
                        $reply->setMessage('「'.$message['text'].'」のタスクを削除したよ！');
                        $reply->replyAuto($client,$event);
                      }
                      else{
                        $reply->setMessage('そのタスクは存在しないよ。');
                        $reply->replyAuto($client,$event);
                      }
                      $_SESSION['deleteFlag'] = false;
                    }

                    // タスク編集時の処理4
                    elseif($_SESSION['editThirdFlag']){
                      // 編集のキャンセル
                      if(strpos($message['text'],'やめる')!==false){
                        $_SESSION['editThirdFlag'] = false;
                        $_SESSION['editNameFlag'] = false;
                        $reply->setMessage('タスクの編集をやめたよ');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      if($_SESSION['editNameFlag']){
                        $flag = $postDB->editTaskDB($message['text'],$_SESSION['beforeTaskName'],$user_id,$pdo);
                        if($flag){
                          $reply->setMessage('タスク名の編集を完了したよ！');
                          $reply->replyAuto($client,$event);
                        }
                        else{
                          $reply->setMessage('タスク名の編集を失敗したよ。');
                          $reply->replyAuto($client,$event);
                        }
                          $_SESSION['editThirdFlag'] = false;
                          $_SESSION['editNameFlag'] = false;
                      }
                      elseif($_SESSION['editDeadlineFlag']){
                        $flag = $postDB->addDeadlineDB($user_id,$message['text'],$_SESSION['beforeTaskName'],$pdo);
                        if($flag){
                          $reply->setMessage('タスクの期限を編集したよ！');
                          $reply->replyAuto($client,$event);
                          $_SESSION['editDeadlineFlag'] = false;
                          $_SESSION['editThirdFlag'] = false;
                        }else{
                          $reply->setMessage('入力の仕方を変えてみて！'."\n".'<例>日だけ指定する場合 : 2019-02-26'."\n".'時間も指定する場合 : 2019-02-26 13:00:00');
                          $reply->replyAuto($client,$event);
                        }
                      }
                      elseif($_SESSION['editGoalFlag']){
                        $goal = preg_replace('/[^0-9]/','',$message['text']);
                        $flag = $postDB->addGoalDB($user_id,$goal,$_SESSION['beforeTaskName'],$pdo);
                        if($flag){
                          $reply->setMessage('タスクの目標を編集したよ！');
                          $reply->replyAuto($client,$event);
                          $_SESSION['editGoalFlag'] = false;
                          $_SESSION['editThirdFlag'] = false;
                        }else{
                          $reply->setMessage('目標に数値を含ませて！'."\n".'<例>勉強の場合 : 200ページ'."\n".'運動の場合 : 100km'."\n".'アプリを開発する : 100%');
                          $reply->replyAuto($client,$event);
                        }
                      }
                    }
                    // タスク編集時の処理3
                    elseif($_SESSION['editSecondFlag']){
                      // 編集のキャンセル
                      if(strpos($message['text'],'やめる')!==false){
                        $_SESSION['editSecondFlag'] = false;
                        $editSecondFlag = false;
                        $reply->setMessage('タスクの編集をやめたよ');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      else if(strpos($message['text'],'名')!==false){
                        $reply->setMessage('新しいタスク名を入力してね！編集をやめたかったら「やめる」と入力してね。');
                        $reply->replyAuto($client,$event);
                        $_SESSION['editThirdFlag'] = true;
                        $_SESSION['editSecondFlag'] = false;
                        $_SESSION['editNameFlag'] = true;
                      }
                      else if(strpos($message['text'],'期限')!==false){
                        $reply->setMessage('新しい期限を入力してね！編集をやめたかったら「やめる」と入力してね。'."\n".'<例>日だけ指定する場合 : 2019-02-26'."\n".'時間も指定する場合 : 2019-02-26 13:00:00');
                        $reply->replyAuto($client,$event);
                        $_SESSION['editThirdFlag'] = true;
                        $_SESSION['editSecondFlag'] = false;
                        $_SESSION['editDeadlineFlag'] = true;
                      }
                      else if(strpos($message['text'],'目標')!==false){
                        $reply->setMessage('新しい目標を入力してね！編集をやめたかったら「やめる」と入力してね。'."\n".'<例>勉強の場合 : 200ページ'."\n".'運動の場合 : 100km'."\n".'アプリを開発する : 100%');
                        $reply->replyAuto($client,$event);
                        $_SESSION['editThirdFlag'] = true;
                        $_SESSION['editSecondFlag'] = false;
                        $_SESSION['editGoalFlag'] = true;
                      }
                      // 該当する文字が打たれなかった場合
                      else{
                        $reply->setMessage('「タスク名」、「タスクの期限」、「目標」のどれかを入力してね！編集をやめたかったら「やめる」と入力してね。');
                        $reply->replyAuto($client,$event);
                      }
                    }
                    // タスク編集時の処理2
                    elseif($_SESSION['editFlag']){
                      // 編集時に確認と入力された場合
                      if(strpos($message['text'],'確認')!==false){
                        $tasks = $postDB->showTaskDB($user_id,$pdo);
                        $reply->setMessage('あなたのタスクは'.$tasks."\n".'だよ。何を編集する？'."\n".'編集をやめる場合は「やめる」と入力してね。');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      // 編集のキャンセル
                      else if(strpos($message['text'],'やめる')!==false){
                        $_SESSION['editFlag'] = false;
                        $reply->setMessage('タスクの編集をやめたよ');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      // データの編集 -> DBに入力されたタスク名があるか確認
                      $data_count=$postDB->checkTaskDB($user_id,$message['text'],$pdo);
                      if($data_count !== 0){
                        $reply->setMessage('タスク名、タスクの期限、目標を編集できるよ。編集したいことを入力してね！'."\n".'例 : タスク名'."\n".'また、編集をやめたかったら「やめる」と入力してね。');
                        $reply->replyAuto($client,$event);
                        $_SESSION['editFlag'] = false;
                        $_SESSION['editSecondFlag'] = true;
                        $_SESSION['beforeTaskName'] = $message['text'];
                    }else{
                      $reply->setMessage('入力してくれたタスク名は存在しないよ。タスクを確認したい場合は「確認する」と入力してね！');
                      $reply->replyAuto($client,$event);
                      }
                    }
                    // 進捗報告の時の処理3
                    elseif($_SESSION['progSecondFlag']){
                      if(strpos($message['text'],'やめる')!==false){
                        $_SESSION['progSecondFlag'] = false;
                        $reply->setMessage('進捗報告をやめたよ');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      $prog = preg_replace('/[^0-9]/','',$message['text']);
                      $sql_list = $postDB -> updatePregDB($user_id,$prog,$_SESSION['beforeTaskName'],$pdo);
                      if($sql_list[0]){
                        if($sql_list[1]>=100){
                          $reply->setMessage('タスクを完了したよ！頑張ったね、お疲れ様！');
                          $reply->replyAuto($client,$event);
                        }else{
                          $reply->setMessage('進捗報告を完了したよ！');
                          $reply->replyAuto($client,$event);
                      }
                        $_SESSION['progSecondFlag'] = false;
                      }else{
                        $reply->setMessage('進捗に数値を含ませて！'."\n".'<例>10ページ');
                        $reply->replyAuto($client,$event);
                      }
                    }

                    // 進捗報告の時の処理2
                    elseif($_SESSION['progFlag']){
                      if(strpos($message['text'],'確認')!==false){
                        $tasks = $postDB->showTaskDB($user_id,$pdo);
                        $reply->setMessage('あなたのタスクは'.$tasks."\n".'だよ。どのタスクの進捗を報告する？'."\n".'報告をやめる場合は「やめる」と入力してね。');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      if(strpos($message['text'],'やめる')!==false){
                        $_SESSION['progFlag'] = false;
                        $reply->setMessage('進捗報告をやめたよ');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      $check = $postDB->checkTaskDB($user_id,$message['text'],$pdo);
                      if($check !== 0){
                        $_SESSION['progSecondFlag'] = true;
                        $_SESSION['progFlag'] = false;
                        $_SESSION['beforeTaskName'] = $message['text'];
                        $reply->setMessage('前回報告してくれた時からの進捗状況を教えてね'."\n".'<例>10ページ'."\n".'進捗報告をやめたい場合 :「やめる」'."\n".'と入力してね！');
                        $reply->replyAuto($client,$event);
                      }
                      else{
                        $reply->setMessage('そのタスクは存在しないよ。'."\n".'タスクを確認したい場合 : 「タスクを確認する」'."\n".'進捗報告をやめたい場合 :「やめる」'."\n".'と入力してね！');
                        $reply->replyAuto($client,$event);
                      }
                    }
                    // タスク確認処理2
                    elseif($_SESSION['showFlag']){
                      if(strpos($message['text'],'確認')!==false){
                        $tasks = $postDB->showTaskDB($user_id,$pdo);
                        $reply->setMessage('あなたのタスクは'.$tasks."\n".'だよ。どのタスクを確認する？'."\n".'確認をやめる場合は「やめる」と入力してね。');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      if(strpos($message['text'],'やめる')!==false){
                        $_SESSION['showFlag'] = false;
                        $reply->setMessage('タスクの確認をやめたよ');
                        $reply->replyAuto($client,$event);
                        break;
                      }
                      $check = $postDB->checkTaskDB($user_id,$message['text'],$pdo);
                      if($check !== 0){
                        $_SESSION['showFlag'] = false;
                        $detail = $postDB->detailShowDB($user_id,$message['text'],$pdo);
                        $reply->setMessage("タスク名 : ".$message['text']."\n"."期限 : ".$detail['deadline']."\n"."進捗率 : ".$detail['prog']."%");
                        $reply->replyAuto($client,$event);
                      }
                      else{
                        $reply->setMessage('そのタスクは存在しないよ。'."\n".'タスクを確認したい場合 : 「タスクを確認する」'."\n".'進捗報告をやめたい場合 :「やめる」'."\n".'と入力してね！');
                        $reply->replyAuto($client,$event);
                      }
                    }
                    // タスク追加の時の処理1
                    elseif(strpos($message['text'],'追加')!==false){
                      $reply->setMessage('タスクを追加するね！タスク名を入力してね！'."\n".'追加をやめたい場合 :「やめる」'."\n".'と入力してね！');
                      $reply->replyAuto($client,$event);
                      $_SESSION['addFlag'] = true;
                    }
                    // タスク確認処理1
                    elseif(strpos($message['text'],'確認')!==false){
                      // DBからuser_idに紐づけてタスクを取ってくる
                      $tasks = $postDB->showTaskDB($user_id,$pdo);
                      $reply->setMessage('あなたのタスクは'.$tasks."\n".'さらに詳細を知りたい場合は、タスク名を入力してね！'."\n".'大丈夫なら、「やめる」と入力してね！');
                      $reply->replyAuto($client,$event);
                      $_SESSION['showFlag'] = true;
                    }
                    // タスク削除の時の処理1
                    elseif(strpos($message['text'],'削除')!==false){
                      $reply->setMessage('タスクを削除するね！削除したいタスク名を入力してね！'."\n".'タスク名を知りたい場合 :「タスクを確認する」'."\n".'削除をやめたい場合 :「やめる」'."\n".'と入力してね！');
                      $reply->replyAuto($client,$event);
                      $_SESSION['deleteFlag'] = true;
                    }
                    // タスク編集の時の処理1
                    elseif(strpos($message['text'],'編集')!==false){
                      $reply->setMessage('タスクを編集するね！編集したいタスク名を入力してね！'."\n".'タスク名を知りたい場合 :「タスクを確認する」'."\n".'編集をやめたい場合 :「やめる」'."\n".'と入力してね！');
                      $reply->replyAuto($client,$event);
                      $_SESSION['editFlag'] = true;
                    }
                    // 進捗報告の時の処理1
                    elseif(strpos($message['text'],'報告')!==false){
                      $reply->setMessage('お疲れ様！進捗報告したいタスク名を教えてね！'."\n".'タスクを確認したい場合 :「タスクを確認する」'."\n".'進捗報告をやめたい場合 :「やめる」'."\n".'と入力してね！');
                      $reply->replyAuto($client,$event);
                      $_SESSION['progFlag'] = true;
                    }
                    // おふざけ
                    elseif(strpos($message['text'],'るんるん')!==false){
                      $reply->setMessage('るんるんるんるんうるせーんだよぉぉぉぉおおおおお！！！！！！！！');
                      $reply->replyAuto($client,$event);
                    }
                    elseif(strpos($message['text'],'きまた')!==false){
                      $reply->replyStamp($client,$event);
                    }

                    // ユーザーに対する返事
                    $reply->setMessage('タスクの追加 : 「タスクを追加する」と入力してね！'."\n".'タスクの削除 : 「タスクを削除する」と入力してね！'."\n".'タスクの編集 : 「タスクを編集する」と入力してね！'."\n".'タスクの確認 : 「タスクを確認する」と入力してね！'."\n".'タスクの進捗報告 : 「報告する」と入力してね！');
                    $reply->replyAuto($client,$event);
                    break;
                default:
                    error_log('Unsupported message type: ' . $message['type']);
                    break;
            }
            // 友達追加された場合 - followイベントの場合
        case 'follow':
            $follow = $event['follow'];
            $reply->setMessage('友達追加ありがとう!'."\n".'このアプリはあなたのタスクの進捗管理をしてくれるよ！早速タスクを追加してみよう！'."\n\n".'タスクの追加 : 「タスクを追加する」と入力してね！'."\n".'タスクの削除 : 「タスクを削除する」と入力してね！'."\n".'タスクの編集 : 「タスクを編集する」と入力してね！'."\n".'タスクの確認 : 「タスクを確認する」と入力してね！'."\n".'タスクの進捗報告 : 「報告する」と入力してね！');
            $reply->relyQuickStart($client,$event);


            break;

        default:
            error_log('Unsupported event type: ' . $event['type']);
            break;
    }
};
