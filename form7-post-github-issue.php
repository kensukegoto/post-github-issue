<?php
/* 

form7でフォーム作成時に、
・不具合の件名のnameをtitissue
・不具合の詳細のnameをbodyissue
と設定して下さい。

以下３点（２０行目付近）をハードコーディングして下さい。
・ユーザー名
・リポジトリ名
・認証トークン
*/
add_action( 'wpcf7_submit', function($contact_form) {
    
    $submission = WPCF7_Submission::get_instance();
 
    if ( $submission ) {
        $data = $submission->get_posted_data();
    }
    
    // フォームから受け取る
    $titIssue = $data["titissue"]; 
    $bodyIssue = $data["bodyissue"]; 
    
    $gitUser = "kensukegoto";
    $gitRepo = "post-test";
    // 認証トークンの発行方法は以下を参照
    $token = "";
    
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
    
});

?>