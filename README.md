# TaskLock

[![Build Status](https://travis-ci.org/AndyDune/TaskLock.svg?branch=master)](https://travis-ci.org/AndyDune/TaskLock)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/andydune/task-lock.svg?style=flat-square)](https://packagist.org/packages/andydune/task-lock)
[![Total Downloads](https://img.shields.io/packagist/dt/andydune/task-lock.svg?style=flat-square)](https://packagist.org/packages/andydune/task-lock)


Usefull for control tasks to avoid parallel job. It support MongoDB or files.

Installation
------------

Installation using composer:

```
composer require andydune/task-lock 
```
Or if composer didn't install globally:
```
php composer.phar require andydune/task-lock
```
Or edit your `composer.json`:
```
"require" : {
     "andydune/task-lock": "^1"
}

```
And execute command:
```
php composer.phar update
```

Use it command if you don not need dev code:
```
php composer.phar update --no-dev
```
