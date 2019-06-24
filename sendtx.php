<?php
ini_set( 'display_errors', 0 ); 
require_once(__DIR__ . '/jsonRPCClient.php');
$host = 'localhost';		/* monacoind 又は monacoin-qt を実行中のホストのアドレス */
$rpcuser = 'monacoinrpc';	/* monacoin.conf で指定した rpcユーザー名 */
$rpcpassword = '';		/* monacoin.conf で指定した rpcパスワード */
$rpcport = '19402';			/* monacoin.conf で指定した rpcポート */
$coindaddr = "http://$rpcuser:$rpcpassword@$host:$rpcport/";
$coind = new jsonRPCClient($coindaddr);

$changeAddress = 'MSyobon4F75Mp4jCTLih1LzLUbdwU5Hzfy';

function error($errorinput){return $errorinput.'<br><a href="./write.html">戻る</a>';}

function EncDec($input, $length = 0){
	$byte = dechex($input);
	for ($length; strlen($byte) < $length;) {
		$byte = '0'.$byte;
	}
	return $byte;
}
function LittleEndian($input){
	$result = '';
	$input_array = str_split($input,2);
	$count = count($input_array);
	for ($i=$count; $i > 0;) { 
		$i--;
		$result .= $input_array[$i];
	}
	return $result;
}
function CreateOP_RETURNScript($message){
	$encMessage = bin2hex($message);
	$encMessageByte = EncDec(strlen($encMessage)/2,2);
	$scriptByte =  EncDec(strlen($encMessage)/2+2,2);
	$result = $scriptByte.'6a'.$encMessageByte.$encMessage;
	return $result;
}

$var = 1;
$input = 1;

$inputTx = filter_input( INPUT_POST, 'tx' );
if ($inputTx == '') {
	$error = error('txidエラー1');
} else if (!preg_match('/^[0-9a-z]+$/', $inputTx)) {
	$error = error('txidエラー2');
}
try {
	$transaction = $coind->gettransaction($inputTx);
} catch (Exception $e) {$error = error('txidエラー3');}
$inputWatanabe = 0;
foreach ($transaction['details'] as $key) {
	if ($key['account'] == 'write') {$inputWatanabe += $key['amount'] * 100000000;$inputIndex = $key['vout'];}
}
if ($inputWatanabe < 2000000) {
	$error = error('残高エラー');
}

$inputPay = filter_input( INPUT_POST, 'pay' );
if ($inputPay >= 0.01) {
	$payWatanabe = $inputPay * 100000000;
} else {
	$payWatanabe = 0;
}

$feeWatanabe = 1000000;
$totalOutputWatanabe = $inputWatanabe - $payWatanabe - $feeWatanabe;
if (!($totalOutputWatanabe >= 0)) {
	$error = error('残高エラー');
}

$outputMona = filter_input( INPUT_POST, 'output' );
$outputWatanabe = $outputMona * 100000000;
$outputAddress = filter_input( INPUT_POST, 'address' );
if ($outputAddress == '') {$outputAddress = $changeAddress;}
else if (!preg_match('/^[0-9a-zA-Z]+$/', $outputAddress)) {$error = error('アドレスエラー1');} 
else if (mb_strlen($outputAddress,"utf-8") != 34) {$error = error('アドレスエラー2');}
else {
	if ($outputWatanabe >= 1) {$outputToAddress = $outputWatanabe;} 
		else {$outputToAddress = $totalOutputWatanabe * 4 / 5;}
	$totalOutputWatanabe -= $outputToAddress;
}
$changeWatanabe = $totalOutputWatanabe;

$output = 1;
$rawTransactionOfOutput = '';

$inputText = filter_input( INPUT_POST, 'text' );
if ($inputText == '') {$error = error('textエラー');}
else if (strlen(bin2hex($inputText))>160) {$error = error('textエラー');} 

if (isset($error)) {echo $error;exit();}


if ($outputToAddress >= 1) {
	$output += 1;
	$outputValiAddr = $coind->validateaddress($outputAddress);
	$outputScriptPubKeyByte = 25;
	$outputScriptPubKey = $outputValiAddr['scriptPubKey'];
	$rawTransactionOfOutput .= LittleEndian(EncDec($outputToAddress,16));
	$rawTransactionOfOutput .= LittleEndian(EncDec($outputScriptPubKeyByte));
	$rawTransactionOfOutput .= $outputScriptPubKey;
}

if ($changeWatanabe >= 1) {
	$output += 1;
	$changeValiAddr = $coind->validateaddress($changeAddress);
	$changeScriptPubKeyByte = 25;
	$changeScriptPubKey = $changeValiAddr['scriptPubKey'];
	$rawTransactionOfOutput .= LittleEndian(EncDec($changeWatanabe,16));
	$rawTransactionOfOutput .= LittleEndian(EncDec($changeScriptPubKeyByte));
	$rawTransactionOfOutput .= $changeScriptPubKey;
}

$rawTransactionOfOutput .= LittleEndian(EncDec($payWatanabe,16));
$rawTransactionOfOutput .= CreateOP_RETURNScript($inputText);

$scriptSigByte = 0;
$scriptSig = '';
$sequenceTerminalSymbol = 'ffffffff';
$locktime = 0;

$rawTransaction = '';
$rawTransaction .= LittleEndian(EncDec($var,8));
$rawTransaction .= LittleEndian(EncDec($input,2));
$rawTransaction .= LittleEndian($inputTx);
$rawTransaction .= LittleEndian(EncDec($inputIndex,8));
$rawTransaction .= LittleEndian(EncDec($scriptSigByte,2));
//$rawTransaction .= $scriptSig;
$rawTransaction .= LittleEndian($sequenceTerminalSymbol);
$rawTransaction .= LittleEndian(EncDec($output,2));

$rawTransaction .= $rawTransactionOfOutput;

$rawTransaction .= LittleEndian(EncDec($locktime,8));
//echo $rawTransaction.'<br>';
try {
	$signedRawTransaction = $coind->signrawtransaction($rawTransaction);
	$txid = $coind->sendrawtransaction($signedRawTransaction['hex']);
	echo '送信完了<br>txid:'.$txid;
} catch (Exception $e) {
	$error = error('エラー');
}
if (isset($error)) {echo $error;exit();}

?>
<br><br><a href="./write.html">writeトップページ</a>