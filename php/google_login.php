<?php
// FILE: /php/google_login.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// This file handles Google OAuth login initiation
// Redirect to Google OAuth consent screen
function redirectToGoogleAuth()
{
    $client_id = '882382379211-4n465q20rjaurqfio3f56mo7u0l58am3.apps.googleusercontent.com';
    $redirect_uri = 'http://localhost/AgroKart-BD-Web/google_callback.php';
    $scope = 'openid email profile';

    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => $scope,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ]);

    header('Location: ' . $auth_url);
    exit;
}

// If this script is accessed directly, redirect to Google auth
redirectToGoogleAuth();
