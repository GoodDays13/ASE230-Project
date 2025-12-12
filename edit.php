<?php
require_once 'authentication.php';
require_once 'database.php';

$type = $_GET['type'] ?? null;
if ($type === null) {
  echo 'No type specified.';
  http_response_code(400);
  exit();
}

if (!isLoggedIn()) {
  header("Location: login.php");
  exit();
}

$postID = $_GET['id'] ?? null;
if ($postID === null || !is_numeric($postID)) {
  echo "Invalid ID.";
  http_response_code(400);
  exit();
}

$post = read($type, $postID);
if ($post === false) {
  echo "Item not found.";
  http_response_code(404);
  exit();
}

if (!(has_permission('admin_edit')
  || ($type === 'post' && $post['user_id'] == $_SESSION['user_id']))) {
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

$newPost = $_POST;
// Prevent changing immutable fields
unset($newPost['id']);
unset($newPost['created']);
unset($newPost['updated']);
if (isset($newPost['role']) && !has_permission('admin_change_role')) {
  unset($newPost['role']);
  echo "Access denied. Cannot change role.";
  http_response_code(403);
  exit();
}
if (isset($newPost['password'])) {
  if (has_permission('admin_change_password')) {
    $newPost['password'] = password_hash($newPost['password'], PASSWORD_DEFAULT);
  } else {
    unset($newPost['password']);
    echo "Access denied. Cannot change password.";
    http_response_code(403);
    exit();
  }
}

try {
  update($type, $postID, $newPost);
} catch (PDOException $e) {
  if ($e->getCode() == 23000) { // Integrity constraint violation
    echo htmlspecialchars($e->getMessage());
    http_response_code(400);
  } else {
    echo htmlspecialchars($e->getMessage());
    http_response_code(500);
  }
  exit();
} catch (Exception $e) {
  echo "Error updating item: " . htmlspecialchars($e->getMessage());
  http_response_code(500);
  exit();
}

$origin = $_SERVER['HTTP_REFERER'] ?? 'index.php';

header("Location: " . $origin);
exit();
