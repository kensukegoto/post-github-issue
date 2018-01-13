<?php
/* 設定 */
    
$gitUser = "githubのユーザー名";
$gitRepo = "不具合報告をするリポジトリ名";
// 認証トークンの発行方法は以下を参照
// https://qiita.com/kz800/items/497ec70bff3e555dacd0
$token = "認証トークン";   
$titIssue = "不具合の概要"; 
$bodyIssue = "不具合の詳細"; 

/* 設定はここまで */

$repo = "https://api.github.com/repos/{$gitUser}/{$gitRepo}/issues";


$issue = json_encode(
    array(
        "title"=>$titIssue,
        "body"=>$bodyIssue
    ),
    JSON_PRETTY_PRINT);

$args = array(
    "repo" => $repo,
    "issue" => $issue,
    "ua" => $gitUser,
    "token"=> $token
);

$res = postGitIssue($args);


$res = json_decode($res,true);
  
function postGitIssue(array $args) {
    
    extract($args);
    
    $header = [
        'Content-Type: application/json',
        'Authorization: token '.$token
    ];
    
    // RETURNTRANSFER exec時に結果出力を回避
	$options = array(
        CURLOPT_URL => $repo,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_POSTFIELDS => $issue,
        CURLOPT_USERAGENT => $ua,
        CURLOPT_RETURNTRANSFER => true
	);
        
	$ch = curl_init();
    
	curl_setopt_array($ch, $options);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}