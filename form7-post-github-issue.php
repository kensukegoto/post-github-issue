<?php
/* 
以下５点を各々の環境に合わせてハードコーディングして下さい。
・ユーザー名
・リポジトリ名
・認証トークン
・ブランチ名（マスターブランチを想定）
・画像ディレクトリ
（リポジトリ直下に置くことを想定。
例えば img など　img/wpなど更に深くしてもOKです。）
*/

// 設定
$global_github_conf = array(
    "gitUser" => "ユーザー名",
    "gitRepo" => "リポジトリ名",
    "token" => "トークン",
    "branch" => "ブランチ名", // ex) master
    "imgDir" => "画像ディレクトリ名", // ex )upimage 
);


$global_github_conf["repo"] = "https://api.github.com/repos/{$global_github_conf["gitUser"]}/{$global_github_conf["gitRepo"]}/";

add_action( 'wpcf7_before_send_mail', function($contact_form) {
    

    $submission = WPCF7_Submission::get_instance();
 
    if ( $submission ) {
        $post = $submission->get_posted_data();
        $files = $submission->uploaded_files();
    }
    
    // 設定
    global $global_github_conf;
    extract($global_github_conf);
    
    // テキストの処理
    function prepare_for_markdown($ans){
        
        $gitMarkDown = preg_quote("`|#>:-*_+!.\?",'/');
        // メタ文字のエスケープ
        $ans = preg_replace("/([{$gitMarkDown}])/",'\\\$1',$ans);
        // $ans = trim(preg_replace('/\t/g', '', $ans));
        // $ans = str_replace(PHP_EOL, '<br>', $ans);
        $ans = preg_replace('/(\t|\r\n|\r|\n)/s', '<br>', $ans);
        
        return $ans;
    }
    
    $answer = array_map("prepare_for_markdown", $post);
    
    extract($answer,EXTR_SKIP);
    
    $ans_1 = "|トラブルの内容について|\n";
    $ans_1 .= "|-|\n";
    $ans_1 .= "|**どの機能で、トラブルが起こりましたか？**|\n";
    $ans_1 .= "|{$ans_1_1}|\n";
    $ans_1 .= "|**トラブルは、どのくらいの確率で発生しますか？**|\n";
    $ans_1 .= "|{$ans_1_2}|\n";
    $ans_1 .= "**トラブルの内容を詳しく教えてください**\n";
    $ans_1 .= "|{$ans_1_3}|\n\n";
    
    
    $ans_2 = "|トラブルのあったPC＋Excel環境について|\n";
    $ans_2 .= "|-|\n";
    $ans_2 .= "|**どのOSを使われていますか？**|\n";
    $ans_2 .= "|{$ans_2_1}|\n";
    $ans_2 .= "|**OSのバージョンを詳しく教えてください**|\n";
    $ans_2 .= "|{$ans_2_2}|\n";
    $ans_2 .= "**Excelのバージョンを詳しく教えてください**\n";
    $ans_2 .= "|{$ans_2_3}|\n";
    $ans_2 .= "|**(ExcelOnlineをご利用の場合)どのブラウザを使われていますか？**|\n";
    $ans_2 .= "|{$ans_2_4}|\n";
    $ans_2 .= "** (ExcelOnlineをご利用の場合)ブラウザのバージョンを詳しく教えてください**\n";
    $ans_2 .= "|{$ans_2_5}|\n\n";
    
    $ans_3 = "|編集されていたExcelファイルについて|\n";
    $ans_3 .= "|-|\n";
    $ans_3 .= "|**Excelで操作されていたファイルは、どこに保存されていましたか？**|\n";
    $ans_3 .= "|{$ans_3_1}|\n\n";
    
    $ans_4 = "|その他申し送り事項|\n";
    $ans_4 .= "|-|\n";
    $ans_4 .= "|**その他申し送り事項がございましたら、こちらに記述をお願いします。**|\n";
    $ans_4 .= "|{$ans_4_1}|\n\n";
    
    
    $ans = $ans_1.$ans_2.$ans_3.$ans_4;
    
    
    
    // 画像の処理
    $usr_imgs = $files;
    
    /* 送信下準備から送信までの関数群 */
    //　送信データの作成
    function prepare_for_post_imgs($img){
        
        // 設定項目
        global $global_github_conf;
        extract($global_github_conf);

        $ret = [];
    
        $img_org = file_get_contents($img);
        

        $img_base64 = base64_encode($img_org);
    
        $img_json = json_encode(
            array(
                "message"=>"トラブル報告フォームからの画像添付",
                "content"=>$img_base64
        ),JSON_PRETTY_PRINT);

        $img_name = make_img_name($img);
        
        $ret['core'] = array(
            "repo" => $repo."contents/{$imgDir}/".$img_name,
            "issue" => $img_json,
            "ua" => $gitUser,
            "token"=> $token
        );
        
        $ret['img_path'] = "https://raw.githubusercontent.com/{$gitUser}/{$gitRepo}/{$branch}/{$imgDir}/".$img_name;
        
        return $ret;
        
    }
    
    //　ファイル名の作成
    function make_img_name($img_tmp){

        $mime = mime_content_type($img_tmp);
        $mime = explode('/',$mime);

        list($usec, $sec) = explode(' ', microtime());
        return ($sec + $usec * 1000000).".".$mime[1];
    }
    
    //　画像送信ラッパー関数
    function post_usr_imgs($img){
    
        post_img2github($img['core']);
        
    }
    // 画像送信本体
    function post_img2github(array $args) {

        extract($args);

        $header = [
            'Content-Type: application/json',
            'Authorization: token '.$token
        ];

        // RETURNTRANSFER exec時に結果出力を回避
        $options = array(
            CURLOPT_URL => $repo,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_CUSTOMREQUEST => 'PUT',
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
    
    function set_img_markdown($img){
        $img_path = $img['img_path'];
        return "![]({$img_path})";
    }
    
    $usr_imgs = array_map('prepare_for_post_imgs',$usr_imgs);
    array_walk($usr_imgs,"post_usr_imgs");
    
    // 画像のマークダウンを作成
    $img_markdown = array_map('set_img_markdown',$usr_imgs);
    $img_markdown = array_values($img_markdown);
    
    
    // 画像のマークダウンを追加
    for($i=0,$l=count($img_markdown);$i<$l;$i++){
        
        $str = $img_markdown[$i];
        $str = ($i!==($l-1))?$str."\n\n":$str;
        $ans .= $str;
        
    }
    
    
    // タイトルの作成
    $title_1_3 = str_replace(PHP_EOL, '', $post["ans_1_3"]);
    $title = $ans_1_1." : ".mb_substr($title_1_3,0,60);
    
    
    // 以下、テキストの整形と送信
    $issue = json_encode(
        array(
            "title"=>$title,
            "body"=>$ans
        ),
        JSON_PRETTY_PRINT);

    $issueData = array(
        "repo" => $repo."issues",
        "issue" => $issue,
        "ua" => $gitUser,
        "token"=> $token
    );
    
    
    function post_git_issue(array $args) {


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
    
    $res = post_git_issue($issueData);
    
    

    return;
   
    // file_get_contentsがもし使えない場合
    function get_local_file_contents( $file_path ) {
        
        ob_start();
        include $file_path;
        $contents = ob_get_clean();

        return $contents;
    }
    
});


?>
