<?php

use APP\Models\Category;
use APP\MOdels\Contact;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/admin', function () {
    $categories = Category::all();
    $contacts = Contact::paginate(7);

    return view('admin.index', compact('categories', 'contacts'));
});