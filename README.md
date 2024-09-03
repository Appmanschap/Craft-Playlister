<p align="center"><img src="src/icon.svg" width="100" height="100" alt="Youtube Playlist importer" /></p>
<h1 align="center">YouTube Playlist importer for Craft CMS</h1>

YouTube Playlist Importer is a CraftCMS plugin for importing YouTube playlists into Craft. Retrieve the playlists and the corresponding videos.

## Requirements
This plugin requires CraftCMS 5.0.0-beta.2 or later.

## Installation
To install the plugin, either install it from the plugin store, or follow these instructions:

1) Install with composer

```sh
composer require appmanschap/craft-youtube-playlist-importer
```

2) Install the plugin

```sh
craft plugin/install youtube-playlist-importer
```

3) Add your YouTube API token in the settings.

## Usage
You can retrieve playlists & videos either by using [Fields](#fields) the [Template variables](#template-variables). Both scenarios will return a [Playlist element](#playlist-element) or [Video element](#video-element)

## Fields
Create a new Playlist Field or Video Field and add it to your entry type.

## Template variables
### craft.ypi.playlists()
Returns a PlaylistQuery. Query as you like to retrieve the playlists. Example (retrieve enabled playlists): 
```twig
craft.ypi.playlists.enabled(true).all()
```

#### Retrieve a single playlist by the playlist id
```twig
craft.ypi.playlists.playlistId(YOUR-PLAYLIST-ID).one()
```

### craft.ypi.videos()
Returns a VideoQuery. Query as you like to retrieve the videos.

#### Retrieve embeddable videos example
```twig
craft.ypi.videos.embeddable(true).all()
```

#### Retrieve videos from a playlist
```twig
craft.ypi.videos.playlistId(YOUR-PLAYLIST-ID).all()
```

#### Retrieve videos with tags example
```twig
craft.ypi.videos.tags[('awesome', 'video']).all()
```

## Playlist element

### Method: getVideos(?bool embeddable)
Retrieve the videos of the playlist. The embeddable parameter will filter the query by it's value when it's available.

## Video element

### Method: getThumbnail(string size)
The method will return the first possible thumbnail url by it's given size. If no parameter is passed then it will retrieve it's known largest thumbnail url.

Possible sizes:
- default
- medium
- high
- standard
- maxres


## Support
Get in touch by [creating a Github issue](https://github.com/Appmanschap/YouTube-Playlist-Importer/issues)