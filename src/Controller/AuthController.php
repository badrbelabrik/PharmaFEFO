<?php

namespace PharmaFEFO\Controller;

use PharmaFEFO\Repository\UserRepository;

class AuthController
{
    private UserRepository $userRepo;

    public function __construct(){
        $this->userRepo = new UserRepository();
    }

    public function login(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?route=dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
        } else {
            $this->showLoginForm();
        }
    }

    private function showLoginForm(): void {
        require_once __DIR__ . '/../../templates/auth/login.php';
    }

    private function processLogin(): void {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Please fill in all fields.";
            require_once __DIR__ . '/../../templates/auth/login.php';
            return;
        }

        $user = $this->userRepo->verifyLogin($email, $password);

        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_firstname'] = $user->getFirstname();
            $_SESSION['user_lastname'] = $user->getLastname();
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['user_role'] = $user->getRole();

            header('Location: index.php?route=dashboard');
            exit();
        } else {
            $error = "Invalid credentials. Please try again.";
            require_once __DIR__ . '/../../templates/auth/login.php';
        }
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: index.php?route=login');
        exit();
    }
}