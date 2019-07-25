#!/usr/bin/env bash

WP_HOME=/Users/ihsanberahim/Documents/Wordpress_Workspace/wpmu-playground
PLUGIN_NAME=gd-wordpress-supports

echo "autoload..."
composer dump-autoload -o > /dev/null

echo "remove if exist..."
touch $PLUGIN_NAME.zip
rm -rf $PLUGIN_NAME.zip

echo "clean build"
rm -rf build/debug/*

echo "build..."
zip -r $PLUGIN_NAME.zip . > /dev/null

echo "move into debug..."
mkdir -p build/debug/$PLUGIN_NAME
mv $PLUGIN_NAME.zip build/debug/$PLUGIN_NAME
cd build/debug/$PLUGIN_NAME
unzip -o $PLUGIN_NAME.zip > /dev/null
rm -rf $PLUGIN_NAME.zip

echo "build installable..."
cd ../
touch $PLUGIN_NAME.zip
rm -rf $PLUGIN_NAME.zip
zip -r $PLUGIN_NAME.zip ./$PLUGIN_NAME > /dev/null

echo "install to WP_HOME..."
rm -rf $WP_HOME/wp-content/plugins/$PLUGIN_NAME
wp plugin install $PLUGIN_NAME.zip --force --activate --path=$WP_HOME

echo "Done."