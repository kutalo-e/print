<?php

function print_form_shortcode ($atts)
{
    extract(shortcode_atts(array(
        ), $atts)
    );

    ob_start();
    //print_r(wp_get_current_user());
    // если пользоватлеь зарегистрирован - показываем ему форму
    if (is_user_logged_in()) :

        ?>
        <div class="tvgag-post">
            <div class="tvgag-post__loader">
                <div class="spinner">
                    <div class="rect1"></div>
                    <div class="rect2"></div>
                    <div class="rect3"></div>
                    <div class="rect4"></div>
                    <div class="rect5"></div>
                </div>
            </div>
            <form action="./" method="post" class="tvgag-post__form " enctype="multipart/form-data">
                <div class="tvgag__files box has-advanced-upload">
                    <div class="box__input">
                        <svg class="box__icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43"><path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"></path></svg>
                        <input class="box__file" type="file" name="files" id="file" data-multiple-caption="{count} files selected" />
                        <label for="file"><strong>Выберите файл .pdf, .png, .jpg</strong><span class="box__dragndrop"> или перетащите его сюда</span>.</label>
                    </div>
                    <div class="box__uploading">Загружается&hellip;</div>
                    <div class="box__success">Выполнено!</div>
                    <div class="box__error">Ошибка!<br>Выберите допустимый формат (JPG, PNG, PDF)</div>
                </div>
                <input id="action" name="action" type="hidden" value="print_user">
                <?php wp_nonce_field('print_user','print_user_field'); ?>
            </form>
        </div>
    <?php else : ?>
        <div class="download-area layout">
            <div class="download-area__box">
                <div class="download-icon__box ">
                    <div class="download-icon "></div>
                </div>
                <div class="download__text not-authorized">Для доступа к сервису <span class="enter like-link like-link--dot">войдите</span> или <span class="registration like-link like-link--dot" target="_blank">зарегистрируйтесь</span></div>
            </div>

        </div><!-- .icons-group -->
    <?
    endif;
    $output = ob_get_contents(); ob_end_clean();
    return $output;
}
add_shortcode('print_form', 'print_form_shortcode');

