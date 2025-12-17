<?php

use \Botble\Blog\Http\Controllers\API\CategoryController;

Route::get('categories', [CategoryController::class, 'index']);
