<?php

namespace FleetCart\Http\Controllers;

use Google\Client;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google OAuth Consent screen.
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        try {
            $client = new Client();
            $client->setClientId(config('services.google_drive.client_id'));
            $client->setClientSecret(config('services.google_drive.client_secret'));
            $client->setRedirectUri(config('services.google_drive.redirect_uri'));
            $client->addScope('https://www.googleapis.com/auth/drive.file');
            $client->addScope('https://www.googleapis.com/auth/drive');

            // Set access type to offline to receive a refresh token
            $client->setAccessType('offline');
            // Force consent prompt so we always get a refresh token
            $client->setPrompt('consent');

            $authUrl = $client->createAuthUrl();

            return redirect()->away($authUrl);
        } catch (Exception $e) {
            Log::error('Google OAuth Redirect Error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Google OAuth initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the callback from Google.
     *
     * @param Request $request
     * @return mixed
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            Log::error('Google OAuth Callback Error: ' . $request->input('error'));
            return response('Google OAuth Error: ' . $request->input('error'), 400);
        }

        $code = $request->input('code');
        if (!$code) {
            return response()->make('
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Google Authentication Required</title>
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f3f4f6; color: #1f2937; margin: 0; }
                        .card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); text-align: center; max-width: 450px; }
                        h1 { color: #f59e0b; font-size: 1.875rem; margin-bottom: 1rem; }
                        p { font-size: 1rem; line-height: 1.5; color: #4b5563; margin-bottom: 1.5rem; }
                        .badge { display: inline-block; background-color: #fef3c7; color: #d97706; padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; margin-bottom: 1.5rem; }
                        .btn { display: inline-block; background-color: #4f46e5; color: white; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: 500; transition: background-color 0.2s; }
                        .btn:hover { background-color: #4338ca; }
                    </style>
                </head>
                <body>
                    <div class="card">
                        <div class="badge">Authorization Required</div>
                        <h1>Google Drive Login Required</h1>
                        <p>You cannot access this callback page directly. You must first start the Google login process by clicking the button below.</p>
                        <a href="/google/auth" class="btn">Start Authorization Process</a>
                    </div>
                </body>
                </html>
            ', 400);
        }

        try {
            $client = new Client();
            $client->setClientId(config('services.google_drive.client_id'));
            $client->setClientSecret(config('services.google_drive.client_secret'));
            $client->setRedirectUri(config('services.google_drive.redirect_uri'));

            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                Log::error('Google OAuth Fetch Token Error: ' . json_encode($token));
                return response('Google OAuth Failed: ' . ($token['error_description'] ?? $token['error']), 400);
            }

            if (!isset($token['refresh_token'])) {
                Log::warn('Google OAuth Token response does not contain a refresh token: ' . json_encode($token));
                // Try checking if we already have a refresh token in env, otherwise fail
                if (!config('services.google_drive.refresh_token')) {
                    return response('Google OAuth Warning: Authorized successfully, but no Refresh Token was returned. Please go to your Google Account settings, revoke access for this app, and try again to trigger the consent prompt.', 400);
                }
                $refreshToken = config('services.google_drive.refresh_token');
            } else {
                $refreshToken = $token['refresh_token'];
                // Save the refresh token securely in the .env file
                DotenvEditor::setKey('GOOGLE_REFRESH_TOKEN', $refreshToken)->save();
            }

            Log::info('Google OAuth Refresh Token updated successfully.');

            return response()->make('
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Google Authentication Successful</title>
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f3f4f6; color: #1f2937; margin: 0; }
                        .card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); text-align: center; max-width: 450px; }
                        h1 { color: #10b981; font-size: 1.875rem; margin-bottom: 1rem; }
                        p { font-size: 1rem; line-height: 1.5; color: #4b5563; margin-bottom: 1.5rem; }
                        .badge { display: inline-block; background-color: #ecfdf5; color: #047857; padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; margin-bottom: 1.5rem; }
                        a { display: inline-block; background-color: #4f46e5; color: white; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: 500; transition: background-color 0.2s; }
                        a:hover { background-color: #4338ca; }
                    </style>
                </head>
                <body>
                    <div class="card">
                        <div class="badge">Success</div>
                        <h1>Authorized Successfully!</h1>
                        <p>Your Google Drive account has been linked successfully. The refresh token has been stored in your .env file. Automatic backups will now run seamlessly.</p>
                        <a href="/">Go to Home</a>
                    </div>
                </body>
                </html>
            ', 200);
        } catch (Exception $e) {
            Log::error('Google OAuth Callback Exception: ' . $e->getMessage());
            return response('Google OAuth Callback Exception: ' . $e->getMessage(), 500);
        }
    }
}
