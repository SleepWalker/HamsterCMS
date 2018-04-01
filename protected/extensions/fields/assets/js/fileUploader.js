(function($) {
    $.fn.fileUploader = function() {
        var d = document;

        $('body').on('click', '.fileList .icon_delete', function() {
            $(this)
                .off()
                .parent()
                .html(
                    '<input type="hidden" name="delFile[]" value="' +
                        $(this).attr('fname') +
                        '" />'
                )
                .hide();

            return false;
        });

        /*******************
         *
         * Красивая HTML5 загрузка файлов
         * На основе гайда: http://www.html5rocks.com/en/tutorials/file/dndfiles/
         *
         ******************/
        // Check for the various File API support.
        if (
            window.File &&
            window.FileReader &&
            window.FileList &&
            window.Blob
        ) {
            // Great success! All the File APIs are supported.

            var curFileId = 0;

            var fileInputs = $('input[type="file"]');

            // Событие, которое производит инициализацию работы скрипта
            $('body').on('click', 'input[type="file"]', function() {
                if ($(this).attr('init')) return true; // Прерываем это событие, так как инициалиция уже проведена

                var id = curFileId++;
                fileField = $(this);
                // Копия поля, которая будет использоваться при добавлении нового файлового поля
                var newFF = fileField.clone();
                fileField.on('change', function(evt) {
                    // Создаем контейнер для текущего поля
                    if (!document.getElementById('files_cont_' + id)) {
                        var ul = $(
                            '<ul class="fileList" id="files_cont_' +
                                id +
                                '"></ul>'
                        );
                        ul.insertAfter(fileField);
                    } else var ul = $('#files_cont_' + id);

                    var files = evt.target.files; // FileList object

                    //Создаем еще одно поле загрузки
                    if (fileField.prop('multiple') != '')
                        newFF.insertAfter(fileField);

                    // files is a FileList of File objects. List some properties.
                    var output = [];
                    for (var i = 0, f; (f = files[i]); i++) {
                        // Only process image files.
                        if (f.type.match('image.*')) {
                            var reader = new FileReader();

                            // Closure to capture the file information.
                            reader.onload = (function(theFile, k) {
                                return function(e) {
                                    // Render thumbnail.

                                    var row = $(ul.children('li')[k]);

                                    var img = new Image();
                                    img.src = e.target.result;

                                    img.onload = function() {
                                        var canvas = row.children('canvas')[0];
                                        canvas.style.display = 'inline';
                                        var context = canvas.getContext('2d');
                                        var imgResize = scaleInside(
                                            img,
                                            100,
                                            100
                                        );
                                        context.drawImage(
                                            img,
                                            (100 - imgResize.width) / 2,
                                            (100 - imgResize.height) / 2,
                                            imgResize.width,
                                            imgResize.height
                                        );
                                    };
                                };
                            })(f, i);

                            // Read in the image file as a data URL.
                            reader.readAsDataURL(f);
                        }

                        output.push(
                            '<li><canvas width="100" height="100" style="background:#fff;" /><strong>',
                            escape(f.name),
                            '</strong> (',
                            f.type || 'n/a',
                            ') - ',
                            f.size,
                            ' bytes',
                            '<a href="" class="icon_delete" fname="',
                            f.name,
                            '"></a></li>'
                        );
                    }
                    ul.html(output.join(''));
                });
                $(this).attr('init', 1);
            });
            /*for (var i = 0; i < fileInputs.length; i++)
  {
    // Назначаем события
    setFileFieldEv(fileInputs[i], curFileId++);
  }*/
        }

        function setFileFieldEv(fileField, id) {
            fileField = $(fileField);
            // Копия поля, которая будет использоваться при добавлении нового файлового поля
            var newFF = fileField.clone();
            fileField.on('change', function(evt) {
                // Создаем контейнер для текущего поля
                if (!document.getElementById('files_cont_' + id)) {
                    var ul = $(
                        '<ul class="fileList" id="files_cont_' + id + '"></ul>'
                    );
                    ul.insertAfter(fileField);
                } else var ul = $('#files_cont_' + id);

                var files = evt.target.files; // FileList object

                //Создаем еще одно поле загрузки
                if (fileField.prop('multiple') != '') {
                    newFF.insertAfter(fileField);
                    setFileFieldEv(newFF, curFileId++);
                }

                // files is a FileList of File objects. List some properties.
                var output = [];
                for (var i = 0, f; (f = files[i]); i++) {
                    // Only process image files.
                    if (f.type.match('image.*')) {
                        var reader = new FileReader();

                        // Closure to capture the file information.
                        reader.onload = (function(theFile, k) {
                            return function(e) {
                                // Render thumbnail.

                                var row = $(ul.children('li')[k]);

                                var img = new Image();
                                img.src = e.target.result;
                                img.onload = function() {
                                    var canvas = row.children('canvas')[0];
                                    canvas.style.display = 'inline';
                                    var context = canvas.getContext('2d');
                                    var imgResize = scaleInside(img, 100, 100);
                                    context.drawImage(
                                        img,
                                        (100 - imgResize.width) / 2,
                                        (100 - imgResize.height) / 2,
                                        imgResize.width,
                                        imgResize.height
                                    );
                                };
                            };
                        })(f, i);

                        // Read in the image file as a data URL.
                        reader.readAsDataURL(f);
                    }

                    output.push(
                        '<li><canvas width="100" height="100" style="background:#fff;" /><strong>',
                        escape(f.name),
                        '</strong> (',
                        f.type || 'n/a',
                        ') - ',
                        f.size,
                        ' bytes',
                        '<a href="" class="icon_delete" fname="',
                        f.name,
                        '"></a></li>'
                    );
                }
                ul.html(output.join(''));
            });
        }

        function scaleInside(img, width, height) {
            // Сжимаем по самой широкой стороне
            var max = Math.max(img.width, img.height);
            var maxScaleSize = Math.max(width, height);
            return {
                width: img.width / max * maxScaleSize,
                height: img.height / max * maxScaleSize
            };
        }
    };
})(jQuery);
