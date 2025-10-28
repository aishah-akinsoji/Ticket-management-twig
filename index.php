<?php
session_start();

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    require_once 'src/functions.php';
    $toast = [
        'display' => true,
        'message' => 'Logged out successfully',
        'type' => 'success'
    ];
    session_unset();
    session_destroy();
}

require_once 'vendor\autoload.php';
require_once 'src\functions.php';

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
]);

$errors = [];
$old = [];
$page = $_GET['page'] ?? 'landing';
$mode = $_GET['mode'] ?? 'login';
$display = false;
$message = null;
$type = null;

$allowedPages = ['landing', 'dashboard', 'tickets', 'auth'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'signup') {
        $uname = trim($_POST['uname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $cpassword = $_POST['cpassword'] ?? '';

        $old = ['uname' => $uname, 'email' => $email];

        if (empty($uname) || !preg_match("/^[A-Za-z ]+$/", $uname)) {
            $errors['uname'] = "Please enter a valid full name (letters and spaces only).";
        }

        $emailValid = preg_match("/\S+@\S+\.\S+/", $email) === 1;
        if (empty($email) || !$emailValid) {
            $errors['email'] = "Please enter a valid email address.";
        }

        $passwordDigitValid = preg_match("/[0-9]/", $password) === 1;
        $passwordSymbolValid = preg_match("/[!@#$%^&*(),.\-_'\":?{}|<>]/", $password) === 1;
        $passwordLetterValid = preg_match("/[A-Za-z]/", $password) === 1;

        if (strlen($password) < 8) {
            $errors['password'] = "Password must be at least 8 characters long.";
        } elseif (!$passwordDigitValid) {
            $errors['password'] = "Password must contain at least one digit.";
        } elseif (!$passwordSymbolValid) {
            $errors['password'] = "Password must contain at least one symbol.";
        } elseif (!$passwordLetterValid) {
            $errors['password'] = "Password must contain at least one letter.";
        }

        if ($password !== $cpassword) {
            $errors['cpassword'] = "Passwords do not match.";
        }

        if (empty($errors) && emailExists($email)) {
            $errors['email'] = "Email already registered.";
        }

        if (empty($errors)) {
            saveUser($uname, $email, $password);
            $_SESSION['user'] = $email;
            $_SESSION['toast'] = [
                'display' => true,
                'message' => 'Account created successfully',
                'type' => 'success'
            ];
            header("Location: index.php?page=dashboard");
            exit;
        }

    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $old = ['email' => $email];

        $emailValid = preg_match("/\S+@\S+\.\S+/", $email) === 1;
        $passwordDigitValid = preg_match("/[0-9]/", $password) === 1;
        $passwordSymbolValid = preg_match("/[!@#$%^&*(),.\-_'\":?{}|<>]/", $password) === 1;
        $passwordLetterValid = preg_match("/[A-Za-z]/", $password) === 1;

        if (empty($email) || !$emailValid) {
            $errors['email'] = "Please enter a valid email address.";
        }

        if (strlen($password) < 8) {
            $errors['password'] = "Password must be at least 8 characters long.";
        } elseif (!$passwordDigitValid) {
            $errors['password'] = "Password must contain at least one digit.";
        } elseif (!$passwordSymbolValid) {
            $errors['password'] = "Password must contain at least one symbol.";
        } elseif (!$passwordLetterValid) {
            $errors['password'] = "Password must contain at least one letter.";
        }

        if (empty($errors)) {
            if (checkUserByEmail($email, $password)) {
                $_SESSION['user'] = $email;
                $_SESSION['toast'] = [
                    'display' => true,
                    'message' => 'Login successful!',
                    'type' => 'success'
                ];
                header("Location: index.php?page=dashboard");
                exit;
            } else {
                $errors['email'] = "Invalid email or password.";
            }
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_ticket') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'] ?? 'Open';
    $priority = $_POST['priority'] ?? 'Low';

    if ($title && $description && isset($_SESSION['user'])) {
        $usersFile = __DIR__ . '/users.json';
        $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

        foreach ($users as &$user) {
            if ($user['email'] === $_SESSION['user']) {
                $newTicket = [
                    'id' => uniqid(),
                    'title' => $title,
                    'description' => $description,
                    'status' => $status,
                    'priority' => $priority,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $user['tickets'][] = $newTicket;
                break;
            }
        }

        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

        $_SESSION['toast'] = [
            'display' => true,
            'message' => 'Ticket created successfully!',
            'type' => 'success'
        ];

        header("Location: index.php?page=tickets");
        exit;
    } else {
        $errors['ticket'] = "All fields are required.";
    }
}

$toast = $_SESSION['toast'] ?? null;
if (isset($_SESSION['toast'])) {
    unset($_SESSION['toast']);
}

function getUserData($email) {
    $usersFile = __DIR__ . '/users.json';
    if (!file_exists($usersFile)) {
        return null;
    }
    $users = json_decode(file_get_contents($usersFile), true);
    foreach ($users as $user) {
        if (($user['email'] ?? null) === $email) {
            return $user;
        }
    }
    return null;
}

function getUserTickets($email) {
    $usersFile = __DIR__ . '/users.json';
    if (!file_exists($usersFile)) {
        return [];
    }
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
    foreach ($users as $user) {
        if (($user['email'] ?? null) === $email) {
            return $user['tickets'] ?? [];
        }
    }
    return [];
}

function getTicketSummary($tickets) {
    $summary = [
        'total' => count($tickets),
        'open' => 0,
        'closed' => 0,
        'resolved' => 0
    ];

    foreach ($tickets as $ticket) {
        $status = strtolower($ticket['status'] ?? '');
        if ($status === 'open') $summary['open']++;
        elseif ($status === 'closed') $summary['closed']++;
        elseif ($status === 'resolved') $summary['resolved']++;
    }

    return $summary;
}
$user = null;
$tickets = [];
$ticketSummary = [
    'total' => 0,
    'open' => 0,
    'resolved' => 0,
    'closed' => 0
];

if (isset($_SESSION['user'])) {
    $user = getUserData($_SESSION['user']);
    $tickets = getUserTickets($_SESSION['user']);
    $ticketSummary = getTicketSummary($tickets);
}


if (!in_array($page, $allowedPages)) {
    $page = 'landing';
}

echo $twig->render("$page.twig", [
    'mode' => $mode,
    'errors' => $errors,
    'old' => $old,
    'toast' => $toast,
    'ticketSummary' => $ticketSummary,
    'tickets' => $tickets
]);
