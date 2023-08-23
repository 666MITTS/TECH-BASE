<!DOCTYPE html>
<html>
<head>
    <title>Simple Bulletin Board</title>
</head>
<body>
    <h1>掲示板</h1>
    
    <!-- 新規投稿フォーム -->
    <form method="post">
        <h2>新規投稿</h2>
        名前：<input type="text" name="name"><br>
        コメント：<textarea name="comment"></textarea><br>
        パスワード：<input type="password" name="password"><br>
        <input type="submit" value="投稿">
    </form>
    
    <!-- 削除フォーム -->
    <form method="post">
        <h2>削除</h2>
        削除対象番号：<input type="number" name="delete_number"><br>
        パスワード：<input type="password" name="password"><br>
        <input type="submit" value="削除">
    </form>
    
    <!-- 編集フォーム -->
    <form method="post">
         <h2>編集</h2>
        編集対象番号：<input type="number" name="edit_number"><br>
        パスワード：<input type="password" name="password"><br>
        <input type="submit" name="edit_submit" value="編集">
    </form>


    <?php
    // MySQLへの接続
    try {
        $dsn = 'mysql:dbname=tb250190db;host=localhost';
        $user = 'tb-250190';
        $password = 'KNaGE7P6bu';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    } catch (PDOException $e) {
        exit('データベース接続失敗。' . $e->getMessage());
    }
    
$sql = "CREATE TABLE IF NOT EXISTS bulletin_board"
        . " ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name VARCHAR(32),"
        . "comment TEXT,"
        . "password VARCHAR(255),"
        . "post_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        . ")";
    $stmt = $pdo->query($sql);


    // 投稿の追加
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["name"]) && isset($_POST["comment"])) {
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $password = $_POST["password"];
        
        $sql = "INSERT INTO bulletin_board (name, comment, password) VALUES (:name, :comment, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    // 投稿の削除
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_number"])) {
        $delete_number = $_POST["delete_number"];
        $password = $_POST["password"];
        
        $sql = "DELETE FROM bulletin_board WHERE id = :id AND password = :password";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $delete_number, PDO::PARAM_INT);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    // 投稿の編集
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_number"]) && isset($_POST["edit_submit"])) {
    $edit_number = $_POST["edit_number"];
    $password = $_POST["password"];
    
    $sql = "SELECT * FROM bulletin_board WHERE id = :id AND password = :password";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $edit_number, PDO::PARAM_INT);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $edit_id = $row['id'];
        $edit_name = $row['name'];
        $edit_comment = $row['comment'];
    }
}
    
    // 投稿の更新
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_submit_final"])) {
    if (
        isset($_POST["edit_id"], $_POST["edit_name"], $_POST["edit_comment"])
        && !empty($_POST["edit_id"])
        && !empty($_POST["edit_name"])
        && !empty($_POST["edit_comment"])
    ) {
        $edit_id = $_POST["edit_id"];
        $edited_name = $_POST["edit_name"];
        $edited_comment = $_POST["edit_comment"];
        
        $sql = "UPDATE bulletin_board SET name = :name, comment = :comment WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $edited_name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $edited_comment, PDO::PARAM_STR);
        $stmt->bindParam(':id', $edit_id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        echo "必要なフィールドが提供されていません。";
    }
}
    
    // 投稿の表示
    $sql = "SELECT * FROM bulletin_board";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($posts as $post) {
        echo "投稿番号：" . $post['id'] . "<br>";
        echo "投稿日時：" . $post['post_date'] . "<br>";
        echo "名前：" . $post['name'] . "<br>";
        echo "コメント：" . $post['comment'] . "<br>";
        echo "<hr>";
    }
    
    
//編集データの表示 
if (isset($edit_id) && isset($edit_name) && isset($edit_comment)) {
    echo "<h2>編集中</h2>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='edit_id' value='$edit_id'>";  // 編集対象のID
    echo "名前：<input type='text' name='edit_name' value='$edit_name'><br>";
    echo "コメント：<textarea name='edit_comment'>$edit_comment</textarea><br>";
    echo "<input type='submit' name='edit_submit_final' value='更新'>";
    echo "</form>";
}
    ?>
</body>
</html>