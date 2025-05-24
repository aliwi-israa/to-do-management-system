**notes to check requirements and get the project working
Requirements: 

PHP 8.1.25

Laravel Framework 10.48.29

Composer version 2.8.8

Node.js v22.16.0

npm 10.9.2

Installation:
git clone https://github.com/aliwi-israa/to-do-management-system
cd to-do-management-system
cp .env.example .env
composer install
npm install && npm run dev
php artisan key:generate
php artisan migrate --seed
php artisan livewire:publish

**make sure to use pusher credintials
PUSHER_APP_ID=1997734
PUSHER_APP_KEY=24e54fc303bc42ca78fd
PUSHER_APP_SECRET=0a4eda43b020a3092ca2
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
BROADCAST_DRIVER=pusher






