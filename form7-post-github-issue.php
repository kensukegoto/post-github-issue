<?php

/* 以下３点（２０行目付近）をハードコーディングして下さい。
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
    
    $gitUser = "ユーザー名";
    $gitRepo = "リポジトリ名";
    // 認証トークンの発行方法は以下を参照
    // https://qiita.com/kz800/items/497ec70bff3e555dacd0
    $token = "認証トークン";
    
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