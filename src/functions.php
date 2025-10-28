<?php

function checkUserByEmail($email, $password) {
    $file = 'users.json';
    if (!file_exists($file)) return false;

    $users = json_decode(file_get_contents($file), true);

    foreach ($users['users'] as $user) {
        if (isset($user['email']) && $user['email'] === $email && password_verify($password, $user['password'])) {
            return true;
        }
    }

    return false;
}
function emailExists($email) {
    $users = json_decode(file_get_contents(__DIR__.'/../users.json'), true) ?: [];
    foreach ($users['users'] as $user) {
        if ($user['email'] === $email) return true;
    }
    return false;
}

function saveUser($uname, $email, $password) {
    $usersFile = __DIR__ . '/../users.json';
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $users[] = [
        'uname' => $uname,
        'email' => $email,
        'password' => $hashedPassword,
        'tickets' => []
    ];
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}

function logoutUser() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: index.php?page=landing");
    
    exit;
}
