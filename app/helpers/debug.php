<?php

{
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;
}

register_shutdown_function(function() {
    global $starttime;
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $starttime);
    pr("This page was created in ".$totaltime." seconds");

    $di = \Phalcon\Di::getDefault();
    if(true === isset($di['profiler']))
    {
        $profiler = $di['profiler'];

        if( $profiler->getProfiles() ) {
            $table   = [];
            foreach ( $profiler->getProfiles() as $key => $profile )
            {
                $table[] = array(
                    'time' => $profile->getTotalElapsedSeconds(),
                    'sql '  => $profile->getSQLStatement(),
                    'bind' => $profile->getSqlVariables()
                );
            }
            pr($table);
        }
    }
    pr(get_required_files());

    pr($_SERVER);
});