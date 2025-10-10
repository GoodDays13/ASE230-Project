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
} else if (getLevelOfRole($_SESSION['role']) < getLevelOfRole('admin')) {
  echo "Access denied. Admins only.";
  http_response_code(403);
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

$newPost = $_POST;
// Prevent changing immutable fields
$newPost['created_at'] = $post['created_at'];
$newPost['updated_at'] = date('Y-m-d H:i:s');

update($type, $postID, $newPost);

header("Location: admin.php");
exit();
