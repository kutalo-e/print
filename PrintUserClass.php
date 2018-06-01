<?php
class PrintUserClass
{
    public function get_unique_file_name ($path, $format, $user_login) {
        $ban_chars = array('!','@','#','$','%','^','&','*','(',')','=',' ','?','/','|','\\','>','<','.',',','"',"'",'{','}','[',']','~',':',';');
        $user_login = str_replace($ban_chars,'', $user_login);//замена некорректных символов

        $user_login = print_tr($user_login);

        $file_name = "$user_login{$format}";
        $i = 1;
        while (file_exists($path . $file_name)) {
            $file_name = "$user_login-$i{$format}";
            $i++;
        }
        return $file_name;
    }
    public function getNumPagesPdf ($filename)
    {
        $count_page = shell_exec("pdfinfo $filename | grep Pages ");
        if (preg_match('/([0-9]{1,})/', $count_page, $matches)) {
            $count = intval($matches[1]);
            return $count;
        } else {
            return false;
        }
    }
}