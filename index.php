<html>
<head>
    <meta charset="UTF-8">
    <title> PRICE </title>
</head>
<body>

<a href="index.php?sync=1"><img src="sync.png" alt="sync" width="150" height="150"/></a>

<?php


//����� �������� �������
$start = microtime(true);


$link_array = array(
    'http://soccer-shop.com.ua/c7-myachi_dlya_futbola/filter/m-3',
    'http://soccer-shop.com.ua/c8-myachi_dlya_futzala/filter/m-3',
    'http://soccer-shop.com.ua/c7-myachi_dlya_futbola/filter/m-5',
    'http://soccer-shop.com.ua/c8-myachi_dlya_futzala/filter/m-5'
);


//�������� �� ������� ������ �������������
if (isset($_GET['sync'])) {
    $sync = 0;
}

//���������� ����������
include_once('simple_html_dom.php');

$dblocation = "goal01.mysql.ukraine.com.ua"; // ��� �������
$dbuser = "goal01_db";          // ��� ������������
$dbpasswd = "xVStkZXA";            // ������
$dbcnx = @mysql_connect($dblocation, $dbuser, $dbpasswd);

if (!$dbcnx) // ���� ���������� ����� 0 ���������� �� �����������
{
    echo("<P>Server no find</P>");
    exit();
}

// ����� �� 
mysql_select_db("goal01_db") or die(mysql_error());

echo " <table border='1' width='100%'> <tr> ";

//������� ������
for ($j = 0; $j <= count($link_array) - 1; $j++) {

    echo "<td valign='top'>";
    echo " <table border='1' width='480px'> ";

//������ ����� ������
    $html = new simple_html_dom();

// ������� DOM �� URL ��� �����
    $html = file_get_html($link_array[$j]);

//��� ����������� ������� ������
    $product_sku = $html->find('.prodModel');

//��� ����������� ������� ������
    $prod = $html->find('.prod-in');

//������� ��� �������� �������
    $name = $html->find('.products-name');

//������� ��� ���� �������
    $price = $html->find('.int');

    $num = 0;

    for ($i = 0; $i <= count($name) - 1; $i++) {

// ����������� ������� ������
        $prod_new = strip_tags(html_entity_decode($prod[$i]));
        $prod_new = trim($prod_new);
        $status = substr($prod_new, 1, 1);
        $status = ord($status);

        if ($status == 156)
            $color = '#8FBC8F';
        elseif ($status == 159)
            $color = '#8B3626';
        elseif ($status == 162)
            $color = '#1E90FF';
        elseif ($status == 157)
            $color = '#FF0000';

// ������� �� �����

        $product_sku_new = strip_tags($product_sku[$i]);

        $name_new = strip_tags($name[$i]);

        $price_new = strip_tags(html_entity_decode($price[$i]));

//���������� ������� �� �������� ������� � ����
        $price_new = str_replace(",", '.', $price_new);
        $price_new = preg_replace("/[^x\d|*\.]/", "", $price_new);


//������ ������� ��  �� ������� � ���� �����
        $strSQL = "SELECT product_id FROM `jos_vm_product` where product_sku='{$product_sku_new}'";
        $res = mysql_query($strSQL);
        $id_produkt = mysql_fetch_row($res);

//���� ������� ���������� ��������

        if ($id_produkt) {

//����� ���� � ���� ��� ��������� ����� �����������
            $strSQL = 'SELECT product_price FROM `jos_vm_product_price` where product_id=' . "$id_produkt[0]" . '';
            $res = mysql_query($strSQL);
            $pri = mysql_fetch_row($res);
            $price_old = (int)$pri[0];

//��������� ���� �� ����� �������� ��������� �� ������ �� ������
            if (($sync == 1) && ($price_old != $price_new)) {
                $strSQL = "UPDATE `jos_vm_product_price` SET  product_price ='{$price_new}' where product_id='{$id_produkt[0]}'";
                mysql_query($strSQL);
//��� �������� ��� ����������� �������
                $num = $num + 1;
            }

//����� ���� � ���� ��� ������
            $strSQL = 'SELECT product_price FROM `jos_vm_product_price` where product_id=' . "$id_produkt[0]" . '';
            $res = mysql_query($strSQL);
            $pri = mysql_fetch_row($res);
            $price_old = (int)$pri[0];

//����� ������ ���� � ��������� ���
            if ($price_old == $price_new) {
                $color_price = '#8FBC8F';
                $price_old = '';
            } else {
                $color_price = '#FF0000';
                $price_old = '||' . $price_old;
            }

        } else {
            $color_price = '#F0F8FF';
            $price_old = '';
        }

//����� �� �����


        echo "<tr>";
        echo "<td>" . $product_sku_new . "</td><td bgcolor='" . $color . "'>" . $name_new . " </td>  <td bgcolor='" . $color_price . "'><b> <big>" . $price_new . " " . $price_old . " </big> </b> </td>";
        echo "</tr>";

    }


//����� ���������� ������������������ �������
//echo "<tr><td > sync-" .$num. "</td></tr>"; 

    echo "</td>";
    echo " </table>";


//����������� �������
    $html->clear();
    unset($html);

}

echo " </td> </tr>  </table>";

// �������� ����������
mysql_close();

$time = microtime(true) - $start;
printf('time - %.4F sek.', $time);
?>

</body>
</html>