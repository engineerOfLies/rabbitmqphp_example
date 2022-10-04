<?php
/**
    @brief gets the currrent machine information and optionally any other task
    specific INI folder installed in the system path.
    Uses the default path of /var/system_ini/ unless $INIPATH is set
    @return parsed ini machine information
*/
function getHostInfo(array $extra = NULL)
{
    $machine;
    $machine = parse_ini_file("rabbitMQ.ini",true);
    echo '<pre>'; print_r($machine); echo '</pre>';

    return $machine;
}

?>