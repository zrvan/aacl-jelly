<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * User controller
 * 
 * @see			http://github.com/ckald/aacl-jelly
 * @package		AACL
 * @uses		Auth
 * @uses		Jelly
 * @uses    Notices
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
            Notices::add('error','Login or password incorrect ');
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
    *
    * This should be totally rewritten
    */
   public function action_control()
   {
      if( isset( $_POST['grant'] ) )
      {
         Jelly::delete('aacl_rule')->execute();
         foreach($_POST['grant'] as $rule => $value)
         {
            $parse = explode('/',$rule);
            $role = ($parse[0]!="")? $parse[0] : NULL;
            $resource = $parse[1];
            $action = isset($parse[2])? $parse[2] : NULL;

            AACL::grant($role,$resource,$action);
         }
         Notices::add('success','Access rules updated');
      }
      $this->template->content = View::factory('user/control', array(
                                       'resources'=>AACL::list_resources(),
                                       'roles'=> Jelly::select('role')->execute(),
                                       ));
   }
   /*
    * Managing users
    */
   public function action_create()
   {
      $user = Jelly::factory($this->model);
      
      $user->form->remove('id');
      if( $user->form->load()->validate() )
      {
         $user->roles = $_POST['roles'];
         $user->save();
         Notices::add('success', 'Created');
         $this->request->redirect('/user/update/'.$user->id);
      }

      $this->template->content = $user->form->add('submit','submit');
   }
   public function action_read($id = NULL)
   {

   }
   public function action_update($id = NULL)
   {
      if( !is_null($id) )
      {
         $user = Jelly::select($this->model,$id);

         $user->form->load()
                     ->remove('password')
                     ->remove('password_confirm');
         if( $user->form->validate() )
         {
            // @todo: Here should be normal many-to-many
            $user->roles = $_POST['roles'];
            $user->save($id);
            Notices::add('success', 'Updated');
            $this->request->redirect('/user/update/'.$id);
         }
         
         $this->template->content = $user->form->add('submit','submit')->render('html');
      }
   }
   public function action_delete($id = NULL)
   {
      if( !is_null($id) )
      {

      }
   }
   public function action_register()
   {
      $user = Jelly::factory($this->model);

      $user->form->remove(array('id','roles'));
      if( $user->form->load()->validate() )
      {
         $user->save();
         Notices::add('success', 'Created');
         $this->request->redirect('/user/update/'.$user->id);
      }

      $this->template->content = $user->form->add('submit','submit');
   }

	public function after()
	{
		return parent::after();
	}

} // End User
