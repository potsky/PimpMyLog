[![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/)

Jekyll source for the *gh-pages* branch.

## Install

- [Ruby](http://www.ruby-lang.org/)
- [Node.js](http://nodejs.org/)
- [Grunt](http://gruntjs.com/) (`npm install -g grunt-cli`)
- [Bower](http://bower.io/) (`npm install -g bower`)
- [Jekyll](http://jekyllrb.com) (`gem install jekyll`)

Clone this repo then install dependencies:

```bash
$ bundle install
$ npm install
$ bower install

??? npm install git+http://github.com/jbakse/grunt-cropthumb.git --save-dev ???
$port install GraphicsMagick
```

## Development

In 2 terminals, run at the same time :

- `grunt` to watch for file modification and live rebuild
- `grunt server` to launch Jekyll in dev mode with watch for file modification and live rebuild

Here is the structure :

- `_js` : my javascripts
- `_css` : my css and less files
- `assets` : the static assets directory
- `upload` : assets for posts (thumbnails are computed on grunt processes)

Install new css and js tools with `bower install ... -S` and do not modify original files in `bower-components` !

## Build

In a terminal, run :

- `grunt build` to build the website in production env
- `grunt server-prod` to launch the web server


## Install

In a terminal, run :

- `grunt install` to build the site and copy files from the *jekyll* branch to the *gh-pages*

The install process can be called remotely on an pre-configured server with PHP servlets located in the `_tools` directory
