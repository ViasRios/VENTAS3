<?php
session_save_path("C:/xampp/tmp");
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
