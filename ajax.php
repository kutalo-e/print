<?php

function get_balance($user_id){
    global $wpdb;

    $user_balance = $wpdb->get_var("
            SELECT balance FROM {$wpdb->prefix}print_users
            WHERE 
                `user_id`='$user_id'
             ");
    return $user_balance;
}

// Обработчик кнопки "Печать"
add_action('wp_ajax_print_form_submit', 'print_form_submit_callback');
function print_form_submit_callback() {
    if (wp_verify_nonce($_POST['print_form_field'], 'print_form')) {//защита от несанкционированных запросов (проверяем
        //та ли форма отправила запрос )

        global $wpdb; //расширяем область видимости переменной бд

        $params = array();

        $params['id'] = intval($_POST['id']);
        $params['pages'] = $_POST['pages'];
        $params['custom_pages'] = $_POST['custom_pages'];
        $params['color'] = $_POST['color'];
        $params['size'] = $_POST['size'];
        $params['type-print'] = $_POST['type-print'];
        $params['number_of_copies'] = $_POST['number_of_copies'];
        $params['urgently'] = $_POST['urgently'];//срочная печать
        $params['type-deliver'] = $_POST['type-deliver'];//тип доставки
        $params['phone_number'] = $_POST['phone_number'];//тип доставки
        $params['users_comment'] = $_POST['users_comment'];//тип доставки
        $new_balance = '';
        $answer = array();

        $return = print_get_cost_and_count($params['id'], $params['pages'], $params['custom_pages'], $params['color'], $params['size'], $params['type-print'], $params['number_of_copies'], $params['urgently'], $params['type-deliver'], false);

        if ($params['urgently']){
            $params['urgently'] = 'Yes';
        }
        else {
            $params['urgently'] = 'No';
        }

        //проверяем, есть лиденьги
        if ($return['balance'] >= $return['money']) {
            $new_balance = $return['balance'] - $return['money'];
            $money = $return['money'];
            $count_pages = $return['pages'];//количество страниц для печати
            $id_user = wp_get_current_user()->ID;

            //всталяем строку в таблицу "print_money" об оперции
            $wpdb->query("
                INSERT {$wpdb->prefix}print_money SET
                    id_user='$id_user',
                    type = 'uhod',
                    value = '$money',
                    date='". date('Y-m-d H:i:s') ."'
                    "
            );

            //обновляем баланс пользователя в таблице "wp_print_users"
            $wpdb->query("
                UPDATE {$wpdb->prefix}print_users SET
                    balance = '$new_balance'
                    where user_id='$id_user'
            ");

            //обновлям таблицу "wp_print" поле payment
            $wpdb->query("
                UPDATE {$wpdb->prefix}print SET
                    payment = 'paid',
                    pages='$count_pages',
                    color='{$params['color']}',
                    size='{$params['size']}',
                    type_print='{$params['type-print']}',
                    number_of_copies='{$params['number_of_copies']}',
                    urgently='{$params['urgently']}',
                    type_deliver='{$params['type-deliver']}',
                    phone_number='{$params['phone_number']}',
                    comment='{$params['users_comment']}',
                    finished=0,
                    price='$money',
                    `params`='". base64_encode(serialize($params)) ."'
                    where user_id='$id_user' and id='{$params['id']}'
            ");

            //получаем путь к файлу
            $file_path = $wpdb->get_var("
                SELECT file_path FROM {$wpdb->prefix}print
                WHERE 
                    id='{$params['id']}'
            ");

            //получаем логин пользователя
            $user_login = wp_get_current_user()->user_login;
            $user_nicename = wp_get_current_user()->user_nicename;
            $user_email = wp_get_current_user()->user_email;

            $mail_result = print_send_mail(
                $file_path,
                $user_login,
                $user_nicename,
                $user_email,
                $params['pages'],
                $params['custom_pages'],
                $params['color'],
                $params['size'],
                $params['type-print'],
                $params['number_of_copies'],
                $params['urgently'],
                $params['type-deliver'],
                $params['phone_number'],
                $params['users_comment']
            );

            $answer['status'] = $mail_result;
            echo json_encode($answer);
//            echo $mail_result;
            //echo 1;
        } else {
//            echo 0;
            $answer['status'] = 0;
            $answer['balance'] = $return['balance'];
            $answer['money'] = $return['money'];
            $answer['difference'] = $return['money'] - $return['balance'];
            echo json_encode($answer);
        }
        die;
    }
}

// Отправка письма
function print_send_mail($file_path, $user_login, $user_nicename, $user_email, $pages, $custom_pages, $color, $size, $type_print,$number_of_copies, $urgently, $type_deliver, $phone_number, $users_comment) {
    $headers[] = 'From:  amactive.online <no-replace@amactive.online>';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $numbers_of_pages = ' '; // номера страниц для печати

    $attachments[] = $file_path;

    if ($pages == 'custom') {
        $numbers_of_pages = $custom_pages;
    }
    else if ($pages == 'all') {
        $numbers_of_pages = 'Все';
    }

    if ($color == 'mono') {
        $color = 'Черно-белая печать';
    }
    else if ($color == 'color') {
        $color = 'Цветная печать';
    }

    if ($type_print == 'one-side') {
        $type_print = 'Односторонняя печать';
    }
    else if ($type_print == 'both-side') {
        $type_print = 'Двустороняя печать';
    }

    if ($urgently == 'Yes') {
        $urgently = 'Cрочная печать';
    }
    else if ($urgently == 'No') {
        $urgently = 'Не срочная печать';
    }

    if ($type_deliver == 'pickup-2-807') {
        $type_deliver = 'Заберут из кабинета 2-807';
    }
   else {
        $type_deliver = 'Доставка';
    }

    $message ='<h3> Регистрационные данные пользователя </h3> Логин: '.$user_login.'<br>Никнейм: '
        .$user_nicename
        .'<br>e-mail: '.$user_email
        .'<br>Телефон: '.$phone_number
        .'<h3>Параметры документа: </h3> Адрес:'.$file_path
        .'<br>Страницы: '.$numbers_of_pages
        .'<br> Цвет печати: '.$color.
        '<br> Размер: '.$size
        .'<br> Тип печати: '.$type_print
        .'<br> Количество копий: '.$number_of_copies
        .'<br> Срочность: '.$urgently
        .'<br> Доставка: '.$type_deliver
        .'<br> Комментарий: '.$users_comment;

    if (!wp_mail('amactive.online@gmail.com', 'Заказ онлайн-печать', $message, $headers, $attachments)){
        return 2;
    }
    else return 1;
}

// обработчик js: Server_refresh
add_action('wp_ajax_print_form', 'print_form_callback');//1-й параметр post переменная с name=action  и value print_form,
// 2-ой параметр - название обработчика (функции)
function print_form_callback() {
    if (wp_verify_nonce($_POST['print_form_field'], 'print_form')) {//защита от несанкционированных запросов (проверяем
        //та ли форма отправила запрос )
        global $wpdb; //расширяем область видимости переменной бд
//sleep(4);
//        print_r($_POST);
        $params = array();

        $params['id'] = intval($_POST['id']);
        $params['pages'] = $_POST['pages'];
        $params['custom_pages'] = $_POST['custom_pages'];
        $params['color'] = $_POST['color'];
        $params['size'] = $_POST['size'];
        $params['type-print'] = $_POST['type-print'];
        $params['number_of_copies'] = $_POST['number_of_copies'];
        $params['urgently'] = $_POST['urgently'];//срочная печать
        $params['type-deliver'] = $_POST['type-deliver'];//тип доставки
        $params['phone_number'] = $_POST['phone_number'];//тип доставки
        $params['users_comment'] = $_POST['users_comment'];//тип доставки

        $return = print_get_cost_and_count($params['id'], $params['pages'], $params['custom_pages'], $params['color'], $params['size'], $params['type-print'], $params['number_of_copies'], $params['urgently'], $params['type-deliver'], false);

        $wpdb->query("
            UPDATE `{$wpdb->prefix}print` SET
                `price` = '". $return['money'] ."',
                `params` = '". base64_encode(serialize($params)) ."'
            WHERE
                id='{$params['id']}'"
        );
//        $return = array(
//            'money' => $order_cost,
//            'pages' => $count_selected_pages,
//            'balance' => get_balance(wp_get_current_user()->ID)
//        );

        array_push($return, $params);

        echo json_encode($return);
        die;
    }
}

// Пополнение средств через "промокод"
add_action('wp_ajax_print_form_put_money', 'print_form_put_money_submit');//1-й параметр post переменная с name=action  и value print_form,
// 2-ой параметр - название обработчика (функции)
function print_form_put_money_submit() {
    if (wp_verify_nonce($_POST['print_form_put_money_field'], 'print_form_put_money')) {//защита от несанкционированных запросов (проверяем
        //та ли форма отправила запрос )
        global $wpdb; //расширяем область видимости переменной бд

//        print_r($_POST);
        $users_promo = addslashes($_POST['users_promo']);//код, который ввел пользователь
        $id_user = wp_get_current_user()->ID;
        $nominal ='';//номинал кода
        $new_balance = '';
        $promo_id = '';
        $return_ = '';

        $result = $wpdb->get_row("
            SELECT * FROM `{$wpdb->prefix}print_codes`
            WHERE 
                `value` = '$users_promo' AND
                `status` != 'used'
             ", ARRAY_A
        );

        if (is_array($result) AND count($result)) :
            $nominal = $result['nominal'];
            $new_balance = $nominal + get_balance(wp_get_current_user()->ID);
            $promo_id = $result['id'];

            $user_exist = $wpdb->get_var("SELECT `user_id` FROM `{$wpdb->prefix}print_users` WHERE `user_id`='$id_user'");
            $user_pay = false;

            if ($user_exist) {
                //обновляем таблицу юзера
                $user_pay = $wpdb->query("
                    UPDATE `{$wpdb->prefix}print_users` SET
                        `balance` = '$new_balance'
                    where 
                        `user_id`='$id_user'
                ");
            } else {
                //добавляем строчку в таблицу wp_print_money (отследить приход уход денег)
                $user_pay = $wpdb->query("
                INSERT `{$wpdb->prefix}print_users` SET
                    `user_id`='$id_user',
                    `balance` = '$new_balance'
                    ");
            }

            if ($user_pay) {
                //обовляем эту таблицу (коды)
                $wpdb->query("
                UPDATE `{$wpdb->prefix}print_codes` SET
                    `status` = 'used',
                    `user_id` = '$id_user',
                    `date`='". date('Y-m-d H:i:s') ."'
                     where `id`='$promo_id'
                ");

                //добавляем строчку в таблицу wp_print_money (отследить приход уход денег)
                $wpdb->query("
                INSERT `{$wpdb->prefix}print_money` SET
                    `id_user`='$id_user',
                    `promocode_id`='$promo_id',
                    `title` = 'Промокод',
                    `type` = 'prihod',
                    `value` = '$nominal',
                    `date`='". date('Y-m-d H:i:s') ."'
                    ");
            }

            $return_ = 1;

        else:
            $return_ = 0;
        endif;

        print_r($return_);
        die;
    }
}

// Зачисление средств на счёт. Например через Webmoney
function print_put_money ($id_user, $nominal, $method_title = 'online') {
    global $wpdb;

    if (($nominal < 1) OR ($id_user < 1)) {
        echo "!!!!";
        return false;
    }

    $id_user = intval($id_user);
    $nominal = intval($nominal);
    $method_title = addslashes($method_title);
    $user_pay = false;

    $new_balance = $nominal + get_balance($id_user);
    $user_exist = $wpdb->get_var("SELECT `user_id` FROM `{$wpdb->prefix}print_users` WHERE `user_id`='$id_user'");
//    echo "\$id_user = $id_user\n";
//    echo "\$user_exist = $user_exist\n";
//    echo "\$new_balance = $new_balance\n";

    if ($user_exist) {
        //обновляем таблицу юзера
        $user_pay = $wpdb->query(
            "UPDATE `{$wpdb->prefix}print_users` SET
                `balance` = '$new_balance'
             where 
                `user_id`='$id_user'"
        );
    } else {
        //добавляем строчку в таблицу wp_print_money (отследить приход уход денег)
        $user_pay = $wpdb->query("
            INSERT `{$wpdb->prefix}print_users` SET
                `user_id`='$id_user',
                `balance` = '$new_balance'
        ");
    }

    //добавляем строчку в таблицу wp_print_money (отследить приход уход денег)
    if ($user_pay) {
        $wpdb->query("
                INSERT `{$wpdb->prefix}print_money` SET
                    `id_user`='$id_user',
                    `title` = '$method_title',
                    `type` = 'prihod',
                    `value` = '$nominal',
                    `date`='". date('Y-m-d H:i:s') ."'
                    ");
    }

    return $user_pay;
}

// Подсчёт стоимости печати по входным параметрам
function print_get_cost_and_count($id, $pages = 'all', $custom_pages = false, $color = 'mono', $size = 'A4', $type_print = 'one-side', $number_of_copies = 1, $urgently = false, $type_deliver = 'pickup-2-807', $db = true) {
    global $wpdb; //расширяем область видимости переменной бд

    $count_selected_pages = 0; // общее кол-ва выбранных страниц
    $selected_pages = array();
    $cost_one_page = get_option('cost_one_page'); // стоимость одной страницы
    $order_cost = ''; // стоимость всего заказа


    $session = $wpdb->get_results("
        SELECT * FROM `{$wpdb->prefix}print`
        WHERE 
            `id`='$id'
         ", ARRAY_A
    )[0];

    if ($db AND $session['params']) {
        $params = unserialize(base64_decode($session['params']));
        $pages = $params['pages'];
        $custom_pages = $params['custom_pages'];
        $color = $params['color'];
        $size = $params['size'];
        $type_print = $params['type_print'];
        $number_of_copies = $params['number_of_copies'];
        $urgently = $params['urgently'];
        $type_deliver = $params['type_deliver'];
    }

    // Подсчёт кол-ва выбранныых страниц (1-5, 8, 11-13)
    if ($pages == 'custom') {
        $custom_pages_arr = explode(",", $custom_pages);
        array_push($custom_pages_arr, '');
        foreach ($custom_pages_arr as $custom_page) {
            if (!$custom_page) {
                continue;
            }
            $custom_page = str_replace(' ', '', $custom_page);
            if (strpos($custom_page, '-') === false) {
                if (($custom_page <= $session['max_pages']) and ($selected_pages[$custom_page] != 1)) {
                    $count_selected_pages++;
                    $selected_pages[$custom_page] = 1;
                }
            } else {
                if (preg_match('/([0-9]{1,})\-([0-9]{1,})/', $custom_page, $matches)) {
                    $c_mim = intval($matches[1]);
                    $c_mim = ($c_mim)?$c_mim:1;
                    $c_max = intval($matches[2]);
                    if (!$c_max and ($selected_pages[$c_mim] != 1)) {
                        $count_selected_pages++;
                        $selected_pages[$c_mim] = 1;
                    } else {
                        if ($session['max_pages'] > 1) {
                            if ($c_mim < $c_max) {
                                while (($c_mim <= $c_max) and ($c_mim <= $session['max_pages'])) {
                                    if ($selected_pages[$c_mim] != 1) {
                                        $count_selected_pages++;
                                        $selected_pages[$c_mim] = 1;
                                    }
                                    $c_mim++;
                                }
                            }
                        } else {
                            if ($selected_pages[1] != 1) {
                                $count_selected_pages++;
                                $selected_pages[1] = 1;
                            }
                        }
                    }
                } else {
                    if (intval($custom_page) and (abs(intval($custom_page)) <= $session['max_pages']) and ($selected_pages[intval($custom_page)] != 1)) {
                        $count_selected_pages++;
                        $selected_pages[intval($custom_page)] = 1;
                    }
                }
            }
        }
    } else {
        $count_selected_pages = $session['max_pages'];
    }

	
    // размер бумаги
    if ($size == 'A3') {
        $cost_one_page = get_option('added_value_A3'); // добавочная стоимость формата печати
    } elseif ($size == 'A2') {
        $cost_one_page = get_option('added_value_A2'); // добавочная стоимость формата печати
    } elseif ($size == 'A5') {
        $cost_one_page = get_option('added_value_A5'); // добавочная стоимость формата печати
    }
	
    // цвет бумаги
    if ($color == 'color') {
        $cost_one_page += get_option('added_value_color'); // добавочная стоимость цветной печати
    }

    // двусторонняя печать
    if ($type_print == 'both-side') {
        $cost_one_page *= get_option('added_value_both_side'); // добавочная стоимость двусторонней печати
    }


    // тип бумаги
    // TODO добавить тип бумаги: ксероксная, фотобумага матовая и тп.

    // Срочная печать
    if ($urgently and (($size == 'A4') or ($size == 'A5'))) {
        $cost_one_page *= get_option('added_value_urgently');
    }

    // количество копий
    if ($number_of_copies > 1) {
        $cost_one_page *= $number_of_copies;
    }

    //считаем цену заказа без доставки
    $order_cost = $count_selected_pages * $cost_one_page;

    // есть ли доставка
    if ($type_deliver == 'delivery') {
        $order_cost += get_option('added_value_delivery');
    }

    // echo $count_selected_pages * $cost_one_page;

    $return = array(
//        'arr' => "
//        SELECT * FROM `{$wpdb->prefix}print`
//        WHERE
//            `id`='$id'
//         ",
        'max' => $session['max_pages'],
        'money' => $order_cost,
        'pages' => $count_selected_pages,
        'balance' => get_balance(wp_get_current_user()->ID)
    );

    return $return;
}

// функция на открытие модального окна
// и сохрание файла
add_action('wp_ajax_print_user', 'print_user_callback');
//add_action('wp_ajax_nopriv_print_user', 'print_user_callback');
function print_user_callback() {
    if (wp_verify_nonce($_POST['print_user_field'], 'print_user')) {
        global $wpdb;
        //    print_r(json_encode($_POST));
//    print_r($_FILES['files']);
        if (!empty($_FILES['files'])) {
            $find_correct_type = false;
            $name = $_FILES['files']['name'];

            if (preg_match('/(\.pdf$)|(\.jpg$)|(\.png$)|(\.jpeg$)/i', $name, $matches)) {
                $find_correct_type = true;
                $format = $matches[0];
            }

            if (!$find_correct_type and !$format) {
                die ('Error. Not find correct file');
            }

            $UserClass = new PrintUserClass();

            $tmp_name = $_FILES['files']['tmp_name'];
            $path = 'print-files/';
            $abs_path = ABSPATH . $path;
            $user_login = wp_get_current_user()->user_login;
            $user_id = wp_get_current_user()->ID;
            $file_name = $UserClass->get_unique_file_name($abs_path, $format, $user_login);
            $full_path = $abs_path . $file_name;
            copy($tmp_name, $full_path);

            if ($format == '.pdf') {
                $pages = $UserClass->getNumPagesPdf($full_path);
            } else {
                $pages = 1;
            }

            $url = get_site_url() . '/' . $path . $file_name;

            $name = addslashes($name);
            $max_id = $wpdb->get_var("SELECT MAX(id) FROM {$wpdb->prefix}print") + 1;

            $wpdb->query("
                INSERT `{$wpdb->prefix}print` SET
                    id='$max_id',
                    user_id='$user_id',
                    date='". date('Y-m-d H:i:s') ."',
                    file_path='$url',
                    file_title='$name',
                    payment='none',
                    max_pages='$pages'"
            );

	        $return_ = print_get_cost_and_count($max_id);

	        $wpdb->query("
                UPDATE `{$wpdb->prefix}print` SET
                    price='{$return_['money']}'
                WHERE
                    id='$max_id'"
	        );

            $return = array(
                'url' => $url,
                'max_pages' => $pages,
                'id' => $max_id,
                'balance' => get_balance(wp_get_current_user()->ID)
            );

            $return = array_merge($return, $return_);

            echo json_encode($return);
        }
        die;
    }
}