<?php

namespace App\Classes\Authorization;

use Dlnsk\HierarchicalRBAC\Authorization;


/**
 *  This is example of hierarchical RBAC authorization configiration.
 */

class AuthorizationClass extends Authorization
{
	public function getPermissions() {
		return [
		    //example permissions
			'editPost' => [
					'description' => 'Edit any posts',   // optional property
					'next' => 'editOwnPost',            // used for making chain (hierarchy) of permissions
				],
			'editOwnPost' => [
					'description' => 'Edit own post',
				],

			//---------------
			'deletePost' => [
					'description' => 'Delete any posts',
				],
            //Kodo permissions
            'editUsers' => [
                'description' => 'Permission to edit users'
            ],

            'editTasks' => [
                'description' => 'Permission to edit tasks'
            ],

            'editFiles' => [
                'description' => 'Permission to edit files'
            ]
		];
	}

	public function getRoles() {
		return [
		    'superadmin' => [
		        'editUsers',
                'editBanks',
                'editTasks',
                'editFiles'
            ],
			'manager' => [
					'editTasks',
                    'editFiles'
				],
			'user' => [
					'editTasks',
                    'editFiles'
				],
            'operator' => [
                'editBanks'
            ]
		];
	}


	/**
	 * Methods which checking permissions.
	 * Methods should be present only if additional checking needs.
	 */

	public function editOwnPost($user, $post) {
		$post = $this->getModel(\App\Post::class, $post);  // helper method for geting model

		return $user->id === $post->user_id;
	}

}
