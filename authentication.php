<?php
require_once 'database.php';

$roles = [
  'user',
  'admin',
];

session_start();

function isLoggedIn()
{
  return isset($_SESSION['user_id']);
}

function has_permission($permission_name)
{
  $pdo = ensureDatabaseReady();
  if ($pdo === false || !isLoggedIn()) {
    return false;
  }
  $stmt = $pdo->prepare("
    SELECT COUNT(*) FROM user
    JOIN role ON user.role = role.id
    JOIN role_permission ON role.id = role_permission.role
    JOIN permission ON role_permission.permission = permission.id
    WHERE user.id = ? AND permission.name = ?
  ");
  $stmt->execute([$_SESSION['user_id'], $permission_name]);
  return $stmt->fetchColumn() > 0;
}

function validate($user)
{
  $user['username'] = trim($user['username'] ?? '');
  $user['email'] = trim($user['email'] ?? '');
  $user['password'] = $user['password'] ?? '';
  return preg_match('/^[a-zA-Z0-9_]{3,}$/', $user['username']) // Alphanumeric + underscores, min 3 chars
    && filter_var($user['email'], FILTER_VALIDATE_EMAIL) // Valid email format
    && strlen($user['password']) >= 8; // Min 8 chars
}

function register($user)
{
  if (validate($user)) {
    // Hash the password before storing
    $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
    $user['role'] = '1'; // Default role. 1 = user
    if (create('user', $user) !== false) {
      return true; // Registration successful
    } else {
      return "Error: Could not register user.";
    }
  } else {
    return "Invalid input. Please check your details.";
  }
}

function login($username, $password)
{
  $storedUser = find('user', 'username', $username);
  if ($storedUser !== false) {
    if (password_verify($password, $storedUser['password'])) {
      $_SESSION['user_id'] = $storedUser['id'];
      $_SESSION['username'] = $storedUser['username'];
      $_SESSION['role'] = $storedUser['role'];
      return true;
    }
  }
  return false;
}

function logout()
{
  session_unset();
  session_destroy();
}
