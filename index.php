<html>
 <head>
<meta charset="UTF-8">
  <title> PRICE </title>
</head>
<body>

 <a href="index.php?sync=1"><img src="sync.png" alt="sync" width="150" height="150" /></a>

<?php


//старт подсчета времени
$start = microtime(true);





$link_array = array( 
'http://soccer-shop.com.ua/c7-myachi_dlya_futbola/filter/m-3', 
'http://soccer-shop.com.ua/c8-myachi_dlya_futzala/filter/m-3',
'http://soccer-shop.com.ua/c7-myachi_dlya_futbola/filter/m-5',
'http://soccer-shop.com.ua/c8-myachi_dlya_futzala/filter/m-5'
);


//Проверка на нажатие кнопки синхронизации
if (isset($_GET['sync'])) {
    $sync = 0;
}

//подгружаем библиотеку
include_once('simple_html_dom.php');

$dblocation = "goal01.mysql.ukraine.com.ua"; // Имя сервера
$dbuser = "goal01_db";          // Имя пользователя
$dbpasswd = "xVStkZXA";            // Пароль
$dbcnx = @mysql_connect($dblocation,$dbuser,$dbpasswd);

if (!$dbcnx) // Если дескриптор равен 0 соединение не установлено
{
  echo("<P>Server no find</P>");
  exit();
}

// Выбор БД 
mysql_select_db("goal01_db") or die(mysql_error());

echo " <table border='1' width='100%'> <tr> ";

//Перебор ссылок
for ($j = 0; $j <= count($link_array)-1; $j++) 
{

echo "<td valign='top'>";
echo " <table border='1' width='480px'> ";

//создаём новый объект
$html = new simple_html_dom();
	
// Создать DOM из URL или файла
$html = file_get_html($link_array[$j]);
	
//для определения артикла товара
$product_sku = $html->find('.prodModel');

//для определения статуса товара
$prod = $html->find('.prod-in');

//Находим все названия товаров
$name = $html->find('.products-name');	

//Находим все цены товаров
$price = $html->find('.int');

$num=0;

for ($i = 0; $i <= count($name)-1; $i++) 
{ 

// Определение статуса товара
$prod_new=strip_tags(html_entity_decode($prod[$i]));
$prod_new=trim($prod_new);
$status = substr($prod_new, 1, 1);
$status=ord($status);

if ($status==156)
$color='#8FBC8F';
elseif($status==159)
$color='#8B3626';
elseif($status==162)
$color='#1E90FF';
elseif($status==157)
$color='#FF0000';

// Очищаем от тегов

$product_sku_new=strip_tags($product_sku[$i]);

$name_new=strip_tags($name[$i]);

$price_new=strip_tags(html_entity_decode($price[$i]));

//Правельное решение по удалению пробела в цене
$price_new=str_replace(",",'.',$price_new);
$price_new=preg_replace("/[^x\d|*\.]/","",$price_new);


//узнаем продукт ид  по артиклу в базе сайта
$strSQL = "SELECT product_id FROM `jos_vm_product` where product_sku='{$product_sku_new}'";
$res =  mysql_query($strSQL);
$id_produkt = mysql_fetch_row($res);

//Если найдены совпадения артиклов

if ($id_produkt){

//Поиск цены в базе для сравнения перед обновлением
$strSQL = 'SELECT product_price FROM `jos_vm_product_price` where product_id='."$id_produkt[0]".'';
$res =  mysql_query($strSQL);
$pri = mysql_fetch_row($res);
$price_old = (int)$pri[0];


//Обновляем цену на сайте согласно найденому ид только по кнопке
if(($sync == 1)&&($price_old!=$price_new)){
$strSQL = "UPDATE `jos_vm_product_price` SET  product_price ='{$price_new}' where product_id='{$id_produkt[0]}'";
mysql_query($strSQL);
//Для подсчета кол обновленных запесей
 $num=$num+1;
}

//Поиск цены в базе для вывода
$strSQL = 'SELECT product_price FROM `jos_vm_product_price` where product_id='."$id_produkt[0]".'';
$res =  mysql_query($strSQL);
$pri = mysql_fetch_row($res);
$price_old = (int)$pri[0];

//Вывод старой цены и подсветка цен
if($price_old==$price_new) 
{
$color_price='#8FBC8F';
$price_old='';
}
else
{
$color_price='#FF0000';
$price_old='||'.$price_old;
}

}
else
{
$color_price='#F0F8FF';
$price_old='';
}

//Вывод на экран


echo "<tr>";

echo "<td>".$product_sku_new."</td><td bgcolor='".$color."'>".$name_new." </td>  <td bgcolor='".$color_price."'><b> <big>".$price_new." ".$price_old." </big> </b> </td>"; 

echo "</tr>";

  } 


//Вывод количества синхронизированных записей
//echo "<tr><td > sync-" .$num. "</td></tr>"; 



echo "</td>";
echo " </table>";


//освобождаем ресурсы
$html->clear(); 
unset($html);

}

echo " </td> </tr>  </table>";



// Закрытие соединения
mysql_close();


$time = microtime(true) - $start;
printf('time - %.4F sek.', $time);
?>

</body>
</html>



