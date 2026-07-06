# GatherPress Groups

**Contributors:** carstenbach  
**Tags:** theater, groups, gatherpress  
**Tested up to:** 6.9  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

[![Playground Demo Link](https://img.shields.io/badge/WordPress_Playground-blue?logo=wordpress&logoColor=%23fff&labelColor=%233858e9&color=%233858e9)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/carstingaxion/gatherpress-groups/main/.wordpress-org/blueprints/blueprint.json) [![Build, test & measure](https://github.com/carstingaxion/gatherpress-groups/actions/workflows/build-test-measure.yml/badge.svg?branch=main)](https://github.com/carstingaxion/gatherpress-groups/actions/workflows/build-test-measure.yml)


## Description

GatherPress Groups extends [GatherPress](https://gatherpress.org/) with a dedicated **Group** content type for ensembles, companies, or any persistent collective. A group is a long-lived entity rather than a time-bound event: it carries venue-style metadata and can receive inbound person–role assignments from the [GatherPress Relations](https://github.com/carstingaxion/gatherpress-relations) plugin.

### What it does

**Group post type (`gatherpress_group`)**  
Registers a public, block-editor-enabled post type called *Group* (hierarchical, with archive, configurable permalink base). It declares `gatherpress-venue-information` support — giving it venue-style fields — and `gatherpress-relations-from` support, which tells the GatherPress Relations plugin to wire the `_gatherpress_relations` junction meta and any source shadow taxonomies (e.g. `_gatherpress_person`) onto this post type. The featured image is labelled *Group Poster* throughout the UI. The post type appears under the GatherPress Events admin menu.

**Shadow taxonomy linking groups to events and productions**  
A private shadow taxonomy (`_gatherpress_group`) is registered on `gatherpress_event` and `gatherpress_play`. GatherPress's shadow-taxonomy mechanism uses it to connect individual events and productions to the group they belong to. The taxonomy is exposed in the editor metabox and quick-edit panel.

**Block variation: Group Details**  
Registers a JavaScript block variation of `gatherpress/venue` named after the singular post-type label. The variation sets `sourcePostType` to `gatherpress_group` and ships linked post title and linked featured image as default inner blocks, giving editors a ready-made Group context block in the inserter.

**Settings sub-page**  
Adds a *Theater* section under the GatherPress settings screen (merging with sections from any active companion plugins) with a *Permalinks* option to customise the URL base for the Groups archive. Defaults to the translated word for “Group”.

**Starter block pattern**  
Registers a `gatherpress-groups/starter` block pattern scoped to the Group post type, giving authors an optional starting layout when creating a new group.

## Requirements

- WordPress 6.4 or later
- PHP 7.4 or later
- [GatherPress](https://gatherpress.org/) 0.34.0-alpha-2 or later

## Installation

1. Upload the plugin files to `/wp-content/plugins/gatherpress-groups`.
2. Activate the plugin via the **Plugins** screen.


## Frequently Asked Questions

### Does this work without GatherPress?

No.

## Changelog

All notable changes to this project will be documented in the [CHANGELOG.md](CHANGELOG.md).

## License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
