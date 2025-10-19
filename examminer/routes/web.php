<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

/*
|--------------------------------------------------------------------------
| Helper: choose view by device (with ?m=1/0 override)
|--------------------------------------------------------------------------
*/
$deviceView = function (Request $request, Agent $agent, string $desktopView, string $mobileView, array $data = []) {
    // Force for testing: /url?m=1 (mobile) or ?m=0 (desktop)
    $override = $request->query('m');
    if ($override === '1') return view($mobileView, $data);
    if ($override === '0') return view($desktopView, $data);

    $isMobile = $agent->isMobile() && !$agent->isTablet();
    return view($isMobile ? $mobileView : $desktopView, $data);
};

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('landing'))->name('home');

/* Auth pages (Blade UI; views live in resources/views/auth/) */
Route::view('/login',  'auth.login')->name('login');
Route::view('/signup', 'auth.signup')->name('signup');
// If you have this view, keep it; otherwise comment it out to avoid "View not found"
Route::view('/recover', 'auth.recover')->name('recover');
Route::view('/reset-password', 'auth.reset-password')->name('password.reset');

/*
|--------------------------------------------------------------------------
| App pages (client-side protected via JWT)
| Device-aware views: desktop vs mobile.*
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function (Request $r, Agent $a) use ($deviceView) {
    return $deviceView($r, $a, 'dashboard', 'mobile.dashboard');
})->name('dashboard');

Route::get('/generate-exam', function (Request $r, Agent $a) use ($deviceView) {
    return $deviceView($r, $a, 'generate-exam', 'mobile.generate-exam');
})->name('generate-exam');

Route::get('/my-exams', function (Request $r, Agent $a) use ($deviceView) {
    return $deviceView($r, $a, 'my-exams', 'mobile.my-exams');
})->name('my-exams');

Route::get('/profile', function (Request $r, Agent $a) use ($deviceView) {
    return $deviceView($r, $a, 'profile', 'mobile.profile');
})->name('profile');

/* Exam details (e.g. /exam/123) */
Route::get('/exam/{id}', function (int $id, Request $r, Agent $a) use ($deviceView) {
    return $deviceView($r, $a, 'exam-view', 'mobile.exam-view', ['id' => $id]);
})->whereNumber('id')->name('exam.view');

/*
|--------------------------------------------------------------------------
| 404 fallback
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return view()->exists('errors.404')
        ? response()->view('errors.404', [], 404)
        : response('Not Found', 404);
});
