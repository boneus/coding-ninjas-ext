<?php

namespace codingninjasext;

use \Exception;
use \WP_Post;

class Freelancer
{
	/**
	 * post instance
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * post type name
	 */
	const POST_TYPE = 'freelancer';

	/**
	 * Freelancer constructor.
	 * @param WP_Post $post
	 */
	public function __construct(WP_Post $post)
	{
		$this->post = $post;
	}

	/**
	 * id for table
	 * @return string
	 */
	public function id()
	{
		$id = $this->post->ID;
		return apply_filters ('cn_freelancer_id', $id, $this->post);
	}

	/**
	 * freelancer name
	 * @return string
	 */
	public function name()
	{
		$name = get_post_meta( $this->post->ID, '_freelancer_name', true );
		return apply_filters ('cn_freelancer_name', $name, $this->post);
	}

	/**
	 * freelancer avatar
	 * @return string
	 */
	public function avatar( $size = 'thumbnail' )
	{
		$attachment_id = get_post_meta( $this->post->ID, '_freelancer_avatar', true );
		$avatar = wp_get_attachment_image_src( $avatar, $size );
		return apply_filters ('cn_freelancer_avatar', $avatar[0], $this->post);
	}
}