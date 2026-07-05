<?php
/**
 * Main plugin controller that manages the hierarchical location taxonomy system.
 *
 * @package GatherPress_Groups
 */

declare(strict_types=1);

namespace GatherPress_Groups;

use GatherPress\Core;
use GatherPress\Core\Settings;
use WP_Post_Type;

/**
 * Main plugin class using Singleton pattern.
 *
 * @since 0.1.0
 */
class Setup {

	use Core\Traits\Singleton;

	const POST_TYPE_NAME = 'gatherpress_group';

	const TAXONOMY_NAME = '_gatherpress_group';

	/**
	 * Constructor for the Setup class.
	 *
	 * Initializes and sets up various components of the plugin.
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Set up hooks for various purposes.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	protected function setup_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		// Register groups post type.
		add_action( 'init', array( $this, 'register_post_type' ) );
		// Register group shadow taxonomy onto events.
		add_action( 'init', array( $this, 'register_post_tax_relations' ), 12 );

		// Add settings sub-page.
		add_action( 'gatherpress_sub_pages', array( $this, 'setup_sub_page' ) );
	}

	/**
	 * Load the plugin's text domain for translations.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		\load_plugin_textdomain(
			'gatherpress-groups',
			false,
			'gatherpress-groups/languages'
		);
	}

	/**
	 * Returns the post type slug localized for the site language and sanitized as URL part.
	 *
	 * Do not use this directly, use get( 'venues_url' ) instead.
	 *
	 * This method switches to the sites default language and gets the translation of 'venues' for the loaded locale.
	 * After that, the method sanitizes the string to be safely used within an URL,
	 * by removing accents, replacing special characters and replacing whitespace with dashes.
	 *
	 * @since 0.34.0
	 *
	 * @return string
	 */
	protected function get_localized_post_type_slug(): string {
		$switched_locale = switch_to_locale( get_locale() );
		$slug            = __( 'Group', 'gatherpress-groups' );
		$slug            = sanitize_title( $slug );

		if ( $switched_locale ) {
			restore_previous_locale();
		}

		return $slug;
	}

