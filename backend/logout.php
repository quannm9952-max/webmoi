<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/controllers/AuthController.php';

(new AuthController(db_connect()))->logout();
