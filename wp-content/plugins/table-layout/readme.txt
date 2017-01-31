=== Responsive Table Layout ===
Contributors: MaartenM, ivalue
Tags: table, row, column, grid, responsive, flexible, layout, page builder, shortcode, editor, device, mobile, tablet, desktop
Requires at least: 4.0
Tested up to: 4.4.2
Stable tag: 1.5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This WordPress plugin provides an easy and user friendly way to make your site's content more responsive.

== Description ==

It contains an editor that uses responsive rows and columns. For each device (phone, tablet, …) you can choose how many columns should be displayed and how wide they must be. Content can be added in every column.

* available at each post edit screen (for any post type)
* writes content to the main post editor (no [custom fields](https://codex.wordpress.org/Custom_Fields))
* ability to toggle between the responsive layout editor and the default WordPress editor.
* column content is added via a [WordPress editor](https://codex.wordpress.org/Function_Reference/wp_editor)
* makes use of row and column [shortcodes](https://codex.wordpress.org/Shortcode_API)
* available for each [post type](https://codex.wordpress.org/Post_Types)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/table-layout` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

== Screenshots ==

1. editor
2. layout settings
3. background settings
4. general settings
5. content settings
6. responiveness settings
7. front large screen
8. front small screen

== Changelog ==

= 1.5.4 =
Release date: Mar 23th, 2016

* Fix - database update check. user not always notified when plugin related data needed to be updated
* Fix - enabled wpautop for editor content. (conflict with other plugins that depend on it)

= 1.5.3 =
Release date: Mar 22th, 2016

* Enhancement - component source screen: code is automatically selected when screen is opened
* Fix - component source screen: update only applied on empty components
* Enhancement - icon picker: icon size increased
* Enhancement - icon picker: limited width
* Enhancement - added plugin tag 'page builder'

= 1.5.2 =
Release date: Mar 15th, 2016

* Fix - background position was not set
* Fix - component not visible after closing settings page without form submission (editor)
* Fix - component source screen: code not always up-to-date
* Fix - component source screen: code is the code of the component itself (not only the children)
* Enhancement - component source screen: improved interface
* Fix - added component source control for columns
* Fix - removed unused row shortcode attribute 'layout' (was set after submitting settings page)
* Fix - overruled heading margin. use only margin set via settings screen (front-end)
* Enhancement - added body class 'table-layout' when Table Layout shortcodes are used (front-end)
* Enhancement - added wrapper element '.mmtl-wrap' arround Table Layout generated html (front-end)

= 1.5.1 =
Release date: Mar 15th, 2016

* Fix - regular expression error when fetching icon classes
* Fix - updater: also looking for column shortcodes without attributes

= 1.5.0 =
Release date: Mar 14th, 2016

* Enhancement - possibility to add background image for columns
* Fix - editor fullscreen mode exits when lightbox is open and esc key is pressed (only close lightbox)
* Fix - default post editor was visible before editor was loaded
* Enhancement - added heading component
* Enhancement - added textblock component
* Enhancement - added button component
* Enhancement - added icon component
* Enhancement - ability to alter the source of a row component
* Enhancement - updated editor look 

= 1.4.2 =
Release date: Feb 26th, 2016

* Fix - changes lost after updating post (editor could not write to post content editor because view mode was active after updating column content)
* Enhancement - show WordPress ‘content’ editor when debug mode is active

= 1.4.1 =
Release date: Feb 25th, 2016

* Enhancement - changed plugin name ’Table Layout’ to ‘Responsive Table Layout’
* Enhancement - removed ajax call for getting thumbnail url when background image is selected (data already provided in callback)
* Fix - removed fullscreen placeholder when editor is deactivated
* Fix - updated `<h2>` styling for editor content
* Fix - removed focus from ‘Add row’ button when clicked (editor)
* Enhancement - placed decrease and increase column width controls next to each other (editor)
* Fix - removed unnecessary ajax call for background image preview when no background images are set (editor)
* Fix - ajax loader hidden by default (editor)
* Enhancement - added html element div.mmtl-overlay that provides the ability to add a transparant colour above a background image
* Enhancement - applied row/column related html id's and classes to the element itself (div.mmtl-row/div.mmtl-col) instead of appending it to div.mmtl-content 
* enhancement - added debug functionality (editor)
* enhancement - added id property for editor instance

= 1.4.0 =
Release date: Feb 21th, 2016

* Enhancement - used css for component background image preview instead of html element (editor)
* Enhancement - placeholder for column sorting is more precise (editor)
* Enhancement - removed row control for adding row after (editor)
* Enhancement - changed editor content styling
* Enhancement - added margin between columns in same row that are placed under each other (editor)
* Enhancement - possibility to add a row/column before or after a row/column (editor)
* Enhancement - added contributor 'iValue' (readme file)
* Enhancement - possibility to toggle full screen (editor)
* Enhancement - added column controls to change column width (editor)

= 1.3.9 =
Release date: Feb 14th, 2016

* Enhancement - simplified creation of editor setting screens
* Enhancement - removed background color from lightbox close button
* Fix - duplicate css class name for admin body class and settings screen caused unwanted styling issues
* Enhancement - changed editor component html attribute 'data-model' to 'data-id' and added id attribute
* Fix - removed column push and pull in editor preview (caused sorting issues)
* Enhancement - added column meta for push and pull (editor)
* Enhancement - added background image preview in editor
* Enhancement - added html title attribute for icons and component meta data (editor)

= 1.3.8 =
Release date: Feb 9th, 2016

* Fix - 'This plugin is not properly prepared for localization'. removed prefix 'mm-' from main plugin file and textdomain
* Enhancement - removed 'mm-' prefix from 'mm-table-layout' in filenames, script and style handles

Plugin reactivation required after update.

= 1.3.7 =
Release date: Feb 9th, 2016

* Fix - text domain missing for some language related functions 
* Enhancement - Updated translations
* Enhancement - removed borders from screenshots

= 1.3.6 =
Release date: Feb 4th, 2016

* Enhancement - added 'grid' tag (readme file)
* Enhancement - added 'Domain Path' for translations (plugin meta data) 
* Fix - only show editor activation button when post type supports 'editor'
* Fix - editor activation button not visible when adding new post
* Enhancement - minified css files
* Enhancement - removed ‘Plugin URI’. old repository url (plugin meta data)

= 1.3.5 =
Release date: Feb 3th, 2016

* Enhancement - added screenshots
* Enhancement - added contributor in readme file
* Fix - updated readme file: incorrect syntax for displaying screenshots on WordPress plugin page
* Enhancement - changed plugin Name in readme file
* Fix - replaced constant MMTL_TEXTDOMAIN with ‘mm-table-layout’: ‘Do not use variable names for the text domain portion of a gettext function’

= 1.3.4 =
Release date: Feb 3th, 2016

* Enhancement - changed plugin prefix ‘tl’ to a more unique prefix ‘mmtl’
* Enhancement - replaced Fancybox with Featherlight (not licensed under GPL2)
* Enhancement - changed column and row html id and class format: {my-id}-wrap and {my-class}-wrap


*go to [GitHub](https://github.com/mmaarten/mm-table-layout/releases "Releases") for more information about older releases*
