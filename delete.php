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

if (delete($type, $postID) === false) {
  echo "Item not found or could not be deleted.";
  http_response_code(404);
  exit();
}

header("Location: admin.php");
exit();
