<?php

namespace Core;

use Cartalyst\Sentinel\Native\Facades\Sentinel as Sentinel;
use Core\LoginHelper as LoginHelper;

class TwigFunction extends \Twig_Extension {

    public function __construct() {
        
    }

    public function getName() {
        return 'slim-twig-helper';
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('isLogin_status', array($this, 'isLogin_status')),
            new \Twig_SimpleFunction('isLogin_profileid', array($this, 'isLogin_profileid')),
            new \Twig_SimpleFunction('isLogin_getProfilepicture', array($this, 'isLogin_getProfilepicture')),
            new \Twig_SimpleFunction('isLogin_getProfilename', array($this, 'isLogin_getProfilename')),
            new \Twig_SimpleFunction('isLogin_getFacebook', array($this, 'isLogin_getFacebook')),
            new \Twig_SimpleFunction('isLogin_getGoogleplus', array($this, 'isLogin_getGoogleplus')),
            new \Twig_SimpleFunction('isLogin_getTwitter', array($this, 'isLogin_getTwitter')),
            new \Twig_SimpleFunction('isLogin_getInstagram', array($this, 'isLogin_getInstagram')),
            new \Twig_SimpleFunction('isLogin_getLinkedin', array($this, 'isLogin_getLinkedin')),
            new \Twig_SimpleFunction('isLogin_getProfilenameSlug', array($this, 'isLogin_getProfilenameSlug')),
            new \Twig_SimpleFunction('isLogin_status_fb', array($this, 'isLogin_status_fb')),
            new \Twig_SimpleFunction('isLogin_getSessionPhoto', array($this, 'isLogin_getSessionPhoto')),
            new \Twig_SimpleFunction('isLogin_getMetaPhoto', array($this, 'isLogin_getMetaPhoto')),
            new \Twig_SimpleFunction('isLogin_gettitle', array($this, 'isLogin_gettitle')),
            new \Twig_SimpleFunction('isLogin_getNumberNotification', array($this, 'isLogin_getNumberNotification')),
        ];
    }

    public function isLogin_status() {
        $result = LoginHelper::getLoginInfo();
        return $result["status"];
    }

    public function isLogin_status_fb() {
        $result = LoginHelper::getLoginInfo();
        return $result["sources"];
    }

    public function isLogin_profileid() {
        $result = LoginHelper::getLoginInfo();
        return $result["profile_id"];
    }

    public function isLogin_getProfilepicture() {
        $result = LoginHelper::getLoginInfo();
       
        return $result["photo_url"];
    }

    public function isLogin_getProfilename() {
        $result = LoginHelper::getLoginInfo();
        return $result["slug"];
    }

    public function isLogin_getProfilenameSlug() {
        $result = LoginHelper::getLoginInfo();
        return $result["slug_fb"];
    }

    public function isLogin_getFacebook() {
        $result = LoginHelper::getLoginInfo();
        return $result["facebook_id"];
    }

    public function isLogin_getGoogleplus() {
        $result = LoginHelper::getLoginInfo();
        return $result["googleplus_id"];
    }

    public function isLogin_getTwitter() {
        $result = LoginHelper::getLoginInfo();
        return $result["twitter_id"];
    }

    public function isLogin_getInstagram() {
        $result = LoginHelper::getLoginInfo();
        return $result["instagram_id"];
    }

    public function isLogin_getLinkedin() {
        $result = LoginHelper::getLoginInfo();
        return $result["linkedin_id"];
    }

    public function isLogin_getMetaPhoto() {
        $result = LoginHelper::getPhotoInfo();
        return $result["img_large"];
    }

    public function isLogin_gettitle() {
        $result = LoginHelper::getPhotoInfo();
        return $result["title"];
    }

    public function isLogin_getSessionPhoto() {
        $result = "";
            $result = $_SESSION['session_photo_id'];
        return $result;
    }
    
     public function isLogin_getNumberNotification()
    {
        $result = LoginHelper::getUnreadNotification();
        return $result["is_read"];
    }

}
