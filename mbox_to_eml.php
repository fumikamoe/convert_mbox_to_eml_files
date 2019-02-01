<?php
// convert huge_one.mbox file to many.eml(or .emlx) files.
// Author uzulla <http://twitter.com/uzulla>

// Edit for laravel Services Container

class ServiceDecomposeMbox
{
    protected static function new_output_file_handle($line, $prefix_dir = './')
    {
        $list = preg_split('/ /', $line, 3);
        try
        {
            $id = $list[1];
            $time = strtotime($list[2]);
            $filename = date("YmdHis", $time) . "_" . $id . ".eml";
            $to_dir = $prefix_dir . "/" . date("Y-m", $time) . '/';
        }
        catch(\Exception $e)
        {
            echo now()." : Mbox File parse error! \n";
            $time = "0000-00";
            $id = "errorid";
            $filename = $time . "_" . $id ."_".substr(md5(time()),5). ".eml";
            $to_dir = $prefix_dir . "/" . $time . '/';
        }
        if (file_exists($to_dir)) {
            if (!is_dir($to_dir))
                echo (now()." : {$to_dir} is file exists. directory create fail.\n");
        } else {
            mkdir($to_dir);
        }

        if (!file_exists($to_dir . $filename)) {
            return [fopen($to_dir . $filename, "w"), $to_dir . $filename];
        } else {
            echo (now()." : {$to_dir}/{$filename} is exists. skipped.\n");
            return [false, null];
        }
    }

    protected static function close_output_file_handle($line, $prefix_dir = './')
    {
        $list = preg_split('/ /', $line, 3);
        $id = $list[1];
        $time = strtotime($list[2]);
        $filename = date("YmdHis", $time) . "_" . $id . ".eml";
        $to_dir = $prefix_dir . "/" . date("Y-m", $time) . '/';
        if (!file_exists($to_dir . $filename)) {
            fclose($to_dir . $filename);
        }
    }

    /*
     * @param string $from_file, string $to_dir
     *
     */
    public static function Decompose($from_file, $to_dir)
    {
        # 매개변수가 3개 이상일경후 0으로 지정
        $skip_header_line = 0;
        $emlx_flag = 0;

        #.mbox 파일을 r모드로 오픈
        $fh = fopen($from_file, "r");
        if (!$fh)
            echo (now()." : can't open from file.\n");
        // writing....
        $counter = 0;
        $current_file_name = null;
        while ($line = fgets($fh)) {
            if (preg_match('/^From /', $line)) { # pattern에 주어진 정규표현식을 subject에서 찾습니다.
                if ($counter++ % 100 === 0) # a와 b가 같은 자료형이고 데이터가 같으면 True
                    echo (now()." : ".$counter."\n");

                # 이전의 쓰기 파일이 열려 있을 경우 닫을 것
                if (isset($oh) && $oh)
                    fclose($oh);

                #현재 지정된 파일 이름이 Null이 아니고 emlx flag가 있을경우
                if (!is_null($current_file_name) && $emlx_flag) {// convert emlx
                    $emlrh = fopen($current_file_name, 'r');
                    $emlxwh = fopen($current_file_name . "x", "w");

                    fwrite($emlxwh, (filesize($current_file_name) + 2) . "\n");
                    while ($eml_line = fgets($emlrh)) {
                        fwrite($emlxwh, $eml_line);
                    }
                    fwrite($emlxwh, "\n\n" . '
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
<key>date-sent</key>
<real></real>
<key>flags</key>
<integer></integer>
<key>sender</key>
<string></string>
<key>subject</key>
<string></string>
<key>to</key>
<string></string>
</dict>
</plist>');
                    # 현재 열린 파일을 지우고 파일 스트림을 닫음
                    unlink($current_file_name);
                    fclose($emlrh);
                    fclose($emlxwh);
                    //ServiceDecomposeMbox::new_output_file_handle($line, $to_dir);
                }

                // start segment. create new file.
                list($oh, $_filename) = ServiceDecomposeMbox::new_output_file_handle($line, $to_dir);
                $current_file_name = $_filename;

                // Skip gmail Special Header, UGLY...
                for ($i = 0; $i < $skip_header_line; $i++)
                {
                    fgets($fh);
                }
                continue;
            }
            if ($oh) { // if false, skip.
                // unescape indented '>From ' to 'From '
                $line = preg_replace('/^>([>]*)From /', "$1From ", $line);
                if ($emlx_flag) // CRLF to LF, I think .emlx is must LF.
                    $line = preg_replace("/\r/", "", $line);
                fwrite($oh, $line);
            }
        }
        echo (now()." : create {$counter} eml files.\n");
        fclose($oh);
    }
}
