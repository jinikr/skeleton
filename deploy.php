<?php

require  'recipe/composer.php';
$config = yaml_parse_file(__DIR__.'/env.yml');
function getDeployer($config)
{
    $deployerConfig = [];
    foreach($config['stages'] as $stageName => $stage)
    {
        $serverList = $stage['deploy']['server'];
        unset($stage['deploy']['server']);
        foreach($serverList as $server)
        {
            $deployerConfig[$stageName][] = array_merge(['server' => $server, 'stage' => $stageName],$stage['deploy']);
        }
    }
    return $deployerConfig;
}

$serverList = getDeployer($config);
foreach($serverList as $stage => $server)
{
    foreach($server as $serverNum => $serverInfo)
    {
        $svr = server($stage.'_'.$serverNum ,$serverInfo['server'] ,22)
            ->stage($stage)
            ->env('deploy_path', $serverInfo['deploy_path'])
            ->user($serverInfo['user']);

        if(true === isset($serverInfo['password']))
        {
            $svr->password($serverInfo['password']);
        }
        else if(true === isset($serverInfo['identity_file']))
        {
            $svr->identityFile($serverInfo['identity_file']['public_key'],
                $serverInfo['identity_file']['private_key'],
                $serverInfo['identity_file']['password']);
        }
        else if(true === isset($serverInfo['config_file']))
        {
            $svr->identityFile($serverInfo['config_file']);
        }
        else if(true === isset($serverInfo['pem_file']))
        {
            $svr->identityFile($serverInfo['pem_file']);
        }
    }
}

// Git 저장소를 설정합니다. 서버에 git가 필요합니다.
set('repository' , $config['repository']);
env('branch', $config['branch']);
set('keep_releases', 10);
/**
 * Update project code
 */
task('deploy:update_code', function () {
    $repository = trim(get('repository'));
    $branch     = env('branch');
    $git        = env('bin/git');
    $gitCache   = env('git_cache');
    $depth      = $gitCache ? '' : '--depth 1';

    if (input()->hasOption('tag') && input()->getOption('tag')) {
        $tag = input()->getOption('tag');
    } elseif (input()->hasOption('revision') && input()->getOption('revision')) {
        $revision = input()->getOption('revision');
    }

    $at = '';
    if (!empty($tag)) {
        $at = "-b $tag";
    } elseif (!empty($branch)) {
        $at = "-b $branch";
    }

    $releases = env('releases_list');

    if (!empty($revision)) {
        // To checkout specified revision we need to clone all tree.
        run("$git clone $at --recursive -q $repository {{release_path}} 2>&1");
        run("cd {{release_path}} && $git checkout -f $revision");
    } elseif ($gitCache && isset($releases[1])) {
        try {
            run("$git clone $at --recursive -q --reference {{deploy_path}}/releases/{$releases[1]} --dissociate $repository  {{release_path}} 2>&1");
        } catch (RuntimeException $exc) {
            // If {{deploy_path}}/releases/{$releases[1]} has a failed git clone, is empty, shallow etc, git would throw error and give up. So we're forcing it to act without reference in this situation
            run("$git clone $at --recursive -q $repository {{release_path}} 2>&1");
        }
    } else {
        // if we're using git cache this would be identical to above code in catch - full clone. If not, it would create shallow clone.
        run("$git clone $at $depth --recursive -q $repository {{release_path}} 2>&1");
    }

})->desc('Updating code');

/**
 * Run migrations
 */
task('deploy:run_migrations', function () {
    /*
    run('{{release_path}}/bin/cake migrations migrate');
    run('{{release_path}}/bin/cake orm_cache clear');
    run('{{release_path}}/bin/cake orm_cache build');
    */
})->desc('Run migrations');

task('php-fpm:restart', function () {
    run('/bin/systemctl restart php-fpm.service');//
    run('/bin/systemctl restart nginx.service');//
})->desc('Restart PHP-FPM service');

/**
 * deploy 실패시 생성된 임시파일 제거
 */
task('trash:cleanup', function () {
    return;
    $releases = env('releases_list');
    $current = basename(env('current'));

    foreach($releases as $release)
    {
        if($release > $current)
        {
            run("rm -rf {{deploy_path}}/releases/$release");
        }
    }

    run("cd {{deploy_path}} && if [ -e release ]; then rm release; fi");
    run("cd {{deploy_path}} && if [ -h release ]; then rm release; fi");
})->desc('Cleaning up old releases');

task('deployer:start', function () {
    if(runLocally('if [ -f start.txt ]; then echo "true"; fi')->toBool())
    {
        throw new \RuntimeException('deployer already started');
    }
    runLocally('touch start.txt');
})->desc('Deployer start')
    ->once()
    ->setPrivate();

task('deployer:end', function () {
    runLocally('rm -rf start.txt');
})->desc('Deployer end')
    ->once()
    ->setPrivate();

before('trash:cleanup', 'deployer:start');
before('deploy:prepare', 'trash:cleanup');

after('deploy:vendors', 'deploy:run_migrations');
after('deploy:run_migrations', 'php-fpm:restart');

after('cleanup', 'deployer:end');

