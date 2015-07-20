<?php

namespace App\Controller;

use App\Model\Photo;
use App\Services\Facebook;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Facebook\GraphUser;
use App\Model\User;

class AppController
{
    private $config;
    private $facebook;
    private $loginUrlOrGraphUser;

    public function __construct($app) {
        $this->config = $app['config'];
        $this->appSession = $app['session'];
        $this->loginUrlOrGraphUser = $this->getLoginUrlOrGraphUser();
    }

    /**
     * @return GraphUser|string
     */
    private function getLoginUrlOrGraphUser() {
        $configFacebook = $this->config['facebook'];
        $this->facebook = new Facebook($configFacebook['id'], $configFacebook['secret'], $configFacebook['url']);
        $loginUrlOrGraphUser = $this->facebook->connect();
        return $loginUrlOrGraphUser;
    }

    /**
     * @param array $params
     * @param null $request
     * @return array
     */
    private function getParamsTwig($params = array(), $request = null) {
        if ($this->loginUrlOrGraphUser instanceof GraphUser) {
            $graphUser = $this->loginUrlOrGraphUser;

            // Create and update user
            $user = User::firstOrCreate(['facebook_id' => $graphUser->getId()]);
            if (!$user instanceof User) {
                $user = new User();
                $user->facebook_id = $graphUser->getId();
            }
            $user->gender = $graphUser->getGender() == 'male' ? 'M.' : 'Mme';
            $user->email = $graphUser->getEmail();
            $user->first_name = $graphUser->getFirstName();
            $user->last_name = $graphUser->getLastName();
            $user->save();
        }

        $configFacebook = $this->config['facebook'];
        return array_merge([
            'APP_ID' => $configFacebook['id'],
            'URL_WEB_APP' => $configFacebook['url'],
            'user' => isset($graphUser) ? $graphUser : null
        ], $params);
    }


    /**
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request, Application $app)
    {
        if ($this->loginUrlOrGraphUser instanceof GraphUser) {
            return $app->redirect('/galeries');
        } else {
            return $app['twig']->render('home.html.twig', $this->getParamsTwig([
                'loginUrl' => $this->loginUrlOrGraphUser,
                'userName' => isset($userName) ? $userName : null
            ]));
        }
    }

    public function galleriesAction(Request $request, Application $app)
    {
        $photos     = Photo::all();
        $played     = $this->hasPlayed();
        $likes      = $this->getAllLikes($photos);

        return $app['twig']->render('galleries.html.twig', $this->getParamsTwig([
            'photos' => $photos,
            'played' => $played,
            'likes'  => $likes
        ]));
    }

    public function albumsAction(Request $request, Application $app)
    {
        $albums = $this->facebook->getGraphObject('/me/albums')->asArray();
        $albums = $albums['data'];

        return $app->json([
            'data' => [
                'albums' => $albums,
                'token' => $this->facebook->getToken()
            ]
        ]);
    }

    public function photosByAlbumAction(Request $request, Application $app)
    {
        $album_id = $request->get('id');
        $photos = $this->facebook->getGraphObject('/' . $album_id . '/photos')->asArray();
        $photos = $photos['data'];

        return $app->json([
            'data' => [
                'photos' => $photos
            ]
        ]);
    }

    //check if user has played
    private function hasPlayed() {
        $played         = false;
        $user_fb_id     = $this->loginUrlOrGraphUser->getId();
        $user           = User::where('facebook_id', $user_fb_id)->first();
        $user_has_photo = Photo::where('user_id', $user->id)->first();
        if(is_object($user_has_photo)) {
            $played = true;
        }

        return $played;
    }

    private function getAllLikes($photos) {
        $all_likes = 0;
        foreach ($photos as $photo) {
            $url = 'http://silex-facebook.dev.io/photo/'.$photo->photo_id.'/ft';
            $likes = $this->facebook->getGraphObject('/'.$url.'/likes', 'GET')->asArray();
            $all_likes = $all_likes + $likes['share']->share_count;
        }
        return $all_likes;
    }

    //upload photo
    public function uploadPhotoAction(Request $request, Application $app)
    {
        $played = $this->hasPlayed();
        $from   = $request->get('from');

        if (null !== $request->get('src')) {
            $photo_src = $request->get('src');
        }

        //photo upload from desktop
        if($from == 'desktop') {
            $album_id = $request->get('album_id');
            $photo_data = [
                'source' => new \CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'])
            ];
            $photo = $this->facebook->getGraphObject('/'. $album_id . '/photos', 'post', $photo_data)->asArray();
            $photo = $this->facebook->getGraphObject('/'.$photo['id'], 'GET')->asArray();
            $photo_src = $photo['source'];
        }

        //get user
        $user_fb_id = $this->loginUrlOrGraphUser->getId();
        $user = User::where('facebook_id', $user_fb_id)->first();

        // update if played
        if($played) {
            $photo = Photo::where('user_id', $user->id);
            $photo->update([
                'link'        => $photo_src,
                'title'       => $request->get('title'),
                'description' => $request->get('description'),
                'photo_id'    => $request->get('photo_id')
            ]);

            return $app->redirect('/galeries');
        }

        // save in database
        $photo = new Photo();
        $photo->user_id     = $user->id;
        $photo->link        = $photo_src;
        $photo->title       = $request->get('title');
        $photo->description = $request->get('description');
        $photo->photo_id    = $request->get('photo_id');
        $photo->save();

        return $app->redirect('/galeries');
    }

    public function singlePhotoAction(Request $request, Application $app) {
        $photo = Photo::where('photo_id', $request->get('id'))->first();
        $user = User::find($photo->user_id);
        return $app['twig']->render('photo.html.twig', $this->getParamsTwig(['photo' => $photo, 'user'=>$user]));        
    }

    public function prizeListAction(Request $request, Application $app)
    {
        return $app['twig']->render('prize_list.html.twig', $this->getParamsTwig());
    }

    public function logoutAction() {
        $facebook->getLogoutUrl($params);
    }
}