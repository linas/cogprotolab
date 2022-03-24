<?php
    error_reporting(0);
    
    $host = urldecode($_POST['host']);
    $port = urldecode((int)$_POST['port']);
    $cmd = urldecode($_POST['cmd']);
    $prompt = str_replace("\\x1b", "", urldecode($_POST['prompt']));
    
    $fp = fsockopen($host, $port, $errno, $errstr, 5);

    if(!$fp){
        echo "telnet connection error: $errno $errstr";

    } else {
        //stream_set_blocking($fp, false);
        stream_set_timeout($fp, 0, 2500);
        
        send($fp, $delay, "scm\n", $prompt);
        
        $cmdarr = explode("\n",$cmd);
        $ret = "[";
        for ($i = 0; $i < count($cmdarr); $i++) {
            $ret .= "\"".str_replace("\n", " ", str_replace("\"", "\\\"", send($fp, $delay, $cmdarr[$i]."\n", $prompt)))."\"";
            if ($i < count($cmdarr) - 1)
                $ret .= ",";
        }
        $ret .= "]";
        
        echo $ret;

        fclose($fp);
    }

    function send($fp, $delay, $command, $prompt) {
        $timeout = 5;
        $starttime = time();

        //write to socket
        if (fwrite($fp, $command) == false) {
            return "telnet command send error";
        }

        $response = "";
        //read socket
        while ((time() - $starttime) < $timeout) {
            while (($ret = fgets($fp)) != false) {
                if ($ret == $prompt) {
                    $response = substr($response, 0, -1);
                    return $response;
                }
                
                $response .= $ret;
            }
        }
        
        return "timed out";
    }
?>