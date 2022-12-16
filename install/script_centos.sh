#!/bin/bash

sudo npm install ../ws/server
sudo npm install -g pm2
sudo cp ../ws/server/config.json ./
sudo rm index.php
sudo pm2 start ../ws/server/server.js
sudo yum install -y coturn
sudo mv /etc/coturn/turnserver.conf /etc/coturn/turnserver.conf.old
sudo cp turnserver.conf /etc/coturn/turnserver.conf
sudo pm2 start turnserver
sudo pm2 startup
sudo pm2 save