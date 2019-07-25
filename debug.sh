#!/usr/bin/env bash

WP_HOME=/Users/ihsanberahim/Documents/Wordpress_Workspace/wpmu-playground
PLUGIN_NAME=gd-wordpress-supports

touch $WP_HOME/wp-content/plugins/$PLUGIN_NAME
rm -rf $WP_HOME/wp-content/plugins/$PLUGIN_NAME
ln -s $(pwd)/ $WP_HOME/wp-content/plugins/$PLUGIN_NAME
wp plugin activate $PLUGIN_NAME --path=$WP_HOME
echo "Done."