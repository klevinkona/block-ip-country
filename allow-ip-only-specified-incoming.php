<?php
/*
This 2 rules need to be added at the end, and after added also your ip, otherwise you will get locked out
iptables -A INPUT -i lo -j ACCEPT
iptables -P INPUT DROP
*/


// Name of the country you intend to block
$country_name = "austria";

// Please visit  the ( http://www.ipdeny.com/ipblocks/ ) and copy the link ( aggregated zone file ) of the country you intend to block the access
$link_country = 'http://www.ipdeny.com/ipblocks/data/aggregated/ad-aggregated.zone';

//Define variables 
$country_file = $country_name . '.txt';
$temp_country = $country_name . "_1.txt";
$file_log = 'logs.txt';
$today = date("F j, Y, g:i a");



if (file_exists($country_file)) {
	exec("wget -O  $temp_country $link_country");
	exec("diff $country_file $temp_country >> newips.txt");

	exec("cat newips.txt | grep / >> filtered_ips.txt");
	exec("rm -rf  $country_file && mv  $temp_country $country_file");	

	$file2 = fopen("filtered_ips.txt","r");


	while(! feof($file2))
	{

		$data2 = fgets($file2);

		if ($data2 != null) {

			//rtrim($data2, "<");
			$string = str_replace(array('>', '<' ), '', $data2);
			$final = rtrim($string, "\n\r") ;
			//echo 'iptables -A OUTPUT -d ' . $final . " -j DROP" . "\n";
			exec("iptables -A INPUT -d $final   -j ACCEPT");


		}
	}

	fclose($file2);


	exec("service iptables save"); 

	exec("> filtered_ips.txt && > newips.txt");


	$data_loged_writed = $country_name . ' - '  . $today . "\n";
	file_put_contents($file_log, $data_loged_writed, FILE_APPEND | LOCK_EX);


} else {

	exec("wget -O  $country_file $link_country");

	$file = fopen("$country_file","r");

	while(! feof($file))
	{
		$data = fgets($file);

		if ($data != null) {
			$final2 = rtrim($data, "\n\r");
			//echo 'iptables -A OUTPUT -d ' .  $final2 . " -j DROP" . "\n";
			exec("iptables -A INPUT -d $final2   -j ACCEPT");
		}
	}

	fclose($file);




	// This is part is to save iptables so it will not lose when reboot, please comment the section that you do not need

     /******** Centos *********/
   	//exec("service iptables save"); 

	/******** OpenSuse *********/
	/* At "/etc/init.d/after.local"  
	add at the bottom of the file this
	 "iptables-restore < /etc/iptables.conf" 
	 so the iptables rules will be restored in startup*/
	exec("iptables-save > /etc/iptables.conf"); 
	 



    //Write logs at logs.txt every time a country script it is executed
	$data_loged_writed = $country_name . ' - '  . $today . "\n";
	file_put_contents($file_log, $data_loged_writed, FILE_APPEND | LOCK_EX);

}?> 
