<?php
include 'database.php';

$minContentLength = 50;
$maxContentLength = 1000;
$invalidPost = false;

function renderPost($post)
{
?>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
      <p class="card-text"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
      <p class="card-text"><small class="text-muted">Posted on <?= htmlspecialchars($post['created_at']) ?></small></p>
    </div>
  </div>
<?php }

function postValid($post)
{
  global $minContentLength;
  global $maxContentLength;
  $post['title'] = trim($post['title'] ?? '');
  $post['content'] = trim($post['content'] ?? '');
  return $post['title'] !== '' && $post['content'] !== ''
    && strlen($post['title']) <= 255 && strlen($post['content']) >= $minContentLength && strlen($post['content']) <= $maxContentLength;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (postValid($_POST)) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $newPost = [
      'title' => $title,
      'content' => $content,
    ];
    if (create('post', $newPost) !== false) {
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
    } else {
      $invalidPost = true;
      echo "Error: Could not save post.";
    }
  } else {
    $invalidPost = true;
  }
}

$posts = readAll('post');

session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Public Square</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
  <div class="container d-flex mt-5">
    <h1>Public Square</h1>
    <a class="btn btn-outline-primary ms-auto mb-auto" href="<?php echo isset($_SESSION['user_id']) ? 'logout.php">Logout' : 'login.php">Login'; ?>
    <?php if (false) { ?>">
    <?php } // weird stuff to make the formatter happy
    ?>
    </a>
  </div>
  <div class="container mt-5">
    <?php if (isset($_SESSION['user_id'])) { ?>
      <div class="card mb-3">
        <div class="card-body">
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
              <label for="title" class="form-label">Title</label>
              <input type="text" class="form-control" id="title" name="title" value="<?php if ($invalidPost) echo trim($_POST["title"]); ?>" required>
            </div>
            <div class="mb-3">
              <label for="content" class="form-label">Content</label>
              <textarea class="form-control" id="content" name="content" rows="3" minlength="<?= $minContentLength ?>" maxlength="<?= $maxContentLength ?>" required><?php if ($invalidPost) echo trim($_POST["content"]); ?></textarea>
              <?php if ($invalidPost) { ?>
                <div class="form-text text-danger">Content was under <?= $minContentLength ?> characters after trimming.</div>
              <?php } ?>
            </div>
            <button type="submit" class="btn btn-primary">Post</button>
        </div>
      </div>
    <?php }
    foreach (array_reverse($posts) as $post) {
      renderPost($post);
    } ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
