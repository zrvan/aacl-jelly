<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * Access rule model
 * 
 * @see			http://github.com/banks/aacl
 * @package		AACL
 * @uses		Auth
 * @uses		Jelly
 * @author		Paul Banks
 * @copyright	(c) Paul Banks 2010
 * @license		MIT
 */
class Model_AACL_Rule extends Jelly_AACL
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('rules')
           ->fields(array(
              'id' => new Field_Primary(array(
                 'editable' => false,
              )),
              'role' => new Field_BelongsTo(array(
                 'label' => 'Role',
                 'column' => 'role_id',
                 'foreign' => 'role.id'
              )),
              'resource' => new Field_String(array(
                 'label' => 'Controlled resource',
                 'rules' => array(
                     'max_length' 	=> 45,
                 )
              )),
              'action' => new Field_String(array(
                 'label' => 'Controlled action',
                 'rules' => array(
                     'max_length' 	=> 25,
                     'null'			=> TRUE,
                 ),
                 'default' => NULL,
              )),
              'condition' => new Field_String(array(
                 'label' => 'Access condition',
                 'rules' => array(
                     'max_length' 	=> 25,
                     'null'			=> TRUE,
                 ),
                 'default' => NULL,
              )),
            ));
	}
	
	/**
	 * Check if rule matches current request
	 * 
	 * @param AACL_Resource	AACL_Resource object that user requested access to
	 * @param string        action requested [optional]
	 * @return 
	 */
	public function allows_access_to($resource, $action = NULL)
	{
      if( $resource instanceof AACL_Resource)
      {
         // Not changed banks method
         if (is_null($this->resource))
         {
            // No point checking anything else!
            return TRUE;
         }

         if (is_null($action))
         {
            // Check to see if Resource whats to define it's own action
            $action = $resource->acl_actions(TRUE);
         }

         // Get string id
         $resource_id = $resource->acl_id();

         // Make sure action matches
         if ( ! is_null($action) AND ! is_null($this->action) AND $action !== $this->action)
         {
            // This rule has a specific action and it doesn't match the specific one passed
            return FALSE;
         }

         $matches = FALSE;

         // Make sure rule resource is the same as requested resource, or is an ancestor
         while( ! $matches)
         {
            // Attempt match
            if ($this->resource === $resource_id)
            {
               // Stop loop
               $matches = TRUE;
            }
            else
            {
               // Find last occurence of '.' separator
               $last_dot_pos = strrpos($resource_id, '.');

               if ($last_dot_pos !== FALSE)
               {
                  // This rule might match more generally, try the next level of specificity
                  $resource_id = substr($resource_id, 0, $last_dot_pos);
               }
               else
               {
                  // We can't make this any more general as there are no more dots
                  // And we haven't managed to match the resource requested
                  return FALSE;
               }
            }
         }

         // Now we know this rule matches the resource, check any match condition
         if ( ! is_null($this->condition) AND ! $resource->acl_conditions(Auth::instance()->get_user(), $this->condition))
         {
            // Condition wasn't met (or doesn't exist)
            return FALSE;
         }

         // All looks rosy!
         return TRUE;
      }
      else
      {
         // $resource should be valid resource id

         if (is_null($this->resource))
         {
            // No point checking anything else!
            return TRUE;
         }

         /*if (is_null($action))
         {
            // Check to see if Resource whats to define it's own action
            $action = $resource->acl_actions(TRUE);
         }*/

         // Get string id
         $resource_id = $resource;

         // Make sure action matches
         if ( ! is_null($action) AND ! is_null($this->action) AND $action !== $this->action)
         {
            // This rule has a specific action and it doesn't match the specific one passed
            return FALSE;
         }

         $matches = FALSE;

         // Make sure rule resource is the same as requested resource, or is an ancestor
         while( ! $matches)
         {
            // Attempt match
            if ($this->resource === $resource_id)
            {
               // Stop loop
               $matches = TRUE;
            }
            else
            {
               // Find last occurence of '.' separator
               $last_dot_pos = strrpos($resource_id, '.');

               if ($last_dot_pos !== FALSE)
               {
                  // This rule might match more generally, try the next level of specificity
                  $resource_id = substr($resource_id, 0, $last_dot_pos);
               }
               else
               {
                  // We can't make this any more general as there are no more dots
                  // And we haven't managed to match the resource requested
                  return FALSE;
               }
            }
         }

         // Now we know this rule matches the resource, check any match condition
         if ( ! is_null($this->condition) AND ! $resource->acl_conditions(Auth::instance()->get_user(), $this->condition))
         {
            // Condition wasn't met (or doesn't exist)
            return FALSE;
         }

         // All looks rosy!
         return TRUE;
      }
	}
	
	/**
	 * Override create to remove less specific rules when creating a rule
	 * 
	 * @return $this
	 */
	public function create()
	{
      $meta = $this->meta();
      $fields = $meta->fields();
		// Delete all more specifc rules for this role
		$delete = Jelly::delete($this)
			->where( $fields['role']->column, '=', $this->_changed['role']);
		
		// If resource is '*' we don't need any more rules - we just delete every rule for this role
		
		if ( ! is_null($this->resource) )
		{
			// Need to restrict to roles with equal or more specific resource id
			$delete->where_open()
				->where($fields['resource']->column, '=', $this->resource)
				->or_where($fields['resource']->column, 'LIKE', $this->resource.'.%')
				->where_close();
		}
		
		if ( ! is_null($this->action))
		{
			// If this rule has an action, only remove other rules with the same action
			$delete->where($fields['action']->column, '=', $this->action);
		}
		
		if ( ! is_null($this->condition))
		{
			// If this rule has a condition, only remove other rules with the same condition
			$delete->where($fields['condition']->column, '=', $this->condition);
		}		
		
		// Do the delete
		$delete->execute();
		
		// Create new rule
		parent::save();
	}
	
	/**
	 * Override Default model actions
	 * 
	 * @param	bool	$return_current [optional]
	 * @return	mixed
	 */
	public function acl_actions($return_current = FALSE)
	{
		if ($return_current)
		{
			// We don't know anything about what the user intends to do with us!
			return NULL;
		}
		
		// Return default model actions
		return array('grant', 'revoke');
	}
	
} // End  Model_AACL_Rule