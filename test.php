<?php


/*********************************************************************************************************/


class  User {

    private  $_db;
    public  $username;
    public int $firmaid;
    public int $firmalar;
    public string $error;
    public  bool $login;
    public  string $user_email,$user_id,$user_name;



    /**
     * @param $username
     * @param $password
     * @return bool
     */
    public function login ($username, $password): bool
    {
        $this->username=($this->KullaniciDogrula($username))?$username : NULL ;
        if($this->username==NULL){
            return false;
        }
        $password1 =($this->PasswordDogrula($password))? $password : NULL;

        if($password1 ==NULL){
            return false;
        }

        $login=$this->_db->row('select * from user where phone=? and active=?',[$this->username,1]);
        if($login && password_verify($password1,$login->password)){

            $_SESSION['giris']=true;
            $_SESSION['username']=$login->phone;
            $_SESSION['user_id']=$login->id;
            $_SESSION['user_email']=$login->email;
            $_SESSION['user_name']=$login->name;
            //$_SESSION['firmalar']=$login->firma_id;/* todo user_yetki tablosunda firmaları ve yetkilerini çekecek bu field kullanım dışıdır.*/
            $this->username=$login->phone;
            $this->firmalar=$login->firma_id;
            $this->login=true;
            $this->user_id=$login->id;
            $this->user_email=$login->email;
            $this->user_name=$login->name;
            //todo last_login update edilecek loginde login.php?giris=basarili geti göndrfdiği zaman tetiklenece  bir class funksiyonu ve last login bilgisi girilimş olacak ayrıca
            return true;
        }else{
            return false;
        }
    }
    /**
     * @param $username
     * @return bool
     */
    private function KullaniciDogrula($username): bool
    {
        if (strlen($username) < 3) {
            return false;
        }

        if (strlen($username) > 20) {
            return false;
        }

        /* if (! ctype_alnum($username)) {
             return false;
         }*/

        return true;
    }

    /**
     * @param $password
     * @return bool
     */
    private  function PasswordDogrula($password): bool
    {
        if (strlen($password) < 3) {
            return false;
        }

        if (strlen($password) > 20) {
            return false;
        }

        /*if (! ctype_alnum($password)) {
            return false;
        }*/

        return true;

    }

    /**
     * @param $email
     * @return bool
     */
    private function EmailDogrula($email): bool
    {
        $email = htmlspecialchars_decode($email, ENT_QUOTES);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)){
            return true;
        }
        return false;
    }
    public function UserSorgu($userlike)
    {
        $a = $this->_db->row("select * from user where id = ? OR phone= ? OR email= ? OR token= ?", [$userlike, $userlike, $userlike,$userlike]);
        if ($a) {  return $a; }
        return false;
    }

    /**
     * @param $firma_id
     * @return void
     */


    /**
     * @param $userlike
     * @return int
     */
    public  function UserSay($userlike): int
    {
        $a = $this->_db->count("select * from user where id = ? OR phone= ? OR email= ?", [$userlike, $userlike, $userlike]);
        if ($a) {  return $a; }
        return 0;
    }
    /**
     * @return void
     */
    public function  logout(): void
    {
        $this->login=false;
        session_destroy();
        ob_flush();
        ob_clean();
    }

    /**
     * @return bool
     */
    public function giris_varmi(): bool
    {
        // TODO $this->login=true değerine taşınacak burada session oöayacak login değeri true ise giriş yapmış kullancı.
        if (isset($_SESSION['giris']) && $_SESSION['giris']){
            return true;
        }
        return false;
    }

    /**
     * @param $username
     * @param $password
     * @param $name
     * @param $firma_id
     * @param $email
     * @param int $active
     * @return false | int
     */
    private  function kayit ($username, $password, $name, $firma_id, $email, int $active=1,): false | int
    {
        if(!($this->KullaniciDogrula($username) && $this->PasswordDogrula($password))){
            $this->error="Kullanıcı adı ve şifrenizi kontrol ediniz.";
            return false;

        }
        if(!($this->EmailDogrula($email))){
            $this->error="Hatalı mail adresi";
            return false;
        }
        if(!($count=$this->_db->count("select * from user where phone = ?",[$username]))){
            $this->error="Böyle bir kullanıcı mevcut";
            return false;
        }
        $data=[
            'phone'=>$username,
            'password'=>password_hash($password,PASSWORD_DEFAULT),
            'name'=>$name,
            'firma_id'=>$firma_id,
            'email'=>$email,
            'active'=>$active
        ];
        if($insert=$this->_db->insert("user",$data)){
            $yetki=array('anasayfa');

            if($this->_db->insert('user_yetki',['user_id'=>$insert,'firma_id'=>$firma_id,'page'=>json_encode($yetki),'log_user'=>$this->username])){
                return true;
            } return false;
        } return false;


    }

    /**
     * @return string
     */
    public function Token(): string {
        return  md5(uniqid(rand(),true));
    }

    /**
     * @param $userlike
     * @return StdClass|false
     */
    public function  UserTokenOlustur($userlike): false|StdClass
    {
        $user=$this->UserSorgu($userlike);
        if($user){
            $token=$this->Token();
            $this->_db->update('user',['token'=>$token],['id'=>$user->id]);
            $a=new StdClass();
            $a->username=$user->phone;
            $a->name=$user->name;
            $a->phone=$user->phone;
            $a->email=$user->email;
            $a->token=$token;
            return $a;
        }
        return false;
    }

    /**
     * @param $token
     * @param $password
     * @return bool
     */
    public function  sifre_sifirla($token,$password): bool {
        if($usr=$user=$this->UserSorgu($token)){
            $update=$this->_db->update('user',['password'=>password_hash($password,PASSWORD_DEFAULT),'token'=>NULL],['token'=>$usr->token]);
            if($update){return true;} return false;
        }
        return false;
    }

    /**
     * @param $id
     * @param array $yetki
     * @param $firma_id
     * @return bool
     */
    private  function Yetkiver($id,array $yetki,$firma_id): bool
    {
        if($this->_db->update('user_yetki',['page'=>json_encode($yetki)],['user_id'=>$id,$firma_id=>$firma_id])){
            return true;
        }
        return false;
    }

    /**
     * @param $user_id
     * @param $firma_id
     * @return false
     */
    public  function  yetki_varmi($user_id,$firma_id){
        $sql=$this->_db->row("select * from user_yetki where user_id=? AND firma_id=?",[$user_id,$firma_id]);
        if($sql){
            return $sql->page;
        }
        return false;
    }

    /**
     * @param $mail
     * @param $name
     * @param $subject
     * @param $message
     * @param $file
     * @return string|bool
     */
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

};
class Firma{

