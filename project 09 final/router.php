<?php
if (preg_match('/\.php$/', $_SERVER['REQUEST_URI'])) {
    require __DIR__ . $_SERVER['REQUEST_URI'];
} else {
    require __DIR__ . '/index.html';
}