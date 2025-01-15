# Frontify

Integration of Frontify DAM
with the Drupal Media Library, Gutenberg and GraphQL v4.

Difference with v2 and v3:

- Substitutes the Media Library with the Frontify Finder
- Generates a thubmnail for the Media entity on Media creation
- When inserting with the Media Library, optionally deduplicate media entities
- Isolates mime type groups for Frontify assets, just as Drupal does (Frontify Image, Frontify Document, Frontify Video)
- Moves the alt text in a generic json field with other metadata, so it's not specific to images
- Adds the Frontify name so it can be used as the media name without using javascript specifics
- Adds the Frontify ID in the custom Frontify asset field so it can be used by other processes to interact with the Frontify API
- Adds an optional PHP GraphQL API wrapper for the Frontify API
- Integrates with Gutenberg Media library
- Integrates with GraphQL v4 directives for responsive images

### Use case for mime type groups

Media types have different metadata fields:
- Images can have alt text, caption, author
- Documents can have only the media name
- Videos can have a description, poster to be overridden

This allows
- more flexibility as a Drupal site builder
- less conditions and stronger typing in the code

### Use case for the PHP GraphQL API wrapper:

Fetch metadata from Frontify to populate metadata in source and Media translations.
This is purely optional, so the module can work without setting any API token.

## Configuration

- Add the Frontify url
- Optionally enable the debug mode

## Work with the API

- Create a new API token in Frontify
- Add the token to the Drupal configuration
- Check `FrontifyApi.php`

## Translations approach

Frontify does not handle translations, so the Frontify field can stay
untranslatable, and translations should be added independently as separate
Drupal fields.

## Frontify documentation

- Developer documentation: https://developer.frontify.com/
- GraphQL API for assets: https://frontify.github.io/graphql-reference/queries/asset
- GraphiQL: https://frontify.github.io/public-api-explorer/example/current-user
- Frontify Finder: https://developer.frontify.com/d/XFPCrGNrXQQM/finder#/general/getting-started

# Roadmap for contribution

- Create other media types than Image (Video, Document)
- Config install and schema
- Upgrade path for the new field type schema
- Adjust the default field widget and field formatter accordingly
- Configure mime type groups on the media provider
- Isolate Gutenberg specifics in a submodule
- Isolate GraphQL specifics in a submodule
- Tests
