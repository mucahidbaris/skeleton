<?php
ini_set('display_errors', E_ALL);
ini_set('display_startup_errors', E_ALL);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Istanbul');
session_start();
ob_start();
require_once 'vendor/autoload.php';
use Dcblogdev\PdoWrapper\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// Looing for .env at the root directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$site = str_replace('www.', '', $_SERVER['HTTP_HOST']);
$decode = openssl_decrypt($_SERVER['lisans'], 'AES-128-ECB', 'lmlzhop1q');
if ($site != $decode) {
    // echo 'Sistemsel Bir sorun oluştur Lütfen  Yazılımcınıza danışın';
    // die();
}
// make a connection to mysql
$options = [
    //required
    'host' => $_ENV['mysql_host'],
    'port' => $_ENV['mysql_port'],
    'username' => $_ENV['mysql_username'],
    'database' => $_ENV['mysql_database'],
    'password' => $_ENV['mysql_password'],
];

class Admin  extends Database{

    public $username,$pasword,$email,$id,$error;
    public  bool $login;

    /**
     * @throws Exception|\Exception
     */
    public  function __construct($args)
    {
        parent::__construct($args);
        if(!isset($_SESSION['token']))  $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    public function islogin() : bool
    {
        if (isset($_SESSION['login']) && $_SESSION['login']){
            $this->login=$_SESSION['login'];
            return true;
        }
            $this->login=false;
            return false;
    }
    public function login($username,$password): bool
    {
        $result=$this->row("select * from user where username=? and password=?",[$username,$password]);
       if($result){
           $_SESSION['login']=true;
           $_SESSION['username']=$result->username;
           $_SESSION['name']=$result->name;
           $_SESSION['email']=$result->email;
           return true;
       }else{
           return false;
       }
    }
    public function  logout(): void
    {
        $this->login=false;
        session_destroy();
        ob_flush();
        ob_clean();
    }
    public function SendMail($mail,$name,$subject,$message,$file=NULL): string|bool
    {


        $url = "https://api.sendgrid.com/v3/mail/send";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Authorization: Bearer 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",
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = [
            "personalizations" => [
                [
                    "to" => [
                        [
                            "email" => "$mail",
                            "name" =>"$name"
                        ]
                    ]
                ]
            ],
            "from" => [
                "email" => "webmaster@pemmobilya.com.tr"
            ],
            "subject" => $subject,
            "content" => [
                [
                    "type" => "text/plain",
                    "value" => $message
                ]
            ]
        ];


        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

//for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;


    }
    public function json2($data,$to): array|string|null
    {
            if($to=='string'){
                return ($data) ? implode(json_decode($data)): NULL;
            }else{
                //array
                return ($data) ?(explode(',', json_decode($data)[0])) : NULL;
            }

    }

    /**
     * @throws ImagickException
     */
    function webpConvert2($file, $compression_quality = 20): bool|string
    {
        if (!file_exists($file)) {
            return false;
        }

        $file_type = exif_imagetype($file);
        //https://www.php.net/manual/en/function.exif-imagetype.php
        //exif_imagetype($file);
        // 1    IMAGETYPE_GIF
        // 2    IMAGETYPE_JPEG
        // 3    IMAGETYPE_PNG
        // 6    IMAGETYPE_BMP
        // 15   IMAGETYPE_WBMP
        // 16   IMAGETYPE_XBM

        $output_file = $file . '.webp';
        if (file_exists($output_file)) {
            return $output_file;
        }

        if (function_exists('imagewebp')) {
            switch ($file_type) {
                case '1': //IMAGETYPE_GIF
                    $image = imagecreatefromgif($file);
                    break;
                case '2': //IMAGETYPE_JPEG
                    $image = imagecreatefromjpeg($file);
                    break;
                case '3': //IMAGETYPE_PNG
                    $image = imagecreatefrompng($file);
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    break;
                case '6': // IMAGETYPE_BMP
                    $image = imagecreatefrombmp($file);
                    break;
                case '15': //IMAGETYPE_Webp
                    return false;
                    break;
                case '16': //IMAGETYPE_XBM
                    $image = imagecreatefromxbm($file);
                    break;
                default:
                    return false;
            }

            // Save the image
            $result = imagewebp($image, $output_file, $compression_quality);
            if (false === $result) {
                return false;
            }

            // Free up memory
            imagedestroy($image);

            return $output_file;
        }

        if (class_exists('Imagick')) {
            $image = new Imagick();
            $image->readImage($file);
            if ($file_type === "3") {
                $image->setImageFormat('webp');
                $image->setImageCompressionQuality($compression_quality);
                $image->setOption('webp:lossless', 'true');
            }
            $image->writeImage($output_file);

            return $output_file;
        }

        return false;
    }

}
$admin = new Admin($options);

function bildirim($title, $message): void
{
    echo "
        <script>
    	
$.extend($.gritter.options, {
		  		    position: 'bottom-right' // possibilities: bottom-left, bottom-right, top-left, top-right
			
		});

$.gritter.add({
				
				// (string | mandatory) the heading of the notification
				title: '$title',
				// (string | mandatory) the text inside the notification
				text: '$message',
				// (string | optional) the image to display on the left
			image:'https://cdn2.iconfinder.com/data/icons/metro-uinvert-dock/256/Info.png',
				// (bool | optional) if you want it to fade out on its own or just sit there
				sticky: true,
				// (int | optional) the time you want it to be alive for before fading out
				
			});


    	 </script>
		";

}