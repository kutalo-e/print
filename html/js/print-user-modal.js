(function ($) {
    if (!$) {
        console.error('jQuery and $ not exist');
        return;
    }
	

    //функция для поворота стрелочек(очень важная(нет))
    function rotate_arrows(on_off) {
        var $refresh_arrow = $('.refresh-arrow');

        if (on_off === 'on') {
            $refresh_arrow.addClass('arrow-rotate');
        } else {
            $refresh_arrow.removeClass('arrow-rotate');
        }
    }

    //сравниваем стоимость и баланс
    function check_balance (user_balance, money) {
        var $no_money_message = $('.no_money_message'),
            $print__btn_print = $('.print__btn-print');
        if (user_balance < money){
            $no_money_message.addClass('show_message');
            $print__btn_print.text("Пополнить баланс");
            return -1;
        }
        else {
            $no_money_message.removeClass('show_message');
            $print__btn_print.text("Печать");
            return 1;
        }
    }
    window.check_balance = check_balance;

    // Only number
    $(function () {
        var $charset_mask = $('.charset-mask'),
            $only_pages = $('.only-pages');

        $charset_mask.keydown(function (event) {
            var $this = $(this);

            // Разрешаем: backspace, delete, tab и escape
            if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 ||
                // для страниц
                (($this.data('mask') == 'pages') && (event.keyCode == 116 || event.keyCode == 32 || event.keyCode == 109 || event.keyCode == 188)) ||
                // для телефона
                (($this.data('mask') == 'tel') && (event.keyCode == 116 || event.keyCode == 32 || event.keyCode == 109 || event.keyCode == 107 || event.keyCode == 16 || event.keyCode == 48)) ||
                // Разрешаем: Ctrl+A
                (event.keyCode == 65 && event.ctrlKey === true) ||
                // Разрешаем: home, end, влево, вправо
                (event.keyCode >= 35 && event.keyCode <= 39)) {
                // Ничего не делаем
                return;
            } else {
                // Убеждаемся, что это цифра, и останавливаем событие keypress
                if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
                    event.preventDefault();
                }
            }
        });

        $only_pages.keydown(function (event) {
            console.log(event.keyCode);
        });
    });

    // close modal
    $(function () {
        var $print__close = $('.print__close');

        $print__close.on('click', function () {
            var $body = $('body');

            $body.removeClass('print-open').removeClass('is-success');
        });
    });

    //обработчик кнопки "пополнить баланс" в личном кабинете
    $(function () {
        var $button__put_money = $('.button__put-money'),
            $body = $('body');

            $button__put_money.on('click', function () {
            $body.addClass('no-display-mess').addClass('open_put_money');
            //$btn__primary.attr('disabled', false);
        });

    });

    //close modal_put_money
    $(function () {
        var $modal_put_money__bg = $('.modal_put_money__bg'),
            $body = $('body'),
            $modal_success_message__bg =$('.modal_success_message__bg'),
            $print__btn_print = $('.print__btn-print');

        $modal_put_money__bg.on('click', function () {
            $('#appendedInputButton').val('');
            $body.removeClass('open_put_money').removeClass('no-display-mess');
            $print__btn_print.attr('disabled', false);
        });
        $modal_success_message__bg.on('click', function () {
            $body.removeClass('open_success_message');
        });
    });

    //close modal_put_money
    function close_modal_put_money() {
        var $body = $('body'),
        $print__btn_print = $('.print__btn-print');

        $body.removeClass('open_put_money');
        $('#appendedInputButton').val('');
        $print__btn_print.attr('disabled', false);
    }

    // close modal
    function close_window () {
        var $print__close = $('.print__close');
        var $body = $('body');
        $body.removeClass('print-open').removeClass('is-success');
    }


    // обработчик кнопки печать
    $(function () {
        var $body = $('body'),
			  $btn_print = $('.btn-print');

        $btn_print.on ('click', function (){
            var $this = $(this),
                $print_form = $('.print-form'), // modal form
                $action = $('[name="action"]', $print_form), //???
                action_value = $action.val(),
				$input_delivery__delivery = $('.input-delivery--delivery'),//чекбокс доставка
				$print__phone_number = $('.print__phone-number'),//номер телефона
				$print__users_comment = $('.print__users-comment');//поле комментарий
				
				//проверка- если доставка отмечена но нет телефона или комментария - не печатаем
				 if ($input_delivery__delivery.is(':checked') && ($print__phone_number.val()=='' || $print__users_comment.val()==''))
				 {
					 $print__phone_number.addClass('empty-field');
					 $print__users_comment.addClass('empty-field');
					 return ;
				 } 

            if ($body.hasClass('is-print-uploading-bottom')) { //пока не отправили один запрос, чтобы небыло дугих запросов
                return;
            }

            if ($this.prop('disabled')) {
                return;
            }

            $action.val('print_form_submit');

            //FormData - собирает все поля формы и формирует объект (ассоциативный массив) со всеми полями формы. Нативная
            var ajaxData = new FormData($print_form.get(0));//get(0) делает из выборки jQ выбрку js

            $action.val(action_value);

            $.ajax({// запрос на сервер "на лету"
                url: 			print_form.url, // http адрес скрипта куда отправляется запрос,
                type:			'POST',
                data: 			ajaxData,
                dataType:		'json', //формат возвращаемых сервером данных
                cache:			false,
                contentType:	false,
                processData:	false,
                beforeSend: function()
                {
                    $this.attr('disabled', 'true');
                    $body.addClass('is-print-uploading-bottom');
                },
                success: function(data) //data - ответ сервера
                {
                    //close_window ();
                    if (data.status == 0){
                        // необходимо пополнить баланс
                        var $to_webmoney__input = $('.to_webmoney__input');

                        // Назначить минимальный платёж для пополнения счёта
                        if (data.difference > 0) {
                            // console.log(data.difference);
                            $to_webmoney__input.data('min', data.difference);
                            $to_webmoney__input.val(data.difference);
                            min_pay_fix();
                        }

                        //открываем модальное окошко пополнения баланса
                        $body.addClass('open_put_money');
                    } else if (data.status == 1) {
                        // $this.attr('disabled', 'true');
                        server_refresh();
                        close_window();
                        $body.addClass('open_success_message');
                    } else if (data.status == 2){
                        $this.attr('disabled', 'false');
                        console.log('mail error');
                        server_refresh();
                    }
                    $body.removeClass('is-print-uploading-bottom');
                    // console.log(data);

                },
                error: function (xhr, ajaxOptions, thrownError) { // в случае неудачного завершения запроса к серверу
                    console.error('print-btn-error-@11: '+xhr.status); // покажем ответ сервера
                    console.error('print-btn-error-@12: '+thrownError); // и текст ошибки
                }
            });
        });
    });

    // обработчик кнопки Пополнить на форме пополнения баланса
    $(function () {
        var $body = $('body'),
            $print_form_put_money = $('.print_form_put_money');// modal form

        $print_form_put_money.on('submit', function (){
            var $this = $(this),
                $error_put_money = $('.error_put_money'),
                $success_put_money = $('.success_put_money');

            //скрываем все сообщения от прошлых операций
            $error_put_money.removeClass('show_message');
            $success_put_money.removeClass('show_message');

            if ($body.hasClass('is-print-uploading-bottom')) { //пока не отправили один запрос, чтобы небыло дугих запросов
                return false;
            }
            //FormData - собирает все поля формы и формирует объект (ассоциативный массив) со всеми полями формы. Нативная
            var ajaxData = new FormData($this.get(0));//get(0) делает извыборки jQ выбрку js
            
            $.ajax({// запрос на сервер "на лету"
                url: 			print_form.url,//print_form_put_money.url, // http адрес скрипта куда отправляется запрос,
                type:			'POST',
                data: 			ajaxData,
                dataType:		'text', //формат возвращаемых сервером данных
                cache:			false,
                contentType:	false,
                processData:	false,
                beforeSend: function()
                {
                    $body.addClass('is-print-uploading-bottom');
                },
                success: function(data) //data - ответ сервера
                {
                    //TODO сделать проверку успешно зачисл или илинет, вывести сообщение
                    // console.log(data);

                    if (data!=1) {
                        $error_put_money.addClass('show_message');
                    }
                    else{
                        $success_put_money.addClass('show_message');
                        server_refresh();
                    }
                    //close_modal_put_money(); // закрываем окно со вводомкода
                    // console.log(data);
                    $body.removeClass('is-print-uploading-bottom');
                },
                error: function (xhr, ajaxOptions, thrownError) { // в случае неудачного завершения запроса к серверу
                    console.error('print-btn-error-@11: '+xhr.status); // покажем ответ сервера
                    console.error('print-btn-error-@12: '+thrownError); // и текст ошибки
                }
            });
            return false;
        });
    });

    // types in custom field
    $(function () {
        var $custom_pages__text = $('.custom-pages--text'), // input with Pages
            $custom_pages__radio = $('.custom-pages--radio'), // radio btn
            $print__size = $('.print__size'), // select SIZE: A4, A3,..
            $keyup = $('.custom-pages--text, .number-of-copies, .print__phone-number, .print__users-comment'), // fields with keypress event
            $change = $('.print-form [type="radio"], .print-form [type="checkbox"], .print-form select'),// fields with keypress event
            $phone_number = $('.print__phone-number'),
            $print__users_comment = $('.print__users-comment'),
            $type_deliver = $("input[name='type-deliver']"),
            $input_delivery__shiping = $('.input-delivery--shiping');

        $keyup.on('keyup', function () {
            server_refresh();
        });
        $change.on('change', function () {
            server_refresh();
        });

        // делаем поле "телефон" и "комментарий" обязательными, если выбрана доставка
        $type_deliver.on('change', function () {
            if ($input_delivery__shiping.prop("checked")) {
                $phone_number.addClass('empty-field'); 
                $print__users_comment.addClass('empty-field');
            } else {
                $phone_number.removeClass('empty-field');
                $print__users_comment.removeClass('empty-field');
            }
        });  



        $print__size.on('change', function () {
            var $this = $(this),
                $print__urgently = $('.print__urgently');

            if (($this.val() != 'A4') && ($this.val() != 'A5')) {
                $print__urgently.hide();
            } else {
                $print__urgently.show();
            }
        });

        $custom_pages__text.on('focusout', function () {
            var $this = $(this),
                data_pages = $this.data('pages');

            if ($this.val().length === 0) {
                if (data_pages > 1) {
                    $this.val('1-' + data_pages);
                } else {
                    $this.val(1);
                }
            }
        });
        $custom_pages__text.on('focus', function () {
            $custom_pages__radio.prop("checked", 'true');
        });

        $custom_pages__radio.on('change', function () {
            var $this = $(this);

            if ($this.prop("checked")) {
                $custom_pages__text.focus();
            }
        });
    });

    // Работа с модальным окном
    function open_window (file_url, pages, money, id, balance, params) {
        var $body = $('body'),
            $print__cost = $('.print__cost'), // field with money (rub)
            $print__cost__count = $('.print__page--count'), // count of pages in pdf
            $print__iframe = $('.print__iframe'), // iframe with document
            $custom_pages__text = $('.custom-pages--text'), // input with Pages
            $max_pages__count = $('.max-pages__count'), // всего страниц
            $print__id= $('.print__id'), // input with id of operation
            $user__balance = $('.user__balance'),
            $print_form__inputs = $('.custom-pages--text, .number-of-copies, .print__phone-number'),
            $print__pages__all = $('.print__pages--all'),
            $print__btn_print = $('.print__btn-print'),
            $input_delivery__pickup = $('.input-delivery--pickup'),
            $type_print__one = $('.type-print--one'),
            $fast_print = $('.fast-print'),
            item_placeholder = '1',
            $print__color = $('.print__color'),
            $print__size = $('.print__size');

        // сбрасываем кэшированные значения
        $print_form__inputs.val('');
        $input_delivery__pickup.prop('checked', 'checked');
        $type_print__one.prop('checked', 'checked');
        $print__pages__all.prop('checked', 'checked');
        $fast_print.prop('checked', '');
        $print__btn_print.attr('disabled', false);
        $print__btn_print.prop('disabled', false);

        $body.addClass('print-open');

        $print__cost.html(money); // заменяем содержимое контейнера
        $print__cost__count.html(pages);
        $max_pages__count.html(pages);
        if(balance==''){balance=0;}//если переменная balance пуста - заносим 0 в нее
        $user__balance.html(balance * 1);//выводим баланс при загрузке
        $print__iframe.attr('src', file_url); // заменяем атрибут src
        $print__id.val(id); // заменяем атрибут value

        $print__color.val('mono');
        $print__size.val('A4');

        if (pages === 2) {
            item_placeholder = 'Например: 1-2';
        }
        if ((pages > 2) && (pages <= 4)) {
            item_placeholder = 'Например: 2-' + pages;
        }
        if ((pages > 4) && (pages < 7)) {
            item_placeholder = 'Например: 1-2, 4-' + pages;
        }
        if (pages > 6) {
            item_placeholder = 'Например: 1-2, 4, 6-' + pages;
        }
        $custom_pages__text.attr('placeholder', item_placeholder);
        $custom_pages__text.data('pages', pages);

        // устанавливаем значения из базы данных
        if ((params !== false) && (params.length != 0)) {
            console.log(params);
            var $print__pages__ = $('.print__pages--' + params.pages),
                $type_print__ = $('.type-print--' + params['type-print']),
                $type_deliver__ = $('.input-delivery--' + params['type-deliver']),
                $number_of_copies = $('.number-of-copies'),
                $urgently = $('[name="urgently"]'),
                $custom_pages__text_ = $('.custom-pages--text'), // input with Pages
                // $print__size = $('.print__size'),
                $print__phone_number = $('.print__phone-number'),
                $print__users_comment = $('.print__users-comment');

            $print__pages__.prop('checked', 'checked');
            $print__color.val(params.color);
            $print__size.val(params.size);
            if (params.size == 'A3') {
                var $print__urgently = $('.print__urgently');

                $print__urgently.hide();
            }
            $type_print__.prop('checked', 'checked');
            $number_of_copies.val(params.number_of_copies);
            $urgently.prop('checked', params.urgently);
            $type_deliver__.prop('checked', 'checked');
            $print__phone_number.val(params.phone_number);
            $print__users_comment.html(params.users_comment);
            $custom_pages__text_.data('pages', params.custom_pages);
            $custom_pages__text_.val(params.custom_pages);
        }

        vertical_adapt();
        server_refresh();
    }
    window.open_window = open_window; // window - суперглобальный объект. создаем глобальную переменную openwindow и присваиваем в нее ссылку на фенкцию - чтобы она была выдна в других фйалах

    // адаптивность по вериткали элементов модального окна
    function vertical_adapt () {
        var $print__iframe = $('.print__iframe'), // iframe with document
            $print__body = $('.print__body'), // форма слева со скролом, адаптивная по вертикали
            $print__head = $('.print__head'), //заголовок формы
            windowHeight = window.innerHeight,
            windowWidth = window.innerWidth;
            //высчитываем высоту экрана

		if (windowWidth > 500){
			$print__iframe.height(windowHeight);
			$print__body.height(windowHeight - $print__head.height());  	
		} 
    }
    window.vertical_adapt = vertical_adapt; //

    vertical_adapt ();

    $(window).resize(function() {
        vertical_adapt ();
    });

    // Обновление стоимости услуги
    function server_refresh_code () {
        var $body = $('body'),
            $print_form = $('.print-form'); // modal form

        if ($body.hasClass('is-print-uploading')) { //пока не отправили один запрос, чтобы небыло дугих запросов
            return;
        }

        //FormData - собирает все поля формы и формирует объект (ассоциативный массив) со всеми полями формы. Нативная
        var ajaxData = new FormData($print_form.get(0));//get(0) делает извыборки jQ выбрку js

        $.ajax({// запрос на сервер "на лету"
            url: 			print_form.url, // http адрес скрипта куда отправляется запрос,
            type:			'POST',
            data: 			ajaxData,
            dataType:		'json', //формат возвращаемых сервером данных
            cache:			false,
            contentType:	false,
            processData:	false,
            beforeSend: function()
            {
                $body.addClass('is-print-uploading');
                rotate_arrows('on');
            },
            complete: function()
            {
                $body.removeClass('is-print-uploading');
                rotate_arrows('off');
            },
            success: function(data) //data - ответ сервера
            {
                var $print__cost = $('.print__cost'), // field with money (rub)
                    $print__cost__count = $('.print__page--count'), // count of pages in pdf
                    $user__balance =$('.user__balance');

                // console.log(data);

                $user__balance.html(data.balance * 1); //обновляем баланс

                if(data.money >= 1) {
                    $print__cost.html(data.money); //.html запишет внутрь выборки .print_cost значение data.money
                    $print__cost__count.html(data.pages);
                    // $user__balance.html(data.balance);
                    check_balance (data.balance, data.money);//проверяем, достаточно ли денег для оплаты.
                }

            },
            error: function (xhr, ajaxOptions, thrownError) { // в случае неудачного завершения запроса к серверу
                console.error('server_refresh-error-@11: '+xhr.status); // покажем ответ сервера
                console.error('server_refresh-error-@12: '+thrownError); // и текст ошибки
            }
        });
    }

    var limitExecByInterval = function(fn, time) {
        var lock, execOnUnlock, args;
        return function() {
            args = arguments;
            if (!lock) {
                lock = true;
                var scope = this;
                setTimeout(function(){
                    lock = false;
                    if (execOnUnlock) {
                        args.callee.apply(scope, args);
                        execOnUnlock = false;
                    }
                }, time);
                return fn.apply(this, args);
            } else execOnUnlock = true;
        }
    };
    window.server_refresh = limitExecByInterval(server_refresh_code, 800);// создал поле глобального объекта server_refrech (тоже глобальное) и присвоили ему функцию.

}($ || window.jQuery)); // круглые скобки - экранирование - создание локальной обласи видимости
// end of file