    public $firma_id,$firma_name,$firma_logo,$firma_vd,$firma_vd_no,$firma_adres,$firma_mail,$firma_telefon;
    private  $username;
    private $user_id;
    private  $_db;

    public function __construct($db,$user_id,$username)
    {
        $this->_db=$db;
        $this->user_id=$user_id;
        $this->username=$username;

    }
    public function FirmaGetir($firmalike)
    {
        $a = $this->_db->row("select * from firma where  id = ? OR firma_vd= ? OR firma_vd_no= ? ", [$firmalike, $firmalike, $firmalike]);
        if ($a) {  return $a; }
        return false;
    }

    /**
     * @return mixed
     */
    public function FirmalarGetir(): mixed
    {
        // $a = $this->_db->rows("select * from firma where id = ? OR firma_vd= ? OR firma_vd_no= ? where  (select firma_id from user_yetki where user_id)", [$firmalike, $firmalike, $firmalike,]);
        $a=$this->_db->rows("select * from firma where id IN (select firma_id from user_yetki where user_id=?)",[$this->user_id]);
        if ($a) {  return $a; }
        return false;
    }

    /**
     * @param $firmaid
     * @return int
     */
    public function FirmaYetkilimi($firmaid): int
    {
        return $this->_db->count("select * form user_yetki where user_id =? firma_id=?",[$this->user_id,$firmaid]);
    }

    /**
     * @param array $firma
     * @return bool
     */
    public function FirmaEkle(array $firma): bool
    {
        $firma['log_user']=$this->username;
        $a=$this->_db->insert('firma',$firma);
        if($a){//firmayı ekleyen olduğu için tüm yetkileri ondadır   todo full teyki diye fonksiyon önerebilirim.
            $yetki=[
                'admin'=>true,
                'firma'=>3,
                'user'=>3,
                'urun'=>3
            ];
            $data=[
                'user_id'=>$this->user_id,
                'firma_id'=>$a,
                'page'=>json_encode($yetki),
                'log_user'=>$this->username
            ];
            $this->_db->insert('user_yetki',$data);

            return true;
        }else{
            return  false;
        }
    }

    public function FirmaYetki(){
        //todo  420. satırdaki yetkilendirme işlemlerinde bu fonksinyonu kullanacagız öncesinde form kısmını tasarlamam ona göre şekil vermem gerekecek. Gerekli yetki sayfası tasarlanacak.


    }

