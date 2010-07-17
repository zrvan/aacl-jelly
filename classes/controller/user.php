<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * User controller
 * 
 * @see			http://github.com/ckald/aacl-jelly
 * @package		AACL
 * @uses		Auth
 * @uses		Jelly
 * @author		Andrew Magalich
 * @copyright	(c) Andrew Magalich 2010
 * @license		MIT
 */
class Controller_User extends Controller_Layout {

   public $model = 'user';

	public function before()
	{
		parent::before();
	}

   public function action_index()
   {
      if ( !$this->user )
      {
         $this->request->redirect('user/login');
      }
      Notices::add('info','Hi, '.$this->user->username);
   }
   public function action_login($u = false)
   {
      if ( $u ) {
         $username = $u;
         $password = 'password';
         if ($this->auth->login($username, $password, FALSE)) {
            Notices::add('success','Logged in successfully as '.$this->auth->get_user()->username);
            Request::instance()->redirect('user/index');
         } else {
            $errors = array('Login or password incorrect');
         }
      }
   }
   public function action_logout()
   {
      $this->auth->logout();
      Notices::add('success','Logged out');
      Request::instance()->redirect('user/login');
   }

	public function after()
	{
		return parent::after();
	}

} // End User
