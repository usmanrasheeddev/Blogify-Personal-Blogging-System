<?php
include 'functions.php';

if(isset($_POST['add_post'])){
    $user = $_POST['user'];
    $category = $_POST['category'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    mysqli_query($conn,"INSERT INTO posts(user_id,category_id,title,content)
        VALUES('$user','$category','$title','$content')");
    header("Location: index.php");
}

$users = getUsers();
$categories = getCategories();
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Post</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Add New Post</h2>
<form method="post">
<select name="user">
<?php while($u=mysqli_fetch_assoc($users)){ ?>
<option value="<?php echo $u['id']; ?>"><?php echo $u['name']; ?></option>
<?php } ?>
</select>
<select name="category">
<?php mysqli_data_seek($categories,0); while($c=mysqli_fetch_assoc($categories)){ ?>
<option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
<?php } ?>
</select><br>
<input type="text" name="title" placeholder="Title"><br>
<textarea name="content" placeholder="Content"></textarea><br>
<button name="add_post">Add Post</button>
</form>
<a href="index.php">Back to Home</a>
</body>
</html>
