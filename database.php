<?php

// JSON file path, resolved relative to this file to avoid CWD issues
$databasePath = __DIR__ . "/raw_files/db.json";

// ---------- Internal Helpers ----------
function ensureDatabaseReady()
{
  global $databasePath;
  $dir = dirname($databasePath);
  if (!is_dir($dir)) {
    if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
      return false;
    }
  }
  if (!is_writable($dir)) {
    return false;
  }
  if (!file_exists($databasePath)) {
    if (@file_put_contents($databasePath, json_encode([])) === false) {
      return false;
    }
  }
  if (file_exists($databasePath) && !is_writable($databasePath)) {
    return false;
  }
  return true;
}

function loadPosts()
{
  global $databasePath;
  if (!ensureDatabaseReady()) {
    return false;
  }
  $data = [];

  $json = @file_get_contents($databasePath);

  if ($json === false) {
    return false;
  }

  $data = json_decode($json, true);

  if (!is_array($data)) {
    return false;
  }

  return $data;
}

function savePosts($posts)
{
  global $databasePath;
  if (!ensureDatabaseReady()) {
    return false;
  }
  $json = json_encode($posts, JSON_PRETTY_PRINT);
  if ($json === false) {
    return false;
  }
  return file_put_contents($databasePath, $json);
}

function sortByCreatedAtDesc($posts)
{
  usort($posts, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
  });
  return $posts;
}

// ---------- Public API ----------

// Create a new post. Returns the created post or false on conflict.
function createPost($postData)
{
  $posts = loadPosts();
  if ($posts === false) {
    return false;
  }
  var_dump($posts);

  $posts[] = $postData;
  $posts = sortByCreatedAtDesc($posts);
  var_dump($posts);

  if (!savePosts($posts)) {
    return false;
  }
  return $postData;
}
// List all posts.
function listPosts()
{
  return loadPosts();
}
