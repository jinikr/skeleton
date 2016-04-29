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


function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

    pr(human_filesize(memory_get_peak_usage(false)));
    pr($_SERVER);
});