    /**
     * @param array $firma
     * @param $where
     * @return bool
     */
    public function FirmaDuzenle(array $firma, $where): bool
    {
        /*if(!$this->FirmaYetkilimi($where)){
            return false;
        }*/
        //todo kayıt var amam düzenleme yetkisi olmayabilir...

        $firma['log_user']=$this->username;
        $a=$this->_db->update('firma',$firma,['id'=>$where]);
        if($a){
            return true;
        }else{
            return  false;
        }
    }

    /**
     * @return bool
     */
    public function firma_varmi(): bool
    {
        // TODO $this->login=true değerine taşınacak burada session oöayacak login değeri true ise giriş yapmış kullancı.
        if (isset($_SESSION['firma']) && $_SESSION['firma']){
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return void
     */
    public  function Firma_login($id): void
    {
        $firma=$this->FirmaGetir($id);
        $_SESSION['firma']=true;
        $_SESSION['firma_id']=$firma->id;
        $this->firma_id=$firma->id;
        $_SESSION['firma_name']=$firma->firma_name;
        $this->firma_name=$firma->firma_name;
        $_SESSION['firma_logo']=$firma->firma_logo;
        $this->firma_logo=$firma->firma_logo;
        $_SESSION['firma_vd']=$firma->firma_vd;
        $this->firma_vd=$firma->firma_vd;
        $_SESSION['firma_vd_no']=$firma->firma_vd_no;
        $this->firma_vd_no=$firma->firma_vd_no;
        $_SESSION['firma_adres']=$firma->firma_adres;
        $this->firma_adres=$firma->firma_adres;
        $_SESSION['firma_mail']=$firma->firma_mail;
        $this->firma_mail=$firma->firma_mail;
        $_SESSION['firma_telefon']=$firma->firma_telefon;
        $this->firma_telefon=$firma->firma_telefon;

    }
}
#[AllowDynamicProperties] class Urun{

    private $_db;
    public $username;
    public $user_id;
    public $id;
    private  $firma_id;


    public function __construct($db,$user_id,$username,$firma_id)
    {
        $this->_db=$db;
        $this->user_id=$user_id;
        $this->username=$username;
        $this->firma_id=$firma_id;

    }
    public function getUrunOzellik(){

        return $this->_db->row("select * from urun_ozellik where firma_id =?",[$this->firma_id]);

    }
    public  function  setUrunOzellik($kalite,$marka,$renk,$ebat,$etiket): bool
    {
        $data=[
            'kalite'=>json_encode($kalite),
            'marka'=>json_encode($marka),
            'renk'=>json_encode($renk),
            'ebat'=>json_encode($ebat),
            'etiket'=>json_encode($etiket),
            'user_id'=>$this->user_id,
            'firma_id'=>$this->firma_id
        ];
        if($this->_db->count("select * from urun_ozellik where firma_id=?",[$this->firma_id])){
            if($this->_db->update('urun_ozellik',$data,['firma_id'=>$this->firma_id])){
                return true;
            }
        }else{
            if($this->_db->insert('urun_ozellik',$data)){
                return true;
            }
        }

        return false;

    }
    public  function setUrun(array $data){
        ///sırasıyla tablolar urun,urun_hareket,fatura,hesap_hareket
        $data['firma_id']=$this->firma_id;
        $data['log_user']=$this->user_id;
        $kdv_orani=$data['kdv_orani'];
        $fatura_id=$data['fatura_id'];
        $hesap_id=$data['hesap_id'];
        $musteri_id=$data['musteri_id'];
        $parabirimi=$data['parabirimi'];
        $tutar=$data['tutar'];
        $odenen=$data['odenen'];

        unset($data['hesap'],$data['alis_fiyati'],$data['satis_fiyati'],$data['kdv_orani'],$data['fatura_id'],$data['hesap_id'],$data['musteri_id'],$data['parabirimi'],$data['tutar'],$data['odenen']);
        $urun_id=$this->_db->insert('urun',$data);// urun tablosuna datayı gönderdik.
        $this->SetUrunHareket(['urun_id'=>$urun_id,'adet'=>$data['urun_stok'],'birim'=>$data['urun_birim'],'hesap_id'=>$hesap_id,'musteri_id'=>$musteri_id,'fatura_id'=>$fatura_id,'parabirimi'=>$parabirimi,'tutar'=>$tutar,'kdv_orani'=>$kdv_orani,'odenen'=>$odenen,'aciklama'=>'Ürün Stok eklemesi yapildi']);

        return $urun_id;

    }
    public function SetUrunHareket(array $data){
        $data['firma_id']=$this->firma_id;
        $data['log_user']=$this->user_id;
        $yeniodenen=$data['odenen']+($data['tutar']*$data['adet']);
        unset($data['odenen']);
        (new Hesap($this->_db,$this->user_id,$this->username,$this->firma_id))->UpdateFatura($data['fatura_id'],['odenen'=>$yeniodenen]);
        return $this->_db->insert('urun_hareket',$data);


    }
}

#[AllowDynamicProperties] class Hesap{
    private $_db;
    public $username;
    public $user_id;
    public $id;



