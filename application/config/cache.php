<?php

/**
 * Use cache
 */
$config['use_cache'] = false;

/**
 * Use memcached
 */
$config['use_memcached'] = false;

/**
 * Cache prefix
 */
$config['cache_prefix'] = null;

/**
 * Memcached servers
 */
$config['memcached_servers'] = array(
   array(
      'host'             => '127.0.0.1',
      'port'             => 11211,
      'persistent'       => true,
      'weight'           => 1,
      'timeout'          => 5,
      'retry_interval'   => 15,
      'status'           => true,
      'failure_callback' => null
   )
);

/**
 * Plugins cache config
 */
if ($config['use_memcached']) {
   $config['plugins_backend_engine'] = 'Memcached';
   $config['plugins_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['plugins_backend_engine'] = 'File';
   $config['plugins_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/plugins_config',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['plugins_frontend_engine'] = 'Core';
$config['plugins_frontend'] = array(
   'lifetime'                  => 86400,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Database cache config
 */
if ($config['use_memcached']) {
   $config['dbschema_backend_engine'] = 'Memcached';
   $config['dbschema_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['dbschema_backend_engine'] = 'File';
   $config['dbschema_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/dbschema',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['dbschema_frontend_engine'] = 'Core';
$config['dbschema_frontend'] = array(
   'lifetime'                  => 86400,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Show contents cache config
 */
if ($config['use_memcached']) {
   $config['show_contents_backend_engine'] = 'Memcached';
   $config['show_contents_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['show_contents_backend_engine'] = 'File';
   $config['show_contents_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/show_contents',
      'hashed_directory_level' => 3,
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666,
      'read_control'           => false
   );
}
$config['show_contents_frontend_engine'] = 'Core';
$config['show_contents_frontend'] = array(
   'lifetime'                  => 60,
   'automatic_cleaning_factor' => 3,
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Display contents cache config
 */
if ($config['use_memcached']) {
   $config['display_contents_backend_engine'] = 'Memcached';
   $config['display_contents_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['display_contents_backend_engine'] = 'File';
   $config['display_contents_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/display_contents',
      'hashed_directory_level' => 3,
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666,
      'read_control'           => false
   );
}
$config['display_contents_frontend_engine'] = 'Core';
$config['display_contents_frontend'] = array(
   'lifetime'                  => 60,
   'automatic_cleaning_factor' => 3,
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * NSCK cache config
 */
if ($config['use_memcached']) {
   $config['nsck_backend_engine'] = 'Memcached';
   $config['nsck_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['nsck_backend_engine'] = 'File';
   $config['nsck_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/nsck',
      'hashed_directory_level' => 3,
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666,
      'read_control'           => false
   );
}
$config['nsck_frontend_engine'] = 'Core';
$config['nsck_frontend'] = array(
   'lifetime'                  => 3,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 50,
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Palettes cache config
 */
if ($config['use_memcached']) {
   $config['palettes_backend_engine'] = 'Memcached';
   $config['palettes_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['palettes_backend_engine'] = 'File';
   $config['palettes_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/palettes',
      'hashed_directory_level' => 3,
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['palettes_frontend_engine'] = 'Core';
$config['palettes_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Dimensions cache config
 */
if ($config['use_memcached']) {
   $config['dimensions_backend_engine'] = 'Memcached';
   $config['dimensions_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['dimensions_backend_engine'] = 'File';
   $config['dimensions_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/dictionaries',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['dimensions_frontend_engine'] = 'Core';
$config['dimensions_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Browsers cache config
 */
if ($config['use_memcached']) {
   $config['browsers_backend_engine'] = 'Memcached';
   $config['browsers_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['browsers_backend_engine'] = 'File';
   $config['browsers_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/dictionaries',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['browsers_frontend_engine'] = 'Core';
$config['browsers_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Operation systems cache config
 */
if ($config['use_memcached']) {
   $config['os_backend_engine'] = 'Memcached';
   $config['os_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['os_backend_engine'] = 'File';
   $config['os_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/dictionaries',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['os_frontend_engine'] = 'Core';
$config['os_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Languages cache config
 */
if ($config['use_memcached']) {
   $config['languages_backend_engine'] = 'Memcached';
   $config['languages_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['languages_backend_engine'] = 'File';
   $config['languages_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/dictionaries',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['languages_frontend_engine'] = 'Core';
$config['languages_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Countries cache config
 */
if ($config['use_memcached']) {
   $config['countries_backend_engine'] = 'Memcached';
   $config['countries_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['countries_backend_engine'] = 'File';
   $config['countries_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/dictionaries',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['countries_frontend_engine'] = 'Core';
$config['countries_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Fraud protections cache config
 */
if ($config['use_memcached']) {
   $config['protections_backend_engine'] = 'Memcached';
   $config['protections_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['protections_backend_engine'] = 'File';
   $config['protections_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/dictionaries',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['protections_frontend_engine'] = 'Core';
$config['protections_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Fraud protection settings cache config
 */
if ($config['use_memcached']) {
   $config['protection_settings_backend_engine'] = 'Memcached';
   $config['protection_settings_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['protection_settings_backend_engine'] = 'File';
   $config['protection_settings_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/dictionaries',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['protection_settings_frontend_engine'] = 'Core';
$config['protection_settings_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Sites cache config
 */
if ($config['use_memcached']) {
   $config['sites_backend_engine'] = 'Memcached';
   $config['sites_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['sites_backend_engine'] = 'File';
   $config['sites_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/sites',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['sites_frontend_engine'] = 'Core';
$config['sites_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Channels cache config
 */
if ($config['use_memcached']) {
   $config['channels_backend_engine'] = 'Memcached';
   $config['channels_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['channels_backend_engine'] = 'File';
   $config['channels_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/channels',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['channels_frontend_engine'] = 'Core';
$config['channels_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Feeds cache config
 */
if ($config['use_memcached']) {
   $config['feeds_backend_engine'] = 'Memcached';
   $config['feeds_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['feeds_backend_engine'] = 'File';
   $config['feeds_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/feeds',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['feeds_frontend_engine'] = 'Core';
$config['feeds_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Locales cache config
 */
if ($config['use_memcached']) {
   $config['locales_backend_engine'] = 'Memcached';
   $config['locales_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['locales_backend_engine'] = 'File';
   $config['locales_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/i18n',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['locales_frontend_engine'] = 'Core';
$config['locales_frontend'] = array(
   'lifetime'                  => 86400,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Click protection cache
 */
if ($config['use_memcached']) {
   $config['protection_click_backend_engine'] = 'Memcached';
   $config['protection_click_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['protection_click_backend_engine'] = 'File';
   $config['protection_click_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/clicks',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['protection_click_frontend_engine'] = 'Core';
$config['protection_click_frontend'] = array(
   'lifetime'                  => 600,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * CPA approved traffic sources cache
 */
if ($config['use_memcached']) {
   $config['cpa_ats_backend_engine'] = 'Memcached';
   $config['cpa_ats_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['cpa_ats_backend_engine'] = 'File';
   $config['cpa_ats_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/cpa/ats',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['cpa_ats_frontend_engine'] = 'Core';
$config['cpa_ats_frontend'] = array(
   'lifetime'                  => 300,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);

/**
 * Product logo images cach
 */
if ($config['use_memcached']) {
   $config['cpa_product_img_backend_engine'] = 'Memcached';
   $config['cpa_product_img_backend'] = array(
      'servers'       => $config['memcached_servers']
   );
} else {
   $config['cpa_product_img_backend_engine'] = 'File';
   $config['cpa_product_img_backend'] = array(
      'cache_dir'              => APPPATH . 'cache/cpa/product_img',
      'hashed_directory_umask' => 0777,
      'cache_file_umask'       => 0666
   );
}
$config['cpa_product_img_frontend_engine'] = 'Core';
$config['cpa_product_img_frontend'] = array(
   'lifetime'                  => 86400,
   'automatic_serialization'   => true,
   'automatic_cleaning_factor' => 3,
   'caching'                   => $config['use_cache'],
   'cache_id_prefix'           => $config['cache_prefix']
);