В этой папке можно разместить файлики: pageURI.mustache, которые будут использованны 
вместо шаблонов, при заходе на урл, соответствующий названию файла.

Например если создать файл: index.mustache, то в нем можно указать уникальный шаблон 
для главной страницы.

Если нужен отдельный шаблон для страницы /page/about, тогда создайте файл about.mustache.

В шаблоне доступны следущие переменные и секции:
{{model}} - модель страницы (если для нее есть запись в базе данных. в таком случае этот
            контент можно будет динамически редактировать через админку).
{{content}} или {{model.content}} - содержимое страницы
{{# setLayout }}layoutName{{/ setLayout }}
