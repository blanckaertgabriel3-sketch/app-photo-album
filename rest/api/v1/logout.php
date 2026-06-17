<?php

session_start();

require "../../config/header.php";

$_SESSION = [];

session_destroy();