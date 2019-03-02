# Add code linting in VS Code

Add javascript linting: 
1. Install the jshint library `sudo npm install -g jshint`
2. Install `jshint` in VS Code extensions

Adding the WordPress Coding Standards WPCS when working in VS Code (on mac)
* Install [https://brew.sh/](Brew)
* Install [https://getcomposer.org/doc/00-intro.md](Composer) with Brew: `brew install composer`
* Add PHPCS: `composer global require squizlabs/php_codesniffer`
* Add the latest WordPress Coding Standard (WPCS)  `composer global require wp-coding-standards/wpcs`
* Install the phpcs extension in VS Code: 'phpcs'
* Right click the wpcs folder in your node_Modules foler, replace /path/to/wpcs and run: `~/.composer/vendor/bin/phpcs --config-set installed_paths /Users/ole/.composer/vendor/wp-coding-standards/wpcs`
* Add the phpcs executable path to vs code settings
* Add this line to your VS Code settings: "phpcbf.standard": "WordPress"
