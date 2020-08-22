<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Photos\Library\V1\PhotosLibraryClient;
use Google\Photos\Library\V1\PhotosLibraryResourceFactory;

class MainController extends Controller {

    public function __construct() {
        $this->client = new \Google_Client();
        $this->client->setApplicationName('sample001');
        $this->client->setScopes(\Google_Service_PhotosLibrary::PHOTOSLIBRARY_READONLY);
        $this->client->setAuthConfig(base_path('resources/credentials.json'));
        $this->client->setAccessType('offline');
        $this->client->setIncludeGrantedScopes(true);
        $this->client->setRedirectUri('http://localhost:8000/oauthcallback');
    }

    public function makePhotosCli($token = null) {
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
        }
        $authCredentials = new UserRefreshCredentials(
            \Google_Service_PhotosLibrary::PHOTOSLIBRARY_READONLY, [
            'client_id' => $this->client->getClientId(),
            'client_secret' => $this->client->getClientSecret(),
            'refresh_token' => $token ? $token : $this->client->getRefreshToken()
        ]);
        return new PhotosLibraryClient(['credentials' => $authCredentials]);
    }

    public function index() {
        $authUrl = $this->client->createAuthUrl();
        return view('welcome', ['oauth' => $authUrl]);
    }

    public function callback(Request $request) {
        $code = $request->input('code');
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        $request->session()->put('token', $token);
        return redirect('/main');
    }

    public function main(Request $request) {
        $data = [];
        $token = $request->session()->get('token');
        $this->client->setAccessToken($token);
        $cli = $this->makePhotosCli($token['access_token']);
        $resp = $cli->listMediaItems();
        foreach ($resp->iterateAllElements() as $item) {
            $mediaResp = $cli->getMediaItem($item->getId());
            $data[] = [
                'id' => $item->getId(),
                'fileName' => $item->getFilename(),
                'baseUrl' => $mediaResp->getBaseUrl()
            ];
        }
        return view('main', ['data' => $data]);
    }

}