	/**
	 * Build taxonomy labels from a post type's labels.
	 *
	 * Reuses semantically equivalent post type labels whenever possible and
	 * generates taxonomy-specific labels as fallbacks.
	 *
	 * @param string $post_type Post type name.
	 * @return array
	 */
	protected function get_shadow_taxonomy_labels( string $post_type ): array {
		$post_type_object = get_post_type_object( $post_type );

		if ( ! $post_type_object instanceof WP_Post_Type ) {
			return array(
				'name'          => $post_type,
				'singular_name' => $post_type,
			);
		}

		$pt = $post_type_object->labels;

		/*
		* Taxonomy label => equivalent post type label.
		*/
		$equivalents = array(
			'name'                  => 'name',
			'singular_name'         => 'singular_name',
			'menu_name'             => 'menu_name',
			'all_items'             => 'all_items',
			'search_items'          => 'search_items',
			'view_item'             => 'view_item',
			'not_found'             => 'not_found',
			'item_link'             => 'item_link',
			'item_link_description' => 'item_link_description',
		);

		$labels = array();

		foreach ( $equivalents as $taxonomy_label => $post_type_label ) {
			if ( ! empty( $pt->{$post_type_label} ) ) {
				$labels[ $taxonomy_label ] = $pt->{$post_type_label};
			}
		}

		$name     = $labels['name'] ?? $pt->name ?? $post_type;
		$singular = $labels['singular_name'] ?? $pt->singular_name ?? $post_type;

		/*
		* Taxonomy-only labels.
		*/
		$labels += array(
			'popular_items'              => sprintf(
				/* translators: %s is replaced with the plural name of the taxonomy, e.g. "Groups". */
				__( 'Popular %s', 'gatherpress-groups' ),
				$name
			),
			'edit_item'                  => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Group". */
				__( 'Edit %s', 'gatherpress-groups' ),
				$singular
			),
			'update_item'                => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Group". */
				__( 'Update %s', 'gatherpress-groups' ),
				$singular
			),
			'add_new_item'               => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Group". */
				__( 'Add New %s', 'gatherpress-groups' ),
				$singular
			),
			'new_item_name'              => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Group". */
				__( 'New %s Name', 'gatherpress-groups' ),
				$singular
			),
			'separate_items_with_commas' => sprintf(
				/* translators: %s is replaced with the plural name of the taxonomy, e.g. "Groups". */
				__( 'Separate %s with commas', 'gatherpress-groups' ),
				lcfirst( $name )
			),
			'add_or_remove_items'        => sprintf(
				/* translators: %s is replaced with the plural name of the taxonomy, e.g. "Groups". */
				__( 'Add or remove %s', 'gatherpress-groups' ),
				lcfirst( $name )
			),
			'choose_from_most_used'      => sprintf(
				/* translators: %s is replaced with the plural name of the taxonomy, e.g. "Groups". */
				__( 'Choose from the most used %s', 'gatherpress-groups' ),
				lcfirst( $name )
			),
			'parent_item'                => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Group". */
				__( 'Parent %s', 'gatherpress-groups' ),
				$singular
			),
			'parent_item_colon'          => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Group". */
				__( 'Parent %s:', 'gatherpress-groups' ),
				$singular
			),
		);

		return $labels;
	}

	/**
	 * Register the custom post type for groups.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		add_filter( 'gatherpress_shadow_taxonomy_args', function( $args, $post_type ) {
			if ( self::POST_TYPE_NAME === $post_type ) {
				$args['labels']             = $this->get_shadow_taxonomy_labels( $post_type );
				$args['show_in_quick_edit'] = true;
				$args['show_ui']            = true; // Needed to show the taxonomy metabox in the editor.
				$args['show_in_menu']       = false; // Correction after show_ui.
			}
			return $args;
		}, 10, 2 );

		$settings     = Settings::get_instance();
		$rewrite_slug = $settings->get( 'groups_url' );

		$labels = array(
			'name'                     => __( 'Groups', 'gatherpress-groups' ),
			'singular_name'            => __( 'Group', 'gatherpress-groups' ),
			'add_new'                  => __( 'Add New', 'gatherpress-groups' ),
			'add_new_item'             => __( 'Add New Group', 'gatherpress-groups' ),
			'edit_item'                => __( 'Edit Group', 'gatherpress-groups' ),
			'new_item'                 => __( 'New Group', 'gatherpress-groups' ),
			'view_item'                => __( 'View Group', 'gatherpress-groups' ),
			'view_items'               => __( 'View Groups', 'gatherpress-groups' ),
			'search_items'             => __( 'Search Groups', 'gatherpress-groups' ),
			'not_found'                => __( 'No groups found', 'gatherpress-groups' ),
			'not_found_in_trash'       => __( 'No groups found in Trash', 'gatherpress-groups' ),
			'parent_item_colon'        => __( 'Parent Group:', 'gatherpress-groups' ),
			// 'all_items'                => __( 'All Groups', 'gatherpress-groups' ),
			'all_items'                => __( 'Groups', 'gatherpress-groups' ),
			'archives'                 => __( 'Group Archives', 'gatherpress-groups' ),
			'attributes'               => __( 'Group Attributes', 'gatherpress-groups' ),
			'insert_into_item'         => __( 'Insert into group', 'gatherpress-groups' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this group', 'gatherpress-groups' ),
			'featured_image'           => __( 'Group Poster', 'gatherpress-groups' ),
			'set_featured_image'       => __( 'Set group poster', 'gatherpress-groups' ),
			'remove_featured_image'    => __( 'Remove group poster', 'gatherpress-groups' ),
			'use_featured_image'       => __( 'Use as group poster', 'gatherpress-groups' ),
			'menu_name'                => __( 'Groups', 'gatherpress-groups' ),
			'filter_items_list'        => __( 'Filter groups list', 'gatherpress-groups' ),
			'filter_by_date'           => __( 'Filter groups by date', 'gatherpress-groups' ),
			'items_list_navigation'    => __( 'Groups list navigation', 'gatherpress-groups' ),
			'items_list'               => __( 'Groups list', 'gatherpress-groups' ),
			'item_published'           => __( 'Group published.', 'gatherpress-groups' ),
			'item_published_privately' => __( 'Group published privately.', 'gatherpress-groups' ),
			'item_reverted_to_draft'   => __( 'Group reverted to draft.', 'gatherpress-groups' ),
			'item_trashed'             => __( 'Group moved to Trash.', 'gatherpress-groups' ),
			'item_scheduled'           => __( 'Group scheduled.', 'gatherpress-groups' ),
			'item_updated'             => __( 'Group updated.', 'gatherpress-groups' ),
			'item_link'                => __( 'Group Link', 'gatherpress-groups' ),
			'item_link_description'    => __( 'A link to a group.', 'gatherpress-groups' ),
		);

		\register_post_type(
			self::POST_TYPE_NAME,
			array(
				'labels'       => $labels,
				'supports'     => array(
					'title',
					'editor',
					'thumbnail',
					'excerpt',
					'custom-fields',
					'revisions',
					'gatherpress-venue-information', // @see
					// 'gatherpress-shadow-source', // @see https://github.com/GatherPress/gatherpress/tree/develop/docs/developer/post-type-supports#gatherpress-shadow-source
					'gatherpress-relations-from', // @see
				),
				'public'       => true,
				'show_in_menu' => 'edit.php?post_type=gatherpress_event',
				'show_in_rest' => true, // This in combination with  'supports' => array('editor') enables the Gutenberg editor.
				'hierarchical' => true, // (Note from Subsites plugin: Important for rewriting to work with 'parent' PT.)
				'description'  => '',
				'menu_icon'    => 'dashicons-id',

				'rewrite'      => [
					'slug'       => $rewrite_slug,
					'with_front' => false,      // Defaults to true.
					// 'feeds'   => false,      // Defaults to 'has_archive'.
					// 'pages'   => false,      // Defaults to true.
					// 'ep_mask' => 'EP_NONE',  // Defaults to EP_PERMALINK.

				],

				'has_archive'  => true,
				'can_export'   => true,
			)
		);
	}

	/**
	 * Register the custom post type for groups.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_post_tax_relations(): void {
		\register_taxonomy_for_object_type( self::TAXONOMY_NAME, 'gatherpress_event' );
		\register_taxonomy_for_object_type( self::TAXONOMY_NAME, 'gatherpress_play' );
	}


	/**
	 * Adds a sub-page for "Theater" to the existing sub-pages array.
	 *
	 * This function modifies the provided sub-pages array to include a new sub-page
	 * for GatherPress Theater with specified details such as name, priority, and sections.
	 *
	 * @param array $sub_pages An associative array of existing sub-pages.
	 * @return array Modified array of sub-pages including the new GatherPress Theater sub-page.
	 */
	public function setup_sub_page( array $sub_pages ): array {
		$current_sub_pages = $sub_pages['theater']['sections'] ?? array();
		$sub_pages['theater'] = array(
			'name'     => __( 'Theater', 'gatherpress-groups' ),
			'priority' => 10,
			'sections' => array_merge( $current_sub_pages, array(
				'group_urls' => array(
					'name'        => __( 'Permalinks', 'gatherpress' ),
					'description' => __( 'Change permalink bases.', 'gatherpress' ),
					'options'     => array(
						'groups_url' => array(
							'labels' => array(
								'name' => __( 'Groups', 'gatherpress-groups' ),
							),
							'field'  => array(
								'type'    => 'text',
								'rewrite' => true,
								'options' => array(
									'label'   => __( 'Permalink base of Groups.', 'gatherpress-groups' ),
									'default' => $this->get_localized_post_type_slug(),
								),
								'preview' => array(
									'template' => 'url-rewrite-preview',
									'suffix'   => _x(
										'sample-group',
										'URL permalink structure example for groups',
										'gatherpress-groups'
									),
								),
							),
						),
					),
				),
			) ),
		);

		return $sub_pages;
	}

}
