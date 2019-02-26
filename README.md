# mercury_fulltext

*This plugin was forked from [this repository](https://github.com/HenryQW/mercury_fulltext) and modified to use the Mercury command line application to retrieve the full text of articles instead of an API.*

A Tiny Tiny RSS plugin written for [tt-rss](https://tt-rss.org) as a replacement for the grandpa-old plugin [af_readability](https://git.tt-rss.org/fox/tt-rss/src/master/plugins/af_readability), which doesn't work well for many RSS sites I subscribe to.

It utilizes [postlight/mercury-parser](https://github.com/postlight/mercury-parser) to extract the full content for feeds.

**Some feeds may not render properly, if Mercury can't handle it.** Eg. BBC video-only feeds.

## Installation

1. Clone the repo into your tt-rss **plugins** folder.
1. Install NodeJS and NPM  
    `sudo apt install nodejs npm`
1. Install Yarn  
    `sudo npm i -g yarn`
1. Install Mercury Parser  
    `yarn global add @postlight/mercury-parser`

## Configuration

The configuration is identical to af_readability.

1. Enable the plugin *mercury_fulltext* in **Preferences/Plugins**.
1. Configure for feeds under **Plugins** tab of the **Edit Feed** window (you can right click your feed to get there).

## References

* The plugin is forked from [HenryQW's mercury_fulltext](https://github.com/HenryQW/mercury_fulltext).
* [postlight/mercury-parser](https://github.com/postlight/mercury-parser).
