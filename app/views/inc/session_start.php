<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(APP_SESSION_NAME);
    session_start();
}
