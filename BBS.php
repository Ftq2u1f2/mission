<!DOCTYPE html>
<htmi lang='ja'>

<head>
  <meta http-equiv='content-type' charset='UTF-8'>
  <title>BBS.html</title>
</head>

<body>
<?php
  /* 入力内容受取 */
  $name = $_POST['name'];  // 名前
  $comment = $_POST['comment'];  // コメント
  $pass = $_POST['pass'];  // パスワード
  $select = $_POST['select'];  // 選択した編集投稿番号

  $edit = $_POST['edit'];  // 編集対象番号
  $pass_edit = $_POST['pass_edit'];  // 編集用パスワード

  $delete = $_POST['delete'];  // 削除対象番号
  $pass_delete = $_POST['pass_delete'];  // 削除用パスワード

  /* パスワード確認用 */
  $flag_edit = 0;
  $flag_delete = 0;

  /* MySQL接続情報 */
  $dsn = 'mysql:dbname=データベース名;host=サーバー名;charset=utf8';
  $user = 'ユーザー名';
  $password = 'パスワード';

  try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* テーブル作成 */
    $sql = "CREATE TABLE IF NOT EXISTS table_name ("
      ."id INT(10) AUTO_INCREMENT PRIMARY KEY,"
      ."name VARCHAR(32),"
      ."comment TEXT,"
      ."date DATETIME,"
      ."pass TEXT);";
    $pdo->query($sql);

    /* 編集投稿選択 */
    if(@$_POST['edit'] && @$_POST['pass_edit']) {
      if(empty($edit) == FALSE) {

        /* パスワード確認 */
        $sql = "SELECT * FROM table_name WHERE id=$edit AND pass='".$pass_edit."'";
        $result = $pdo->query($sql);
        foreach($result as $row) {
          $temp_name = $row['name'];
          $temp_comment = $row['comment'];
          $temp_pass = $row['pass'];
        }
        $sql = $pdo->prepare("SELECT * FROM table_name WHERE id=$edit AND pass='".$pass_edit."'");
        $result = $sql->execute();
        $count = $sql->fetchColumn();
        if($count != 0) {
          print "投稿内容を編集して送信してください。\n";
          print "<hr>";
          $flag_edit = 1;
        }

        if($flag_edit == 0) {
          print "パスワードが違います。\n";
          print "<hr>";
        }

      } else {
        header("Location: ./BBS.php");
        exit;
      }
    }

    /* 投稿送信 */
    if(@$_POST['name'] && @$_POST['comment'] && @$_POST['pass']) {

      /* 編集 */
      if(@$_POST['select']) {
        if((empty($name) && empty($comment) && empty($pass) && empty($select)) == FALSE) {
          $sql = $pdo->prepare("UPDATE table_name SET name=:name, comment=:comment, date=cast(now() as datetime), pass=:pass WHERE id=$select");
          $sql->bindParam(':name', $name, PDO::PARAM_STR);
          $sql->bindParam(':comment', $comment, PDO::PARAM_STR);
          $sql->bindParam(':pass', $pass, PDO::PARAM_STR);
          $result = $sql->execute();
          if($result) {
            print "投稿内容を編集しました。\n";
            print "<hr>";
          }

        } else {
          header("Location: ./BBS.php");
          exit;
        }

      /* 新規 */
      } else {
        if((empty($name) && empty($comment) && empty($pass)) == FALSE) {
          $sql = $pdo->prepare("INSERT INTO table_name (name, comment, date, pass) VALUES (:name, :comment, cast(now() as datetime), :pass)");
          $sql->bindParam(':name', $name, PDO::PARAM_STR);
          $sql->bindParam(':comment', $comment, PDO::PARAM_STR);
          $sql->bindParam(':pass', $pass, PDO::PARAM_STR);
          $result = $sql->execute();
          if($result) {
            print "投稿が完了しました。\n";
            print "<hr>";
          }

        } else {
          header("Location: ./BBS.php");
          exit;
        }
      }
    }

    /* 投稿削除 */
    if(@$_POST['delete'] && @$_POST['pass_delete']) {
      if((empty($delete)) == FALSE) {

        /* パスワード確認 */
        $sql = $pdo->prepare("DELETE FROM table_name WHERE id=$delete AND pass='".$pass_delete."'");
        $sql->execute();
        $count = $sql->rowCount();
        if($count != 0) {
          print "投稿内容を削除しました。\n";
          print "<hr>";
          $flag_delete = 1;
        }

        if($flag_delete == 0) {
          print "パスワードが違います。\n";
          print "<hr>";
        }

      } else {
        header("Location: ./BBS.php");
        exit;
      }
    }

  /* エラー時例外処理 */
  } catch(PDOException $e) {
    print $e->getMessage()." - ".$e->getLine().PHP_EOL;
  }
?>

<p>名前、コメント、パスワードを入力して送信してください。</p>
<form action ="BBS.php" method ="post">
  <!-- 投稿送信フォーム -->
  <input type ='text' name ='name' placeholder ="名前" value ="<?php echo $temp_name; ?>"><br>
  <input type ='text' name ='comment' placeholder ="コメント" value ="<?php echo $temp_comment; ?>"><br>
  <input type ='text' name ='pass' placeholder ="パスワード" value ="<?php echo $temp_pass; ?>">
  <input type ='hidden' name ='select' value ="<?php echo $edit; ?>">
  <input type ='submit' value ="送信"><br><br>

  <!-- 編集投稿選択フォーム -->
  <input type ='text' name ='edit' placeholder ="編集対象番号"><br>
  <input type ='text' name ='pass_edit' placeholder ="パスワード">
  <input type ='submit' value ="編集"><br><br>

  <!-- 投稿削除フォーム -->
  <input type ='text' name ='delete' placeholder ="削除対象番号"><br>
  <input type ='text' name ='pass_delete' placeholder ="パスワード">
  <input type ='submit' value ="削除"><br><br>
</form>

<?php
  try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* 投稿一覧表示 */
    $sql = "SELECT * FROM table_name ORDER BY id ASC";
    $lines = $pdo->query($sql);
    if(!$lines) {
      echo $pdo->error;
      exit();
    }
    print "<hr>";
    foreach($lines as $row) {
      echo $row['id'].", ";
      echo $row['name'].", ";
      echo $row['comment'].", ";
      echo $row['date']."<br>";
    }

  /* エラー時例外処理 */
  } catch(PDOException $e) {
    print $e->getMessage()." - ".$e->getLine().PHP_EOL;
  }
?>
</body>
</html>
