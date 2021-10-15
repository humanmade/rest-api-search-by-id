# REST API Search by ID

This plugin modifies the WordPress REST API [`/wp/v2/search` endpoint](https://developer.wordpress.org/rest-api/reference/search-results/) to support the `include` and `exclude` query parameters used in other collections.

In the default REST API you must know the post type for a resource in order to be able to query it, because API routes are broken out by resource type. However, in WordPress we have many functions which accept only an ID and return a matching post regardless of type. With this plugin active it is possible to query for a specific post object by ID, even if you do not know that record's post type:

```
/wp/v2/search?type=post&include=12345
```

## useResourceById Hook

In addition to the API query parameter changes described above, when this plugin is active there is also a script with the handle `use-resource-by-id` enqueued while you are using the Block Editor. This script declares a [React hook](https://reactjs.org/docs/hooks-intro.html) which wraps the WordPress Block Editor's own [`getEntityRecord` selector](https://developer.wordpress.org/block-editor/reference-guides/data/data-core/#getentityrecord) to let you request a post resource without knowing its post type.

Usage:

```js
// The hook is exposed as a browser global.
const { useResourceById } = window;

const MyComponent = () => {
	// Assuming the post object with ID 2501 is a 'customPostType', these two
	// hook calls will result in the exact same data:
	const result1 = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecord( 'postType', 'post', 2501 );
	} );
	const result2 = useResourceById( 2501 );
};
```

This hook uses the search endpoint with the `include` parameter to determine the `subtype` of the requested record. That subtype is cached in memory once known, and the hook will thereafter return the results of a direct `getEntityRecord` call so that the resource is properly routed through the Block Editor data store.

## Installation

Add this to your site with [Composer](https://getcomposer.org/) by running,

```
composer require humanmade/rest-api-search-by-id
```

The package specifies that this is a WordPress plugin, so depending on how you have your Composer installers configured, this should automatically end up in your plugins directory. You can of course also manually download a zipfile of a release and load it into your site by other means.

## Caveats

At present `include` and `exclude` as implemented in this plugin assume you are querying only for `post` objects. Support may be added later to permit searching across object types.

## Release Process

To release a new version,

- bump the version numbers in `package.json` and `plugin.php` (both the plugin header and the version parameter in the `wp_enqueue_script` call)
- generate the minified file with `npm run build`
- commit the built code
- tag with the desired version number

For example,
```
# (Manually edit package.json and plugin.php to set version 1.2.3)
npm run build
git add js/use-resource-by-id.min.js
git commit -m 'Release v1.2.3'
git tag v1.2.3
git push origin main
git push origin --tags
```

## License

GPL v2 or later
