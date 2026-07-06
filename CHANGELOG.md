# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased](https://github.com/carstingaxion/gatherpress-groups/compare/0.1.0...HEAD)

## [0.1.0](https://github.com/carstingaxion/gatherpress-groups/compare/0.1.0...0.1.0) - 2026-07-06

- Initial release
- Registers `gatherpress_group` as a public, hierarchical, block-editor-enabled post type with archive and configurable permalink base
- Declares `gatherpress-venue-information` post type support, giving Group posts venue-style fields instead of an event date
- Declares `gatherpress-relations-from` post type support, enabling [GatherPress Relations](https://github.com/carstingaxion/gatherpress-relations) to wire junction meta and source shadow taxonomies (e.g. `_gatherpress_person`) onto Group posts
- Registers shadow taxonomy `_gatherpress_group` on `gatherpress_event` and `gatherpress_play`, linking events and productions to the group they belong to
- Exposes the shadow taxonomy in the block editor metabox and quick-edit panel
- Registers a `gatherpress/venue` block variation scoped to `gatherpress_group`, with linked post title and linked featured image as default inner blocks
- Adds a *Theater* > *Permalinks* settings section under the GatherPress admin screen with a configurable URL base for the Groups archive
- Registers a `gatherpress-groups/starter` block pattern scoped to the Group post type
