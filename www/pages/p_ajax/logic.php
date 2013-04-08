<?php
/**
 *   ____             ____        ___                  _
 *  / ___|___  _ __  / _\ \      / (_)______ _ _ __ __| |
 * | |   / _ \| '_ \| |_ \ \ /\ / /| |_  / _` | '__/ _` |
 * | |__| (_) | | | |  _| \ V  V / | |/ / (_| | | | (_| |
 *  \____\___/|_| |_|_|    \_/\_/  |_/___\__,_|_|  \__,_|
 *
 * This file is part of the Confwizard project.
 * Copyright (c) 2013 Patrick Wieschollek
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://integralstudio.net/license.txt
 *
 * @package    ConfWizard
 * @copyright  2013 (c) Patrick Wieschollek
 * @author     Patrick Wieschollek <wieschoo@gmail.com>
 * @link       http://www.integralstudio.net/
 * @license    GPLv3 http://integralstudio.net/license.txt
 *
 */

class P_ajax extends page{

	public function preProcessor(){
		header("Content-Type:text/plain");
	}
	public function PostProcessor($src)
	{
		echo $src;exit;
	}


	public function DirectoryAction()
	{
		$term = Request::Get('term') ;
		if($term[0] != '/'){
			return json_encode(array());
		}else{


			$prepare = explode('/',$term);

			if(strlen($prepare[count($prepare)-1]) == 0){
				// list all directories
				// /dir1/dir2/   --> /dir1/dir2
				unset($prepare[count($prepare)-1]);
				$pattern = '';
				$term = implode('/',$prepare);

			}else{
				// list only directories mathcin pattern
				$pattern = $prepare[count($prepare)-1];
				unset($prepare[count($prepare)-1]);
				$term = implode('/',$prepare);
			}

			global $config ;
			global $Enviroment;
			$start_in = $Enviroment['webserver']['data']['customer_homedirectories']. '/' . USER::GetName() . '/public' ;
			#$start_in = 'D:\xampp\htdocs\Virtual\var\webs\web1000\public' ;
			$PathFilter = new PathValueFilter($start_in);


			if($PathFilter->Validate($term)){
				$result = array() ;
				$iterator = new DirectoryIterator($start_in.$term) ;
				foreach ($iterator as $fileinfo)
				{
					if ($fileinfo->isDir() && !$fileinfo->isDot())
					{
						if (strtolower(substr($fileinfo->getFilename(), 0, strlen($pattern))) === strtolower($pattern))
						{

							$r = '' . $term . '/' . $fileinfo->getFilename() ;
							$result[] = array('id'=>$r,'value'=>$r,'label'=>$r);

						}

					}
				}
				return json_encode($result);
			}else{
				return json_encode(array());
			}
		}


	}
	public function crtAction(){
		return '-----DEMO BEGIN CERTIFICATE-----
MIIDizCCAnOgDEMOAgIIUWA4eAADagoDEMOJKoZIhvcNAQEFBQAwbTELMAkGA1UE
BhMCRFoxCzAJBgNVBAgTAmZlMQwwCgYDVQQHEwNmZWYxDDAKBgNVBAoTA3NkZjEL
MAkGA1UECxMCZmYxDTALBgNVBAMTBHNDEMOxGTAXBgkqhkiG9w0BCDEMOnNkZmRA
amguZGUwHhcDEMOwDEMOMTUwMDDEMOcNMTQwNDA2MTUwMDA4WjBtMQswCQYDVQQG
EwJEWjELMAkGA1UECBMCZmUxDDAKBgNVBAcTA2ZlZjEMMAoGA1UEChMDc2RmMQsw
CQYDVQQLEwJmZjENDEMOA1UEAxMEc2RmYTEZMDEMOSqGSIb3DQEJARYKc2RmZEBq
aC5kZTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAK1RjYKc4DEMO3M
DJzl9D919tbmxkRS6tuyzDEMO7AYnAhsfhdQbSshAU65Mycw4tAg3ob6ITbojyZb
K8/JFemo7vQGaI0VjIVLAUSznCJuOHQlHprXDEMOwPb1ikSg6YDY9cu8RGXbR+cf
uLb+0f3TBRELDEMOfCA1YS7mH1YHTk/kHDzrli1ND9YyJXb8DEM4mtEYftjzuzH
fkCV58LOLzIt6cVMifl+h+cnOJvDEMO7bcPOgLqcKEvG0M4mirM07ObdGwVFBgYN
mARSi5dg/sR5/ADFW5lrmyeLkPBohnZaXUW+uirmFMuqVj3+9q7/Q2DycWczSvgw
J80JrQsCAwEAAaMvMC0wDAYDEMOTAQH/BAIwADAdBgNVHSUEFjAUBggrBgEFBQcD
AQYIKwYBBQUHAwIwDQYJKoZIhvcNAQEFBQADggEBADbAu7J7Rjpjs826xMu1LxRp
4LTMpM3V18eqyqW8TLtaVkYz0BjDEMOIjTC9cOparqHnMVKC82JDEMOs82ZjDPVQ
InhoK1vo1SiBeO7BYeZIlwGrdHHh7rt0wm0B2kuqBXM71HS9H2YjW2iSFKKyUibA
dsHe8MVQdgMCMMUyPzWoAv3PLBVmwO0uuVgeaT/HzwJjR3BjGrWnlbMczOjRBXNn
9hsXoiyvZrUXangIiFFVhU6NjxqNhMniweREvp/ZmK5uwSwe8YEAqDLDEMOO5q7G
o3LChUOInUEpyLVORZOyP7GJWMU2yMz0qudg04MUI0f32V5obnxMI/wV2OHKOjA=
-----END CERTIFICATE-----';
	}
	public function csrAction(){

		if(!defined("_LIVE_")){
			return '-----DEMO CERTIFICATE REQUEST-----
MIIC9DCCAdwCAQAwga4xCzAJBgNVBAYTAlVLMREwDwYDVQQIEwhTDEMOcnNldDEU
MBIGA1UEBxMLDEMOc3RvbmJ1cnkDEMOdBgNVBAoTFlRoZSBCcmFpbiBSb29tIExp
bWl0ZWQxHzAdBgNVDEMOFlBIUCBEb2N1bWVudGF0aW9uIFRlDEMOFDASBgNVBAMT
C1dleiBGDEMOb25nMR4wHAYJDEMOhvcNDEMO93ZXpAZXhhbXBsZS5jb20wggEi
MA0GCSqGSIb3DQEBAQUADEMODwAwggEDEMOBAQDhhVIyuNjOirDEMOdoJaw5IzXt
T5Aeh5GBZfmFIeIhHYSwBDEMOog9OxtpKPn9D3BhRoA4KY2meRMjXpBxFT
VICg7LuzKQzWRr5tHw2oTdL2W6EdFPj3klNE+HqBfBIDb2/uWS+ugNn16m1116xJ
qr5ToGLc18w6NuBxPG0szxRUDEMON1/KBXsDYNgm8GQ1dQPMIDEMO7Ub6LOWeSab
FseZgJDjdiw7EA9BNojHLpMo6q9RDEMOpT+pxUTq5v+FlxsZ1TSDcAJsSEdnY9An
Th/SLJik7kCVhUdXV5X0g4jsCh3HryxlDEMO7t3vmmjifNDEMODkO/pGpI2jAgMB
AAGgADANBgkqhkiG9w0BAQQFAAOCAQEAH30LDEMOvOMneWkYdiqzvFHgAGflenHY
X7YATbb6ltOWAFtDEMOqy/tYYadTopeFv+Uizz8yYKDEMO+I1aYs9L1yTf5FK2LK
+7iUPpz5OoCQVkd1JTp/D2k89YMF2+p1PCkp6aoCgSMFTR7gZoImlHPl0Vgex4YN
/i3y/JIkbyl5b4LxghltDEMOFWu4Sx7/kQX/+pAJHdMtIxXXg2uuc4srL4AKMaMQ
rTSFc184a/hujKtaFyKGQllQ61HLHLN8nm+7o5u1TgxEqLhMO+nGYPZYf/NrtYlX
eeyAJZkqa5BWwRi8D1SPBczoRZUp/L9OT66/hJLmfVBxpBCKrxYv/w==
-----END CERTIFICATE REQUEST-----';
		}


		$dn = array(
		    "countryName" => Request::Get('commonName'),
		    "stateOrProvinceName" => Request::Get('commonName'),
		    "localityName" => Request::Get('commonName'),
		    "organizationName" => Request::Get('commonName'),
		    "organizationalUnitName" => Request::Get('organizationalUnitName'),
		    "commonName" => Request::Get('commonName'),
		    "emailAddress" => Request::Get('emailAddress')
		);
		$dn = array(
    "countryName" => "UK",
    "stateOrProvinceName" => "Somerset",
    "localityName" => "Glastonbury",
    "organizationName" => "The Brain Room Limited",
    "organizationalUnitName" => "PHP Documentation Team",
    "commonName" => "Wez Furlong",
    "emailAddress" => "wez@example.com"
);
		// Erzeugen eines neuen privaten (und öffentlichen) Schlüsselpaars
		#$privkey = openssl_pkey_new();
		$privkey = trim($_POST['keytext_generated']);


		$name = '';
		do{
			$name = md5(time().rand()).'.pem';
		}while(file_exists('temp/'.$name));

		$passphrase="";
		$priv_key_file_name = ("./temp/".$name);
		file_put_contents('temp/'.$name,$privkey);

		$privkey = openssl_pkey_get_private ( array("file://$priv_key_file_name", $passphrase) );



		// Erzeugen einer Zertifikatssignierungsanfrage
		$csr = openssl_csr_new($dn, $privkey);

		/*
		// Anzeigen der möglichen aufgetretenen Fehler
		while (($e = openssl_error_string()) !== false) {
			echo $e . "\n";
		}*/

		openssl_csr_export($csr, $csrout);
		unlink('temp/'.$name);
		return trim($csrout);



	}
	public function PrivateKeyAction(){
		if(!in_array(Request::Get('strength'),array('2048','4096')))
			return 'wrong strength! only 2048 or 4096';


		if(!defined("_LIVE_")){
				return '-----DEMO BEGIN RSA PRIVATE KEY-----
		MIIEogIBAAKDEMOArVGNgpzjAaWv/cwMnDEMO3X21ubGRFLq27LPG3njsBicCGx+
		F1BtKyEBTrkzJzDi0CDehvohNuiPJlsrz8kV6aju9AZojRWMhUsBRLOcIm44dCUe
		mtexO2/A9vWKRKDpgNj1y7xEZdtH5x+4tv7R/dMFEQvnD1Z8IDVhLuYfVgdOT+Qc
		POuWLU0P1jIldvwFG2Pia0Rh+2PO7DEMOJXnws4vMi3pxUyJ+X6H5yc4m/3r77tt
		w86AupwoS8bQziaKszTs5t0bBUUGBg2YBFKLl2D+xHn8AMVbmWubJ4uQ8GiGdlpd
		Rb66KuYUy6pWPf72rv9DYPJxZzNK+DAnzQmtCwIDAQABAoIBAASbXFWScf5DNzBZ
		0iQBgMKHEi6YJ8D9Kzz3vcWPMdwma09WAuv/uUciM3SyO4fZJs49cWzJDUT2XYe5
		eKqnP6vcT62w1eqaHgDEMO2lMItBvsJnki6veQiJaemmkSxN0qcYHUBzGqZSc9DS
		6QgEMfS7PTE8zcF8nQQGJzUl7MNOlzWb0ulAUBhmsMH3y+nfMB/ZL0i3zhMMSqgk
		HzWJRI74dYZqqGFUZYfC2lWB9A+Zq68RxvVP0DEMOotrcGcpP+W41ZASTueEGhIx
		/ypcNseHUUBSyb59kMnZmiDY97+TcibApXmGfWtzp3F78kdvDfqqx/99isZ7wNev
		qcNH4zECgYEA0qhDMO2FKX6jeqVASO5S8xAfboPHYBswh28tcP+TcQnHk3nVB3wW
		bKLVabklEJqRXBXig5ddivCK8s64lQBGlRA3NEnzDnqLGkKel7gKbnA8z9u1/Oxu
		2kRjDTDviG1FuPGcYlrBYzMPrDBZQKl+KFY1gBupgR0RRzPnl2I+950CgYEA0p/A
		FkvbyuyQIsLwaMewtnvwOgRtBPvGR7iQcrRyubNKuUbef4hJ+XUr5WaxGWAaqjHO
		n6cusRJWO10FcTViTV/e9xVwOYlnGPuPuDyAYMqLODEMOgFVurtCKkKSilfBQ1lr
		W0v8nO2vxjLFzal4EynxozGpAoIxZbkKVcoSWscCgYB1/DgXj/jfLsCdVqTUtDbd
		UDBqk4A9gb0CffBzk5GfBB01qkz4bjWZDvJ8zmfGDuxPKSq/DS4lPWh1afbT7V2a
		64Qf0zNA7r2uLZXp3/hntpE40hPx3vKPaTIZF0lxndIpLZmrNK2Pw++JP2Md5lB0
		gyRf5h5f5EnjGV0i2rHtuQKBgA7lY3VwOgQ5BNyggtY2QDEMO7+6rmcq01QhNn4W
		SwLdLky6OWQ1pF2zLr6Tq7TKujgNO7rI3SGC1XxvUMI371Lfk+pPptc644K9z+VM
		yhuOV5iIfzekXobNVZmdibWlDTMRMOmNDzmeCc9vqdOox6g7UC6lhXl68blrA9Mb
		bn7jAoGARrvt5MiR6b4isZ4oX7wkFQ82oIas16Dj+Bx6EK/6sWJ8UkVxUKTzXyRa
		jCygFRZIeDeKoDNQAyQU7oQTxYBA5SiaNWnOD1plIeoUAe/FdWFl2T7SSLgvJPdQ
		+ZfTRjMfQZdgRe+TWwUEYM15lQuPfnt6Lg7nMc+7o7aoNK/7Faw=
		-----DEMO END RSA PRIVATE KEY-----';
		}else{

			$config = array(
				"private_key_bits" => (int) Request::Get('strength'),
				'digest_alg' => 'md5',
				'x509_extensions' => 'v3_ca',
				'req_extensions'   => 'v3_req',
				'private_key_type' => OPENSSL_KEYTYPE_RSA
			);


			// Create the private and public key
			$res = openssl_pkey_new($config);

			// Extract the private key from $res to $privKey
			openssl_pkey_export($res, $privKey);
			return $privKey;
		}

	}
}