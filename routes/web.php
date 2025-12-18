<?php
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/message', [ChatController::class, 'message'])->name('chat.message');
Route::post('/chat/submit-request', [ChatController::class, 'submitRequest'])->name('chat.submitRequest');
Route::view('/about', 'about')->name('about');
Route::post('/voice-to-text', [ChatController::class, 'VoiceToText']);

Route::get('/', function () {
    return view('chat.index');
});