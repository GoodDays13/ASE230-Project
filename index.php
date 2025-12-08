<?php
require_once 'authentication.php';
require_once 'database.php';

$minContentLength = 50;
$maxContentLength = 1000;
$invalidPost = false;

function renderPost($post)
{
  $canEdit = isLoggedIn() && $_SESSION['user_id'] === $post['user_id'];
?>
  <div id="view-post-<?= htmlspecialchars($post['id']) ?>" class="card mb-3">
    <div class="card-body">
      <div class="d-flex align-items-center">
        <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
        <?php if ($canEdit) { ?>
          <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" onclick="document.getElementById('view-post-<?= htmlspecialchars($post['id']) ?>').style.display='none';document.getElementById('edit-post-<?= htmlspecialchars($post['id']) ?>').style.display='block';">Edit</button>
        <?php } ?>
      </div>
      <p class="card-text"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
      <p class="card-text"><small class="text-muted">
          Posted on <?= htmlspecialchars($post['created']) ?>
          by <?= htmlspecialchars(read('user', $post['user_id'])['username'] ?? '[deleted]') ?>
        </small></p>
    </div>
  </div>
  <?php if ($canEdit) { ?>
    <div id="edit-post-<?= htmlspecialchars($post['id']) ?>" class="card mb-3" style="display:none;">
      <div class="card-body">
        <form method="post" action="edit.php?type=post&id=<?= htmlspecialchars($post['id']) ?>">
          <div class="mb-3">
            <label for="title-<?= $post['id'] ?>" class="form-label">Title</label>
            <input type="text"
              class="form-control"
              id="title-<?= $post['id'] ?>"
              name="title"
              value="<?= htmlspecialchars($post['title']) ?>"
              required>
          </div>

          <div class="mb-3">
            <label for="content-<?= $post['id'] ?>" class="form-label">Content</label>
            <textarea class="form-control"
              id="content-<?= $post['id'] ?>"
              name="content"
              rows="10"
              required><?= htmlspecialchars($post['content']) ?></textarea>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-danger ms-2"
              onclick="if(confirm('Delete this post?')) 
                                 window.location.href='delete.php?type=post&id=<?= htmlspecialchars($post['id']) ?>'">
              Delete
            </button>
            <button type="button" class="btn btn-secondary ms-2"
              onclick="document.getElementById('edit-post-<?= htmlspecialchars($post['id']) ?>').style.display='none';
                                 document.getElementById('view-post-<?= htmlspecialchars($post['id']) ?>').style.display='block';">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
<?php }
}

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
      'user_id' => $_SESSION['user_id'],
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
    <?php if (isLoggedIn()) { ?>
      <span class="badge bg-primary ms-3 me-auto my-auto mb-auto">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
      <?php if (has_permission('admin_panel')) { ?>
        <a class="btn btn-outline-primary mb-auto" href="admin.php">Admin Panel</a>
      <?php } ?>
      <a class="btn btn-outline-primary ms-3 mb-auto" href="logout.php">Logout</a>
    <?php } else { ?>
      <a class="btn btn-outline-primary ms-auto mb-auto" href="login.php">Login</a>
    <?php } ?>
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
          </form>
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
