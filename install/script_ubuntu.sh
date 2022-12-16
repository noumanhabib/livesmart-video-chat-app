#!/bin/bash

sudo npm install ../ws/server
sudo npm install -g pm2
sudo cp ../ws/server/config.json ./
sudo rm index.php
sudo pm2 start ../ws/server/server.js
sudo apt-get -y update
sudo apt-get -y install coturn
sudo systemctl stop coturn
sudo mv /etc/turnserver.conf /etc/turnserver.conf.old
sudo cp turnserver.conf /etc/turnserver.conf
sudo pm2 start turnserver
sudo pm2 startup
sudo pm2 save