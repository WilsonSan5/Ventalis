<?php
namespace Deployer;

require 'recipe/common.php';

// Config

set('repository', 'https://github.com/WilsonSan5/holi_dream.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('ventalis-wilson.com')
    ->set('remote_user', 'u113884515')
    ->set('deploy_path', '~/ventalis');

// Hooks

after('deploy:failed', 'deploy:unlock');