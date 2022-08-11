### Run on windows
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pac-admin-worker:*
sudo supervisorctl start pac-user-worker:*