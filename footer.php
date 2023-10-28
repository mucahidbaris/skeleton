</div>
</body></html>
<?php
if(isset($_GET['s'])){
    switch ($_GET['s']){
        case 'basarili':
            bildirim('İşlem Başarılı','Başarıyla tamamlandı.',);
            break;
        case 'hata':
            bildirim('HATA','Hay Aksi :( Birşeyler ters gitti.',);
            break;
    }
}
?>

