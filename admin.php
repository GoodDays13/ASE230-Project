<?php
require_once 'authentication.php';
require_once 'database.php';

if (!isLoggedIn()) {
  header("Location: login.php");
  exit();
} else if (getLevelOfRole($_SESSION['role']) < getLevelOfRole('admin')) {
  echo "Access denied. Admins only.";
  exit();
}

function renderData($data)
{
  foreach ($data as $key => $value) { ?>
    <p><strong><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $key))); ?>:</strong> <?php echo htmlspecialchars($value); ?></p>
  <?php }
}

function renderEditableData($data)
{
  foreach ($data as $key => $value) {
    if ($key === 'id' || $key === 'created_at' || $key === 'updated_at' || $key === 'password') {
      continue; // Skip non-editable fields
    }
    echo '<div class="mb-3">';
    echo '<label for="' . htmlspecialchars($key) . '" class="form-label">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . '</label>';
    echo '<input type="text" class="form-control" id="' . htmlspecialchars($key) . '" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
    echo '</div>';
  }
}

function renderDataWithEdit($type, $items)
{
  foreach ($items as $id => $user) { ?>
    <div class="card mb-4 p-3">
      <div class="d-flex align-items-center justify-content-between">
        <h5 class="mb-0">ID: <?= htmlspecialchars($id) ?></h5>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="toggleEdit('<?= $type ?>-<?= $id ?>')">
          <i class="bi bi-pencil"></i> Edit
        </button>
      </div>
      <div id="<?= $type ?>-<?= $id ?>-view">
        <?php renderData($user); ?>
      </div>
      <div id="<?= $type ?>-<?= $id ?>-edit" style="display:none;">
        <form method="post" action="edit.php?type=<?= $type ?>&id=<?= htmlspecialchars($id) ?>">
          <?php renderEditableData($user); ?>
          <button type="submit" class="btn btn-primary mt-2">Save Changes</button>
          <button type="button" class="btn btn-danger ms-2 mt-2" onclick="if(confirm('Are you sure you want to delete this <?= $type ?>?')) { window.location.href='delete.php?type=<?= $type ?>&id=<?= htmlspecialchars($id) ?>'; }">Delete</button>
          <button type="button" class="btn btn-secondary ms-2 mt-2" onclick="document.getElementById('<?= $type ?>-<?= $id ?>-edit').style.display='none'; document.getElementById('<?= $type ?>-<?= $id ?>-view').style.display='block';">Cancel</button>
        </form>
      </div>
    </div>
<?php }
}

$type = $_GET['type'] ?? null;
$query = $_GET['query'] ?? null;

$users = readAll('user');
$posts = readAll('post');

if ($type === 'user' && $query) {
  $users = array_filter($users, function ($user) use ($query) {
    foreach ($user as $field) {
      if (preg_match('/' . $query . '/i', $field)) {
        return true;
      }
    }
    return false;
  });
} elseif ($type === 'post' && $query) {
  $posts = array_filter($posts, function ($post) use ($query) {
    foreach ($post as $field) {
      if (preg_match('/' . $query . '/i', $field)) {
        return true;
      }
    }
    return false;
  });
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
  <div class="container d-flex mt-5">
    <h1>Admin Panel</h1>
    <span class="badge bg-primary ms-3 my-auto mb-auto">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
    <a class="btn btn-outline-primary ms-auto mb-auto" href="index.php">Home</a>
    <a class="btn btn-outline-primary ms-3 mb-auto" href="logout.php">Logout</a>
    </a>
  </div>
  <div class="container mt-5">
    <div class="accordion" id="entityAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button <?= $type === 'user' ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
            Users
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse <?= $type === 'user' ? 'show' : '' ?>" data-bs-parent="#entityAccordion">
          <div class="accordion-body">
            <input type="text" class="form-control mb-3" placeholder="Search Users..." onkeydown="if(event.key==='Enter'){window.location.href='admin.php?type=user&query=' + this.value}" value="<?= $type === 'user' ? htmlspecialchars($query ?? '') : '' ?>">
            <?php
            renderDataWithEdit('user', $users);
            ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button <?= $type === 'post' ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            Posts
          </button>
        </h2>
        <div id="collapseTwo" class="accordion-collapse collapse <?= $type === 'post' ? 'show' : '' ?>" data-bs-parent="#entityAccordion">
          <div class="accordion-body">
            <input type="text" class="form-control mb-3" placeholder="Search Posts..." onkeydown="if(event.key==='Enter'){window.location.href='admin.php?type=post&query=' + this.value}" value="<?= $type === 'post' ? htmlspecialchars($query ?? '') : '' ?>">
            <?php
            renderDataWithEdit('post', $posts);
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

<script>
  function toggleEdit(userId) {
    document.getElementById(userId + '-view').style.display = 'none';
    document.getElementById(userId + '-edit').style.display = 'block';
    // Optionally hide the edit button if needed
  }
</script>

</html>
