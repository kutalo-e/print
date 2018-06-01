(function ($) {
    if (!$) {
        console.error('jQuery and $ not exist');
        return;
    }

    // Word with Drag and Drop and Ajax upload
    $(function () {
        // feature detection for drag&drop upload
        var isAdvancedUpload = function()
        {
            var div = document.createElement( 'div' );
            return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
        }();


        // applying the effect for every form

        $('.box').each( function()
        {
            var $this        = $(this),
                url		     = print_form.url,
                $form		 = $this.closest('form'),
                $box		 = $('.box', $this),
                $body		 = $('body'),
                $input		 = $form.find('.box__input input[type="file"]'),
                $label		 = $form.find('.box__input label'),
                $errorMsg	 = $form.find('.box__error span'),
                droppedFiles = false,
                showFiles	 = function(files)
                {
                    $label.text( files.length > 1 ? ( $input.attr( 'data-multiple-caption' ) || '' ).replace('{count}', files.length ) : files[0].name );
                };

            // letting the server side to know we are going to make an Ajax request
            $form.append( '<input type="hidden" name="ajax" value="1" />' );

            // automatically submit the form on file select
            $input.on( 'change', function( e )
            {
                showFiles( e.target.files );
                $form.trigger('submit');
            });

            // drag&drop files if the feature is available
            if( isAdvancedUpload )
            {
                $form
                    .addClass( 'has-advanced-upload' ) // letting the CSS part to know drag&drop is supported by the browser
                    .on( 'drag dragstart dragend dragover dragenter dragleave drop', function( e )
                    {
                        // preventing the unwanted behaviours
                        e.preventDefault();
                        e.stopPropagation();
                    })
                    .on( 'dragover dragenter', function() //
                    {
                        $form.addClass( 'is-dragover' );
                    })
                    .on( 'dragleave dragend drop', function()
                    {
                        $form.removeClass( 'is-dragover' );
                    })
                    .on( 'drop', function( e )
                    {
                        droppedFiles = e.originalEvent.dataTransfer.files; // the files that were dropped
                        showFiles( droppedFiles );
                        $form.trigger('submit');
                    });
            }

            // if the form was submitted
            $form.on('submit', function( e )
            {
                $body.removeClass('is-error');

                // preventing the duplicate submissions if the current one is in progress
                if($box.hasClass('is-uploading')) return false;

                if(isAdvancedUpload) // ajax file upload for modern browsers
                {
                    e.preventDefault();

                    // gathering the form data
                    var ajaxData = new FormData($form.get(0));
                    if(droppedFiles)
                    {
                        $.each(droppedFiles, function(i, file)
                        {
                            ajaxData.append($input.attr('name'), file);
                        });
                    } else {
                        if ($('#file', $this).val().length == 0) {
                            $body.addClass('is-error');
                            return false;
                        }
                    }

                    // $box.addClass( 'is-uploading' ).removeClass( 'is-error' );
                    $body.addClass('is-uploading').removeClass('is-error').removeClass('is-success');

                    // ajax request
                    $.ajax(
                        {
                            url: 			url,
                            type:			$form.attr('method'),
                            data: 			ajaxData,
                            // dataType:		'text',
                            dataType:		'json',
                            cache:			false,
                            contentType:	false,
                            processData:	false,
                            complete: function()
                            {
                                // $box.removeClass( 'is-uploading' );
                                $body.removeClass( 'is-uploading' );
                            },
                            success: function( data )
                            {
                                console.log(data);
                                if(data.url.indexOf('http') + 1) {
                                    open_window(data.url, data.max_pages, data.money, data.id, data.balance, false);
                                    $body.addClass('is-success');
                                    check_balance (data.balance, data.money);
                                }else{
                                    $body.addClass('is-error');
                                }
                            },
                            error: function (xhr, ajaxOptions, thrownError) { // в случае неудачного завершения запроса к серверу
                                console.error('tvgag-post-submit-error-@11: '+xhr.status); // покажем ответ сервера
                                console.error('tvgag-post-submit-error-@12: '+thrownError); // и текст ошибки
                            }
                        });
                }
                else // fallback Ajax solution upload for older browsers
                {
                    var iframeName	= 'uploadiframe' + new Date().getTime(),
                        $iframe		= $( '<iframe name="' + iframeName + '" style="display: none;"></iframe>' );

                    $( 'body' ).append( $iframe );
                    $form.attr( 'target', iframeName );

                    $iframe.one( 'load', function()
                    {
                        var data = $.parseJSON( $iframe.contents().find( 'body' ).text() );
                        $form.removeClass( 'is-uploading' ).addClass( data.success == true ? 'is-success' : 'is-error' ).removeAttr( 'target' );
                        if( !data.success ) $errorMsg.text( data.error );
                        $iframe.remove();
                    });
                }
            });
        });
    });

    }($ || window.jQuery));
// end of file