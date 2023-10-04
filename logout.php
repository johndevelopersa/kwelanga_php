<?php

if (!isset($_SESSION)) session_start();
session_unset();
session_destroy();

header('location:' . ($_SERVER['REQUEST_SCHEME']??'https') . '://'. $_SERVER['HTTP_HOST']);

