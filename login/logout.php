<?php
require_once "Auth.php";

Auth::logout();

header("Location: /sistema/login/index.php");
exit;