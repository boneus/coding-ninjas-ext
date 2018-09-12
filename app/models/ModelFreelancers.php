<?php

namespace codingninjasext;

use \Exception;

class ModelFreelancers
{
	public function getAll()
	{
		$args = array(
			'numberposts' => -1,
			'post_type'   => Freelancer::POST_TYPE
		);

		$posts = get_posts( $args );

		if ( !$posts ) {
			return false;
		}

		foreach ( $posts as &$post ) {
			$post = new Freelancer( $post );
		}

		return $posts;
	}

	public function get( $id ) {

		$post = get_post( $id, OBJECT, 'display' );

		if ( !$post ) {
			return false;
		}
		else {
			return new Freelancer( $post );
		}
	}

	public function getWithLessThanTwoTasks() {

		$freelancers = [];

		$all = $this->getAll();

		foreach ( $all as $one ) {

			$args = array(
				'numberposts' => -1,
				'post_type'   => \codingninjas\Task::POST_TYPE,
				'meta_key'    => '_task_freelancer',
				'meta_value'  => $one->id()
			);

			$posts = get_posts( $args );

			if ( count( $posts ) <= 2 )
				$freelancers[] = $one;
		}

		return $freelancers;
	}
}