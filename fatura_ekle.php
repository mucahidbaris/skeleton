<?php
global $db;
include_once 'header.php';

@$tur=strip_tags(trim($_GET['tur']));
    switch ($tur){
        case 'cek':
       $fatura_turu='cek';
       break;
        case 'senet':
            $fatura_turu='senet';
        break;
        case 'gelir':
            $fatura_turu='gelir';
            break;
        case 'gider':
            $fatura_turu='gider';
            break;
        case 'teklif':
            $fatura_turu='teklif';
            break;
        default:
            $fatura_turu='gider';
    }





$hesap= new Hesap($db,$_SESSION['user_id'],$_SESSION['username'],$_SESSION['firma_id']);
$hesaplar=$hesap->getHesap();

$musteri=new Musteri($db,$_SESSION['user_id'],$_SESSION['username'],$_SESSION['firma_id']);
$musteriler=$musteri->getMusteri();


if(isset($_POST['submit'])) if(hash_equals($_SESSION['token'],$_POST['token'])) {
    unset($_POST['token'],$_POST['submit']);
    $_POST['resim']=file_get_contents(webpConvert2($_FILES['file']['tmp_name'],1));
    $urun=new Hesap($db,$_SESSION['user_id'],$_SESSION['username'],$_SESSION['firma_id']);
    $result=$urun->setFatura($_POST);
    if($result){
        header("location:fatura.php?s=basarili");
    }else{
        header("location:fatura.php?s=hata");
    }
}

?>
<div id="main">
    <div class="container-fluid">
        <div class="page-header">
            <div class="alert">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Bilgilendirme!</strong> Fatura ekledikten sonra fatura diğer ilgili ekranlarda hangi fatura ile işlme yapacagın sorulacaktır.


            </div>
        </div>
        <div class="breadcrumbs">

            <ul>
                <li>
                    <a href="index.php">Home</a>
                    <i class="icon-angle-right"></i>
                </li>
                <li>
                    <a href="">Yeni</a>
                        <i class="icon-angle-right"></i>
                </li>
            </ul>
            <div class="close-bread">
                <a href="#"><i class="icon-remove"></i></a>
            </div>

        </div>
        <div class="row-fluid">
            <div class="span12">
                <div class="box box-color box-bordered">
                    <div class="box-title">
                        <h3><i class="icon-th-list"></i> Yeni </h3>
                    </div>
                    <div class="box-content nopadding">
                        <form action="#" method="POST" enctype="multipart/form-data" class='form-horizontal form-column form-bordered'>
                            <div class="span6">
                                <div class="control-group">
                                    <label for="textfield" class="control-label">Türü</label>
                                    <div class="controls">
                                        <div class="input-xlarge">
                                            <input type="text" name="tur" id="tur" value="<?=$fatura_turu?>" placeholder="fatura türü" class="input-xlarge" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="textfield" class="control-label">Firma</label>
                                    <div class="controls">
                                        <div class="input-xlarge">
                                            <select name="musteri_id" id="musteri_id" class='chosen-select' required>
                                                <?php  foreach ( $musteriler as $mstr): ?>
                                                    <option value="<?=$mstr->id?>"><?=$mstr->adi?></option>
                                                <?php  endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="password" class="control-label">Açıklama</label>
                                    <div class="controls">
                                        <input type="text"   name="aciklama" id="aciklma" placeholder="kısa detay" class="input-block-level" required>
                                    </div>
                                </div>
                                <?php if ($fatura_turu=='gider'): ?>
                                <div class="control-group">
                                    <label for="password" class="control-label">Hammadde<br>(Ürün Alımı)?</label>
                                    <div class="controls">
                                        <label class="checkbox">
                                            <input type="checkbox" name="etiket" value="urun" wfd-id="id32"> Stoklu Ürün (Hammadde ve Cihaz) Alımı
                                        </label>
                                    </div>
                                </div>
                                <?php endif;?>
                            </div>
                            <div class="span6">

                                <div class="control-group">
                                    <label for="textfield" class="control-label">Hesap & Para Birimi</label>
                                    <div class="controls">
                                        <div class="input-xlarge">
                                            <select name="hesap" id="hesap" class='chosen-select' required>
                                                <?php foreach ($hesaplar as $hsp): ?>
                                                    <option value="<?=$hsp->id?>,<?=$hsp->para_birimi?>"><?=$hsp->hesap_adi?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($fatura_turu=='gider' || $fatura_turu=='cek' || $fatura_turu=='senet'): ?>
                                    <div class="control-group">
                                        <label for="textfield" class="control-label">Evrak Numarasi</label>
                                        <div class="controls">
                                            <input type="text" name="evrak_no" id="evrak_no" placeholder="EarsivFaturano" class="input-xlarge" required>
                                        </div>
                                    </div>
                                <div class="control-group">
                                    <label for="password" class="control-label">Toplam Fatura Tutarı(KDV'siz)</label>
                                    <div class="controls">
                                        <input type="number" min="1"  name="tutar" id="tutar" placeholder="1 birim satış fiyatı" class="input-xlarge" required>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="control-group">
                                    <label for="password" class="control-label">KDV Oranı</label>
                                    <div class="controls">
                                        <input type="number" min="1"  name="kdv_orani" id="kdv_orani" placeholder="1 veya 20" class="input-xlarge" required>
                                    </div>
                                </div>
                                <?php if ($fatura_turu=='gider' || $fatura_turu=='cek' || $fatura_turu=='senet'): ?>
                                <div class="control-group">
                                    <label for="textfield" class="control-label"></label>
                                    <div class="controls">
                                        <div class="fileupload fileupload-new" data-provides="fileupload">
                                            <div class="fileupload-new thumbnail" style="width: 200px; height: 150px;"><img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=no+image" /></div>
                                            <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                                            <div>
                                                <span class="btn btn-file"><span class="fileupload-new">Select image</span><span class="fileupload-exists">Değiştir</span><input type="file" name='file' /></span>
                                                <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Sil</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                            </div>
                            <div class="span12">
                                <div class="form-actions">
                                    <input type="hidden" name="token" value="<?=$_SESSION['token']?>" />
                                    <button type="submit" name="submit"  value="1" class="btn btn-primary">Kaydet</button>
                                    <button type="button" class="btn">İptal</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once 'footer.php'; ?>