    public function __construct($db,$user_id,$username,$firma_id)
    {
        $this->_db=$db;
        $this->user_id=$user_id;
        $this->username=$username;
        $this->firma_id=$firma_id;

    }
    public function getHesap($id=NULL ,array|string $field="*"){
        if($id){
            return $this->_db->row("select $field from hesap where firma_id =? and id=?",[$this->firma_id,$id]);
        }else{
            return $this->_db->rows("select $field from hesap where firma_id =?",[$this->firma_id]);
        }

    }
    public function getFatura($tur=NULL,$etiket=NULL,array|string $field="*"){
        if($etiket){
            return $this->_db->rows("select $field from fatura where tutar>odenen and firma_id =? and etiket=? and tutar>?",[$this->firma_id,$etiket,':odenen']);
        }elseif($tur){
            return $this->_db->rows("select $field from fatura where firma_id =? and tur=?",[$this->firma_id,$tur]);
        }else{
            return $this->_db->rows("select $field from fatura where firma_id =?",[$this->firma_id]);
        }

    }
    public function getFaturainfo($id,array|string $field="*")
    {
        $fatura= $this->_db->row("select $field from fatura where firma_id=? and id =?",[$this->firma_id,$id]);
        $musteri_adi=(new Musteri($this->_db,$this->user_id,$this->username,$this->firma_id))->getMusteri($fatura->musteri_id,'adi')->adi;
        $fatura->musteri_adi=$musteri_adi;
        return $fatura;

    }
    public function  setHesapHareket(array $data){
        //firma_id,user_id,log_user içeride işleyebilirim
        $data['firma_id']=$this->firma_id;
        $data['log_user']=$this->user_id;
        $hesap_hareket_id=$this->_db->insert('hesap_hareket',$data);
        return $hesap_hareket_id;

    }
    public function setFatura(array $data): array
    {
        switch ($data['tur']){///hesap hareketinde giderse -1  cek senet gelir teklif de ise +1 not gelir ve teklifde ise tutar verisi gelmemektedir sonradan yansımaktadır.
            case 'gider':
                $katsayi=-1;
                break;
            default:
                $katsayi=1;

        }
        $data['firma_id']=$this->firma_id;
        $data['log_user']=$this->user_id;
        [$data['hesap_id'],$data['parabirimi']]=explode(',',$data['hesap']);
        unset($data['hesap']);
        $fatura_id=$this->_db->insert('fatura',$data);// urun tablosuna datayı gönderdik.
        $hesap_hareket_id=$this->setHesapHareket(['hesap_id'=>$data['hesap_id'],'fatura_id'=>$fatura_id,'tutar'=>(KDVDahil($data['tutar'],$data['kdv_orani']))*$katsayi,'aciklama'=>$data['aciklama'],'kdv_orani'=>$data['kdv_orani']]);
        return ['fatura_id'=>$fatura_id,'hesap_hareket_id'=>$hesap_hareket_id,'hesap_id'=>$data['hesap_id']];

    }
    public  function UpdateFatura($id,array $update){
        $update['log_user']=  $this->user_id;
        $where = [
            'id' => $id,
            'firma_id'=>$this->firma_id
        ];
        return $this->_db->update('fatura',$update,$where);
    }



}

#[AllowDynamicProperties] class Musteri{
    private $_db;
    public $username;
    public $user_id;
    public $id;



    public function __construct($db,$user_id,$username,$firma_id)
    {
        $this->_db=$db;
        $this->user_id=$user_id;
        $this->username=$username;
        $this->firma_id=$firma_id;

    }
    public  function  getMusteri($id=NULL ,array|string $field="*"){

        if($id){
            return $this->_db->row("select $field from musteri where firma_id =? and id=?",[$this->firma_id,$id]);
        }else{
            return $this->_db->rows("select $field from musteri where firma_id =?",[$this->firma_id]);
        }

    }


}




?>
