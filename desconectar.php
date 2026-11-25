<?php
require_once "nucleo.php";
session_destroy();
header("Location: index.php");
exit;