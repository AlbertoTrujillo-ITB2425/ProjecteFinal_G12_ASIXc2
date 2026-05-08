<?php
ini_set('session.save_handler', 'redis');
$redis_pass = getenv('REDIS_PASSWORD') ?: 'redispass';
ini_set('session.save_path', "tcp://s5_redis:6379?auth=$redis_pass");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
