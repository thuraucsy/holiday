<?php
function mmstrlen($str) {
$standalones = array("ဤ", "၍", "ဪ", "၏", "၊", "။", "၌");
$consonants = array("က", "ခ", "ဂ", "ဃ", "င", "စ", "ဆ", "ဇ", "ဈ", "ည", "ဍ", "ဌ", "ဋ", "ဎ", "ဏ", "တ", "ထ", "ဒ", "ဓ", "န", "ပ", "ဖ", "ဗ", "ဘ", "မ", "ယ", "ရ", "လ", "ဝ", "သ", "ဟ", "ဠ", "အ");
$numbers = array("၀", "၁", "၂", "၃", "၄", "၅", "၆", "၇", "၈", "၉");
$len = mb_strlen($str, "UTF-8");
$count = 0;
for($i = 0; $i < $len; $i++) {
$char = mb_substr($str, $i, 1, "UTF-8");
if(!burmese($char)) {
$count++;
} else {
if(in_array($char, $consonants) ||
in_array($char, $standalones) ||
in_array($char, $numbers) || $char == " ") $count++;
if($char == "်") {
$prev = mb_substr($str, $i - 1, 1, "UTF-8");
if(in_array($prev, $consonants)) $count--;
}
}
}
return $count;
}
## Dependencies
function burmese($char) {
return utf82uni($char) >= 0x1000 and utf82uni($char) <= 0x109f;
}
// Convert UTF-8 string to Unicode hex values
function utf82uni($str) {
$unicode = array();
$values = array();
$lookingFor = 1;
for ($i = 0; $i < strlen( $str ); $i++ ) {
$thisValue = ord( $str[ $i ] );
if ( $thisValue < ord('A') ) {
// exclude 0-9
if ($thisValue >= ord('0') && $thisValue <= ord('9')) {
// number
$unicode[] = chr($thisValue);
}
else {
$unicode[] = '%'.dechex($thisValue);
}
} else {
if ( $thisValue < 128)
$unicode[] = $str[ $i ];
else {
if ( count( $values ) == 0 ) $lookingFor = ( $thisValue < 224 ) ? 2 : 3;
$values[] = $thisValue;
if ( count( $values ) == $lookingFor ) {
$number = ( $lookingFor == 3 ) ?
( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
$number = dechex($number);
$unicode[] = (strlen($number)==3)?"0x0".$number:"0x".$number;
$values = array();
$lookingFor = 1;
} // if
} // if
}
} // for
return implode("",$unicode);
} 
echo mmstrlen("ရောင်းစားတဲ့မော်ဒယ်တစ်ခုကိုလည်းတွေ့ရပါတယ်။");
?>
