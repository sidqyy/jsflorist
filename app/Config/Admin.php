<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Admin extends BaseConfig
{
    /**
     * List of admin emails that are allowed to access admin panel
     * 
     * @var array
     */
    public $adminEmails = [
        'admin@jsflorist.com',
        // Add more admin emails here as needed
    ];
}
