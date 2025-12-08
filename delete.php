<?php
require_once 'authentication.php';
require_once 'database.php';

$type = $_GET['type'] ?? null;
if ($type === null) {
  echo 'No type specified.';
  http_response_code(400);
  exit();
}

$postID = $_GET['id'] ?? null;
if ($postID === null || !is_numeric($postID)) {
  echo "Invalid ID.";
  http_response_code(400);
  exit();
}

// Permission checks
if (!isLoggedIn()) {
  header("Location: login.php");
  exit();
}
if (!(has_permission('admin_delete')
  || ($type === 'post' && read('post', $postID)['user_id'] == $_SESSION['user_id']))) {
  echo "Access denied. Insufficient permissions.";
  http_response_code(403);
  exit();
}

if (!has_permission('admin_change_role')) {
  if ($type === 'permission') {
    echo "Access denied. Cannot edit permissions.";
    http_response_code(403);
    exit();
  } else if ($type === 'role') {
    echo "Access denied. Cannot edit roles.";
    http_response_code(403);
    exit();
  } else if ($type === 'role_permission') {
    echo "Access denied. Cannot edit role permissions.";
    http_response_code(403);
    exit();
  }
}

if (delete($type, $postID) === false) {
  echo "Item not found or could not be deleted.";
  http_response_code(404);
  exit();
}

$origin = $_SERVER['HTTP_REFERER'] ?? 'index.php';

header("Location: " . $origin);
exit();
