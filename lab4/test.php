<?php
$hoTen = "Nguyễn Văn A";
echo "Chào bạn $hoTen";
$tuoi = 20;
$diem = 8.5;
$isPass = true;
if ($isPass) {
 echo "Bạn đã qua môn";
} else {
 echo "Bạn đã rớt môn";
}
$mang = array("NT208", "NT521", "NT513");
echo $mang[0]; //NT208
/* Associative Array */
$sinhVien = array(
    "ten" => "A",
    "tuoi" => 20
   );
   echo $sinhVien["ten"]; //A
   isset($sinhVien); //true
   isset($sv); //false
   ?>