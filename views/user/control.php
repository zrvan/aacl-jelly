<form method="post">
<table>
   <thead>
   <td>Action</td>
   <?php
      echo '<td>guest</td>';
      foreach( $roles as $role )
      {
         echo '<td>'.$role->name.'</td>';
      }
   ?>
   </thead>
<?php
foreach($resources as $resource => $actions)
{
   if( count($actions['actions']) > 0)
   {
      echo '<tr><td><h2>'.$resource.'</h2></td>';
      /*echo '<td>'.(AACL::granted(NULL,$resource,NULL)? '+':'-').'</td>';
      foreach( $roles as $role )
      {
         echo '<td>'.(AACL::granted($role->name,$resource,NULL)? '+':'-').'</td>';
      }*/
      echo '</tr>';
      foreach( $actions['actions'] as $action)
      {
         echo '<tr><td>'.$action.'</td>';
         echo '<td>'.Form::checkbox('grant[/'.$resource.'/'.$action.']', true, AACL::granted(NULL,$resource,$action)).'</td>';
         foreach( $roles as $role )
         {
            echo '<td>'.Form::checkbox('grant['.$role->name.'/'.$resource.'/'.$action.']', true, AACL::granted($role->name,$resource,$action)).'</td>';
         }
      }
   }
}
?>
</table>
   <input type="submit" name="submit" />
</form>