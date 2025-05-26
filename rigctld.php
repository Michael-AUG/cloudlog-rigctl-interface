<?php
/**
 * @brief        rigctld API
 * @date         2018-12-03
 * @author       Manawyrm
 * @copyright    MIT-licensed
 *
 */
class rigctldAPI
{
    private $host; 
    private $port;
    private $socket = false; 
    private $fp = false;

    function __construct($host = "127.0.0.1", $port = 4532)
    {
        $this->host = $host; 
        $this->port = $port;

        $this->connect();
    }

    function __destruct()
    {
        if ($this->fp !== false) {
            fclose($this->fp);
        }
    }

    public function connect()
    {
        $this->fp = fsockopen($this->host, $this->port, $errno, $errstr, 5);
        if (!$this->fp) 
            return false; 

        return true;
    }

private function runCommand($command)
{
    if ($this->fp === false) {
        return false;
    }

    if (feof($this->fp)) {
        $this->fp = false;
        return false;
    }

    // Clear any buffered data before sending command
    stream_set_blocking($this->fp, false);
    while (($discard = fgets($this->fp)) !== false) {
        // discard everything
    }
    stream_set_blocking($this->fp, true);

    stream_set_timeout($this->fp, 2);
    fwrite($this->fp, $command . "\n");

    // Read one line response (trimmed)
    $line = trim(fgets($this->fp));

    return $line === false ? false : $line;
}

public function getFrequencyAndMode()
{
    $freq = null;
    $mode = null;

    // Try reading frequency, retry if invalid
    for ($i = 0; $i < 3; $i++) {
        $freqCandidate = $this->runCommand("f");
        if (is_numeric($freqCandidate) && intval($freqCandidate) > 1000000) {
            $freq = $freqCandidate;
            break;
        }
        // wait briefly before retry
        usleep(100000);
    }

    // Try reading mode, retry if invalid
    $validModes = ['CW','USB','LSB','FM','AM','DIGI','RTTY','PKT','CWR','SAM'];
    for ($i = 0; $i < 3; $i++) {
        $modeCandidate = strtoupper($this->runCommand("m"));
        if (in_array($modeCandidate, $validModes)) {
            $mode = $modeCandidate;
            break;
        }
        usleep(100000);
    }

    if ($freq === null || $mode === null) {
        error_log("Could not get valid frequency or mode after retries. freq='$freq' mode='$mode'");
        return [
            "frequency" => null,
            "mode" => null,
        ];
    }

    return [
        "frequency" => $freq,
        "mode" => $mode,
    ];
}

    public function getFrequency()
    {
        return $this->runCommand("f");
    }

    public function getMode()
    {
        $mode = $this->runCommand("m");
        if ($mode === false)
            return false; 

        $mode = explode("\n", $mode); 

        return [
            "mode" => $mode[0],
        ];
    }
}
