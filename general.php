<?php
class PostgreSqlDB{

  public function addTaskDB($task_name, $user_id, $pdo){
      $sql = 'INSERT INTO task(name,user_id) VALUES(?,?)';
      $stmt = $pdo -> prepare($sql);
      $flag = $stmt -> execute(array($task_name,$user_id));
      return $flag;
  }

  public function deleteTaskDB($task_name, $pdo){
    $sql = 'DELETE FROM task WHERE name = ?';
    $stmt = $pdo -> prepare($sql);
    $flag = $stmt -> execute(array($task_name));
    return $flag;
  }

  public function showTaskDB($user_id,$pdo){
    $sql ='SELECT * FROM task WHERE user_id=?';
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array($user_id));
    $tasks = '';
    foreach($stmt as $task){
      if($task['prog']){
        $percent = ($task['prog']/$task['goal'])*100;
        $tasks.="\n".'・'.$task['name'].' : '.$percent.'%';
      }else{
        $tasks.="\n".'・'.$task['name'].' : 0%';
      }
    }
    return $tasks;
  }

  public function editTaskDB($task_name,$before_task_name,$user_id,$pdo){
    $sql = 'UPDATE task SET name = ? WHERE user_id = ? AND name = ?';
    $stmt = $pdo -> prepare($sql);
    $flag = $stmt -> execute(array($task_name,$user_id,$before_task_name));
    return $flag;
  }

  public function checkTaskDB($user_id,$task_name,$pdo){
    $sql = 'SELECT * FROM task WHERE user_id = ? AND name = ? AND prog <= 100';
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array($user_id,$task_name));
    $all = $stmt->fetchAll();
    return count($all);
  }

  public function detailShowDB($user_id,$task_name,$pdo){
    $sql = 'SELECT * FROM task WHERE user_id = ? AND name = ?';
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array($user_id,$task_name));
    $all = $stmt->fetchAll();
    return $all[0];
  }

  public function addDeadlineDB($user_id,$deadline,$task_name,$pdo){
    $sql = 'UPDATE task SET deadline = ? WHERE user_id = ? AND name = ?';
    $stmt = $pdo -> prepare($sql);
    $flag = $stmt -> execute(array($deadline,$user_id,$task_name));
    return $flag;
  }

  public function addGoalDB($user_id,$goal,$task_name,$pdo){
    $sql = 'UPDATE task SET goal = ? WHERE user_id = ? AND name = ?';
    $stmt = $pdo -> prepare($sql);
    $flag = $stmt -> execute(array($goal,$user_id,$task_name));
    return $flag;
  }

  public function updatePregDB($user_id,$prog,$task_name,$pdo){
    $check_sql = 'SELECT prog FROM task WHERE user_id = ? AND name = ?';
    $st = $pdo->prepare($check_sql);
    $st -> execute(array($user_id,$task_name));
    $result = $st->fetch();
    $prog += $result['prog'];
    $sql = 'UPDATE task SET prog = ? WHERE user_id = ? AND name = ?';
    $stmt = $pdo -> prepare($sql);
    $flag = $stmt -> execute(array($prog,$user_id,$task_name));
    return $flag;
  }
}

 ?>
