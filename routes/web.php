<?php

use Illuminate\Support\Facades\Route;

$screens = ['dashboard','resumes','analyze','ats','jobs','interviews','skills','insights','portfolio','analytics','profile','billing','settings','help','pricing','privacy','terms'];

Route::get('/', fn () => view('welcome'));
foreach ($screens as $screen) {
    Route::get("/{$screen}", fn () => view('app', ['screen' => $screen]))->name($screen);
}
