# cloudlog-rigctl-interface
## Edits
This fork contains changes by Michael GM5AUG, to address issues with the `runCommand("fm"...)` function. The way that rigctl responds to `fm` can lead to values being misassigned to variables, ending up with situations where we send the following to Cloudlog:
```
Frequency: CW - Mode: 500
Frequency: 500 - Mode: 7011000
```
To overcome this, `rigctld.php` now queries the frequency and mode separately, and parses and cleans the data to make sure no false values are passed on to CloudLog.

This makes sure that `7011000` is not passed as a `mode`. It makes sure that:
* Frequency is numerical
* Mode is a string (of pre-set values)
* Neither is empty

Another alteration is in the `rigctlCloudlogInterface.php` file, where I added the `"key" => $cloudlog_apikey` line to the data array. Once adding this, CloudLog accepted the information.

Hopefully these changes are of use to someone!

73 Michael GM5AUG

## Original README
Connects Cloudlog to rigctld / hamlib via PHP.
This allows you to automatically log the used frequency and mode in Cloudlog's Live QSO menu. 

Change your parameters in config.php, 
```
// rigctl-specific configuration 
$rigctl_host = "127.0.0.1";
$rigctl_port = 4532;

// Cloudlog-specific parameters
$cloudlog_url = "https://log.tbspace.de";
$cloudlog_apikey = "p1fgZhGPbWMRaD4Iz5xm";

// displayed in Cloudlogs Live QSO menu
$radio_name = "FT-991a";

// minimum update interval
$interval = 1; 
``` 

If you're on Debian (or Ubuntu/similar), you can install everything that is required with: 
`apt install php-cli php-curl`

Start the software by running `./rigctlCloudlogInterface.php`.
If you've downloaded the software as a .zip file instead of cloning it directly from the Git repository, you might have to make the file executable first. This is done by running
`chmod +x rigctlCloudlogInterface.php`.

If you want to run it in the background without an open terminal window, you can run `screen ./rigctlCloudlogInterface.php`. (this won't work on Windows, sorry!) 

If you prefer tmux, use `tmux new -s rigctlCloudlog ./rigctlCloudlogInterface.php`. 

For more information on how-to setup hamlib/rigctld have a look over at the excellent guide written for pat: https://github.com/la5nta/pat/wiki/Rig-control
