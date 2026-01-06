module.exports = {
  apps : [{"name": "laravel-queue-worker",
    "script": "artisan",
    "args": "queue:work --queue=default --tries=3 --sleep=3",
    "interpreter": "php",
    "cwd": "d:\\admin-crud-git\\CRM-Minimal-Carbon",
    "autorestart": true,
    "restart_delay": 5000,
  }]
};