function print_modal_window_shortcode ($atts)
{

    ob_start();//
    ?>
    <form action="" class="print-form">
        <div class="print">
            <div class="print__padding">
                <div class="print__row print__row--left">
                    <div class="print__head">
                        <div class="print__pad">
                            <!--<div class="print__text--big">Печать</div>-->
                            <div class="text-info all-pages f-14">Выбрано: <b><span class="print__page--count">8</span> лист.</b></div>
                            <div class="print__text--big balance f-14"><span class="balance-text">Ваш баланс:</span> <br><span class="user__balance">20</span> руб. </div>
                            <div class="print__text--big prise">Стоимость: <span class="print__cost" >20</span> руб. <span class="refresh-arrow" onclick="server_refresh();"></span></div>

                            <div class="no_money_message">На вашем счете недостаточно средств. <br>Нажмите "Пополнить баланс" и следуйте инструкциям.</div>
                            <div class="print__buttons">
                                <div class="btn btn-primary btn-print print__btn-print">Печать</div>
                                <div class="btn print__close">Отмена</div>
                            </div>
                        </div>
                    </div>
                    <div class="print__body form-horizontal">
                        <div class="print__pad">
                            <div class="control-group">
                                <div class="control-label">Страницы<br><span class="max-pages">(всего: <span class="max-pages__count">8</span>)</span></div>
                                <div class="controls">
                                    <label class="radio">
                                        <input type="radio" class="print__pages--all" name="pages"  value="all" checked>
                                        Все
                                    </label>
                                    <label class="radio">
                                        <table border="0">
                                            <tr>
                                                <td>
                                                    <input type="radio" class="custom-pages--radio print__pages--custom" name="pages" value="custom">
                                                </td>
                                                <td>
                                                    <input type="text" name="custom_pages" class="span2 custom-pages--text charset-mask" data-mask="pages" placeholder="Например: 1-5, 8, 11-13" data-pages="8">
                                                </td>
                                            </tr>
                                        </table>
                                    </label>

                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">Цвет<br>печати</div>
                                <div class="controls">
                                    <select class="span2 print__color" name="color">
                                        <option value="mono">Черно-белая печать</option>
                                        <option value="color">Цветная печать</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">Размер<br>бумаги</div>
                                <div class="controls">
                                    <select class="span2 print__size" name="size">
                                        <option value="A4" >А4</option>
                                        <option value="A3">А3</option>
                                        <option value="A5">А5</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">Тип печати</div>
                                <div class="controls">
                                    <label class="radio">
                                        <input type="radio" name="type-print" class="type-print type-print--one type-print--one-side" value="one-side" checked>
                                        Односторонняя
                                    </label>
                                    <label class="radio">
                                        <input type="radio" name="type-print" class="type-print type-print--both type-print--both-side" value="both-side">
                                        Двусторонняя
                                    </label>

                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">Количество<br>копий</div>
                                <div class="controls">
                                    <input type="text" name="number_of_copies" class="input-medium number-of-copies charset-mask" id="number-of-copies" placeholder="1">
                                </div>
                            </div>
                            <div class="control-group print__urgently">
                                <div class="control-label">Срочная<br>печать</div>
                                <div class="controls">
                                    <table border="0" >
                                        <tr>
                                            <td class="pad-align "><input name="urgently" type="checkbox" class="fast-print" id="fast-print"></td>
                                            <td><label for="fast-print">Ваш заказ будет готов
                                                в течение 15 минут.
                                                Стоимость -
                                                в 2 раза выше обычной.</label>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">Получение</div>
                                <div class="controls">
                                    <label class="radio">
                                        <input type="radio" name="type-deliver" class="input-delivery input-delivery--pickup input-delivery--pickup-2-807" value="pickup-2-807" checked>
                                        Аудитория 2-807
                                    </label>
                                    <label class="radio">
                                        <input type="radio" name="type-deliver" value="delivery" class="input-delivery input-delivery--shiping input-delivery--delivery">
                                        Доставка (50 руб.)
                                    </label>

                                </div>
                                <div class="control-label" >Номер<br>телефона</div>
                                <div class="controls">
                                    <input type="text" name="phone_number" class="input-medium print__phone-number charset-mask" data-mask="tel" placeholder="+7 999 999 99 99" >
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label" >Ваш<br>комментарий<br>к заказу</div>
                                <div class="controls">
                                    <textarea id="redex" maxlength="200" name="users_comment" class="print__users-comment" rows="4" placeholder="При выборе доставки напишите здесь место доставки и время.(Доставка осуществляется во все корпуса ДГТУ кроме 10.)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="print__row print__row--right">
					<?php if( wp_is_mobile() ) { ?>
						<p>На мобильных устройствах предпросмотр PDF документов не доступен.</p>
						<iframe src="" frameborder="0" class="print__iframe"></iframe>
					<?php }  
					else { ?>
					<iframe src="<?php echo get_stylesheet_directory_uri(); ?>/img/banner-print.jpg" frameborder="0" class="print__iframe"></iframe>
					
					<?php } ?> 
                </div>
            </div>
        </div>
        <input id="action" name="action" type="hidden" value="print_form">
        <input name="id" class="print__id" type="hidden" value="0">
        <?php wp_nonce_field('print_form','print_form_field'); ?>
    </form>
    <?
    $output = ob_get_contents(); ob_end_clean();
    return $output;
}
add_shortcode('print_modal_window', 'print_modal_window_shortcode');

function print_information_shortcode () {
	global $wpdb;

	$id = intval($_GET['id']);
	$id_user = wp_get_current_user()->ID;

	$print = $wpdb->get_row("
        SELECT * FROM `{$wpdb->prefix}print`
        WHERE 
            `id` = '$id'
         ", ARRAY_A
	);
	ob_start();

	if (is_array($print) AND (($print['user_id'] == $id_user) OR (current_user_can('administrator')))) {
	?><div class="table-parent">
            <table border="1" cellspacing="0" class="history-table table__user-orders">
                <thead>
                <tr>
                    <td>Дата</td>
                    <td>Файл</td>
                    <td>Сумма</td>
                    <td>Статус</td>
                    <td>Действия</td>
                </tr>

                </thead>
                <tbody>
		<?php
		$params = serialize(base64_decode($print['params']));
		echo "<tr class=''>
                <td>{$print['date']}</td>
                <td>{$print['file_title']}</td>
                <td><b>{$print['price']}</b> руб.</td>
                <td class='history-table__status'>";
                if ($print['finished'] == '1') {
                    echo "<b>Обработан</b>";
                } elseif ($print['payment'] == 'paid') {
                    echo "Оплачен";
                } else {
                    echo "Не оплачен";
                }
        echo " </td>";
		?>
                </tbody>
            </table>
        </div>

	<?php
    }

	$output = ob_get_contents(); ob_end_clean();
	return $output;
}
// Информация о заказе
add_shortcode('print_information', 'print_information_shortcode');