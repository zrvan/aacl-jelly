<table>
   <thead>
   <td>Action</td>
   <?php
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
      foreach( $roles as $role )
      {
         echo '<td>'.Form::checkbox($role->name.'/'.$resource.'/', true, AACL::granted($role->name,$resource,NULL)).'</td>';
      }
      echo '</tr>';
      foreach( $actions['actions'] as $action)
      {
         echo '<tr><td>'.$action.'</td>';
         foreach( $roles as $role )
         {
            echo '<td>'.Form::checkbox($role->name.'/'.$resource.'/'.$action, true, AACL::granted($role->name,$resource,$action)).'</td>';
         }
      }
   }
}
?>
</table>