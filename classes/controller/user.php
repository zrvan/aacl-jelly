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
   public function action_force_login($u = false)
   {
      if ( $u ) {
         $username = $u;
         $this->auth->force_login($username);
         if ( $this->auth->get_user()) {
            Notices::add('success','Forced in successfully as '.$this->auth->get_user()->username);
            Request::instance()->redirect('user/index');
         } else {
            $errors = array('Not forced in.');
         }
      }
   }
   public function action_login()
   {
      $user = Jelly::factory('user');

      $this->template->content = $user->subform(array('username', 'password'))
              ->add('submit', 'submit');

      if ($user->subform->load()->validate()) {
         if($this->auth->login($user->username, $user->password)) {
            $this->user = $this->auth->get_user();
            Notices::add('success','Logged in successfully as '.$this->user->username);
            $this->request->redirect('user/index');
         } else {
            Notices::add('error','Login or password incorrect');
         }
      }
   }
   public function action_logout()
   {
      $this->auth->logout();
      Notices::add('success','Logged out');
      Request::instance()->redirect('user/login');
   }
   /*
    * Setting rules
    */
   public function action_control()
   {
      $this->template->content = View::factory('user/control', array(
                                       'resources'=>AACL::list_resources(),
                                       'roles'=> Jelly::select('role')->execute(),
                                       ));
   }
   /*
    * Managing users
    */
   public function action_users()
   {

   }

	public function after()
	{
		return parent::after();
	}

} // End User
