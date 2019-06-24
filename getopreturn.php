<?php
ini_set( 'display_errors', 0 ); 
require_once(__DIR__ . '/jsonRPCClient.php');
$host = 'localhost';		/* monacoind 又は monacoin-qt を実行中のホストのアドレス */
$rpcuser = 'monacoinrpc';	/* monacoin.conf で指定した rpcユーザー名 */
$rpcpassword = '';		/* monacoin.conf で指定した rpcパスワード */
$rpcport = '19402';			/* monacoin.conf で指定した rpcポート */
$coindaddr = "http://$rpcuser:$rpcpassword@$host:$rpcport/";
$coind = new jsonRPCClient($coindaddr);

$tx = filter_input(INPUT_GET, 'tx');
try{
	$rawTransaction = $coind->getrawtransaction($tx);
} catch (Exception $e) {
	echo 'エラー';
}
$hexOutput = $rawTransaction;
$symbolCount = mb_substr_count($rawTransaction, 'ffffffff');
for ($i=0; $i < $symbolCount; $i++) { 
	$hexOutput = substr($hexOutput, 8);
	$hexOutput = strstr($hexOutput, 'ffffffff');
}
$output = hexdec(substr($hexOutput, 8, 2));
$pointer = 10;
for ($i=0; $i < $output; $i++) { 
	$pointer += 16;
	$divByte = hexdec(substr($hexOutput, $pointer, 2));
	if (substr($hexOutput, $pointer+2, 2)=='6a') {
		$opreturnByte = hexdec(substr($hexOutput, $pointer+4, 2));
		$opreturn = substr($hexOutput, $pointer+6, $opreturnByte * 2);
		$pointer = $pointer + 2 + $divByte * 2;
	} else {
		$pointer = $pointer + 2 + $divByte * 2;
	}
}
if (isset($opreturn)) {	
	echo $tx.'<br>のOP_RETURNは<br><br>';
	echo $opreturn;
	echo '<br>↓<br>';
	echo hex2bin($opreturn);
} else {
	echo 'OP_RETURNを取得できませんでした。';
}
?>
<br><br><br>OP_RETURN取得フォーム<br>
<form action="getopreturn.php" method="get">
  tx
  <input type="text" id="tx" name="tx" maxlength="64"><br>
  <input type="submit" id="submit">
</form>
<br><a href="./write.html">writeトップページ</a>
