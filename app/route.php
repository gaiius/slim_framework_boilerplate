<?php

/*
  Routes
  controller needs to be registered in dependency.php
 */
$app->get('/', 'Controller\Home:dispatch')->setName('homepage');
//$app->get('/users', 'Controller\User:dispatch')->setName('userpage');
///* register */
$app->get('/sorry', 'Controller\Home:error');
//DASHBOARD
$app->get('/home', 'Controller\Home:index');
//LOGOUT
